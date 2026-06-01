<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Libraries;

use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Models\HospitalStatusModel;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Hospital\Entities\Hospital;
use App\Modules\Hospital\Entities\HospitalStatus;
use App\Modules\Hospital\Entities\Handover;

/**
 * Class HospitalService
 *
 * Engine room for all emergency department flows, status logs, and analytics.
 */
class HospitalService
{
    /**
     * @var HospitalModel
     */
    private HospitalModel $hospital_model;

    /**
     * @var HospitalStatusModel
     */
    private HospitalStatusModel $status_model;

    /**
     * @var HandoverModel
     */
    private HandoverModel $handover_model;

    /**
     * HospitalService constructor.
     */
    public function __construct()
    {
        $this->hospital_model = new HospitalModel();
        $this->status_model   = new HospitalStatusModel();
        $this->handover_model = new HandoverModel();
    }

    /**
     * Resolves the hospital mapped to the given session-bound user.
     * Returns null if no hospital_id is present in the session or the record is missing.
     *
     * @return Hospital|null
     */
    public function getMappedHospital(): ?Hospital
    {
        $hospital_id = session()->get('hospital_id');
        if ($hospital_id === null) {
            return null;
        }

        /** @var Hospital|null $hospital */
        $hospital = $this->hospital_model->find((int) $hospital_id);
        return $hospital;
    }

    /**
     * Retrieves queue list and metrics.
     *
     * @param int $hospital_id
     * @return array
     */
    public function getQueueData(int $hospital_id): array
    {
        // 1. Fetch active handovers (status != 'Cleared')
        $queue = $this->handover_model
            ->select('handovers.id, handovers.ambulance_id, handovers.hospital_id, handovers.patient_age, handovers.patient_gender, handovers.acuity, handovers.eta_minutes, handovers.wait_time_minutes, handovers.status, handovers.created_at, ambulances.unit_id, ambulances.provider, pre_notifications.chief_complaint')
            ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
            ->join('pre_notifications', 'pre_notifications.id = handovers.pre_notification_id', 'left')
            ->where('handovers.hospital_id', $hospital_id)
            ->where('handovers.status !=', 'Cleared')
            ->orderBy('handovers.created_at', 'ASC')
            ->findAll();

        // 2. Fetch completed handovers today count and average wait
        $today_start = date('Y-m-d 00:00:00');
        $today_end   = date('Y-m-d 23:59:59');

        $db = \Config\Database::connect();
        $stats = $db->table('handovers')
            ->select('COUNT(id) as completed_count, SUM(wait_time_minutes) as total_wait')
            ->where('hospital_id', $hospital_id)
            ->where('status', 'Cleared')
            ->where('updated_at >=', $today_start)
            ->where('updated_at <=', $today_end)
            ->get()
            ->getRow();

        $completed_count = (int) ($stats->completed_count ?? 0);
        $total_wait = (int) ($stats->total_wait ?? 0);
        $avg_wait_today = $completed_count > 0 ? (int) round($total_wait / $completed_count) : 0;

        // Baseline comparison (baseline average = 60 minutes)
        $baseline_difference = $avg_wait_today > 0 ? ($avg_wait_today - 60) : 0;

        // Count of ambulances currently in queue (status == 'Arrived' or 'Preparing' or 'Acknowledged')
        $in_queue_count = 0;
        foreach ($queue as $h) {
            if ($h->status !== 'En route') {
                $in_queue_count++;
            }
        }

        // Return structured dashboard telemetry
        return [
            'queue'   => $queue,
            'metrics' => [
                'avg_wait_today'      => $avg_wait_today,
                'baseline_difference' => $baseline_difference,
                'completed_today'     => $completed_count,
                'ambulances_in_queue' => $in_queue_count,
            ],
        ];
    }

    /**
     * Updates hospital ED status and logging history.
     *
     * @param int $hospital_id
     * @param string $status
     * @param int $bays_available
     * @param int $user_id
     * @return bool
     */
    public function updateStatus(int $hospital_id, string $status, int $bays_available, int $user_id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // A. Update Hospitals master status
        $this->hospital_model->update($hospital_id, [
            'status'         => $status,
            'bays_available' => $bays_available,
        ]);

        // B. Log to hospital_status history table
        $log = new HospitalStatus([
            'hospital_id'    => $hospital_id,
            'status'         => $status,
            'bays_available' => $bays_available,
            'updated_by'     => $user_id,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        $this->status_model->save($log);

        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Completes handover, releases crew, and updates wait duration.
     *
     * @param int $handover_id
     * @param string $bay_number
     * @param string $notes
     * @param int $user_id
     * @return bool
     */
    public function completeHandover(int $handover_id, string $bay_number, string $notes, int $user_id): bool
    {
        /** @var Handover|null $handover */
        $handover = $this->handover_model->find($handover_id);
        if ($handover === null) {
            return false;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Calculate wait time since arrived_at (or created_at if arrived_at null)
        $start_time = $handover->arrived_at ?? $handover->created_at;
        $end_time   = date('Y-m-d H:i:s');

        $diff_minutes = 0;
        if ($start_time !== null) {
            $diff_seconds = strtotime($end_time) - strtotime($start_time->toDateTimeString());
            $diff_minutes = (int) max(0, round($diff_seconds / 60));
        }

        // Update handover values
        $handover->status               = 'Cleared';
        $handover->bay_number           = $bay_number;
        $handover->notes                = $notes;
        $handover->completed_by         = $user_id;
        $handover->handover_complete_at = $end_time;
        $handover->wait_time_minutes    = $diff_minutes;

        $this->handover_model->save($handover);

        // Release the ambulance to "Available"
        $db->table('ambulances')
            ->where('id', $handover->ambulance_id)
            ->update([
                'status'       => 'Available',
                'last_updated' => $end_time
            ]);

        // Complete any related pre-notifications
        if ($handover->pre_notification_id) {
            $db->table('pre_notifications')
                ->where('id', $handover->pre_notification_id)
                ->update([
                    'status'      => 'Handover Complete',
                    'received_at' => $end_time
                ]);
        }

        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Fetches analytics data.
     *
     * @param int $hospital_id
     * @param int $days
     * @return array
     */
    public function getAnalytics(int $hospital_id, int $days): array
    {
        $db = \Config\Database::connect();
        $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

        // A. Daily wait time averages
        $daily_waits = $db->table('handovers')
            ->select("DATE(handover_complete_at) as day, ROUND(AVG(wait_time_minutes)) as avg_wait")
            ->where('hospital_id', $hospital_id)
            ->where('status', 'Cleared')
            ->where('handover_complete_at >=', $start_date)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get()
            ->getResultArray();

        // B. Daily handover totals
        $daily_counts = $db->table('handovers')
            ->select("DATE(handover_complete_at) as day, COUNT(id) as count")
            ->where('hospital_id', $hospital_id)
            ->where('status', 'Cleared')
            ->where('handover_complete_at >=', $start_date)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->get()
            ->getResultArray();

        // C. Provider performance summary
        $provider_performance = $db->table('handovers')
            ->select("ambulances.provider, COUNT(handovers.id) as total_handovers, ROUND(AVG(handovers.wait_time_minutes)) as avg_wait")
            ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
            ->where('handovers.hospital_id', $hospital_id)
            ->where('handovers.status', 'Cleared')
            ->where('handovers.handover_complete_at >=', $start_date)
            ->groupBy('ambulances.provider')
            ->orderBy('total_handovers', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'daily_waits'          => $daily_waits,
            'daily_counts'         => $daily_counts,
            'provider_performance' => $provider_performance,
        ];
    }
}
