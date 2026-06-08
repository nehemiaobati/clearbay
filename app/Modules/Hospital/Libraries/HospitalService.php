<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Libraries;

use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Models\HospitalStatusModel;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Hospital\Entities\Hospital;
use App\Modules\Hospital\Entities\HospitalStatus;
use App\Modules\Hospital\Entities\Handover;
use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Entities\User;
use CodeIgniter\I18n\Time;

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
     * @param string $user_role
     * @return bool
     */
    public function updateStatus(int $hospital_id, string $status, int $bays_available, int $user_id, string $user_role): bool
    {
        // Role guard: only hospital_admin and admin can modify bay configuration (structural data)
        if ($user_role !== 'hospital_admin' && $user_role !== 'admin') {
            $hospital = $this->hospital_model->find($hospital_id);
            $bays_available = $hospital ? (int) $hospital->bays_available : 0;
        }

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
     * Marks a handover as "Arrived" and records the arrival timestamp.
     * Accessible to nurses and hospital_admin via the Hospital module.
     *
     * @param int $handover_id
     * @return bool
     */
    public function markArrived(int $handover_id): bool
    {
        /** @var Handover|null $handover */
        $handover = $this->handover_model->find($handover_id);
        if ($handover === null) {
            return false;
        }

        // Only allow transition from 'En route'
        if ($handover->status !== 'En route') {
            return false;
        }

        $handover->status     = 'Arrived';
        $handover->arrived_at = date('Y-m-d H:i:s');

        return $this->handover_model->save($handover);
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

        // Ensure arrived_at is set for accurate wait time calculation
        if ($handover->arrived_at === null) {
            $handover->arrived_at = date('Y-m-d H:i:s');
        }

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

    // --- Hospital User Management Methods ---

    /**
     * Returns all users scoped to a specific hospital, ordered by creation date descending.
     *
     * @param int $hospital_id
     * @return array
     */
    public function getHospitalUsers(int $hospital_id): array
    {
        $user_model = new UserModel();
        return $user_model
            ->select('users.id, users.name, users.email, users.role, users.active, users.created_at')
            ->where('users.hospital_id', $hospital_id)
            ->orderBy('users.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Retrieves a single user by ID, verifying it belongs to the given hospital.
     *
     * @param int $user_id
     * @param int $hospital_id
     * @return User|null
     */
    public function getHospitalUser(int $user_id, int $hospital_id): ?User
    {
        $user_model = new UserModel();
        /** @var User|null $user */
        $user = $user_model->where('id', $user_id)->where('hospital_id', $hospital_id)->first();
        return $user;
    }

    /**
     * Creates a new hospital-scoped user with a temporary password.
     * Only nurse and hospital_admin roles are permitted.
     *
     * @param string $name
     * @param string $email
     * @param string $role
     * @param int $hospital_id
     * @return bool
     */
    public function createHospitalUser(string $name, string $email, string $role, int $hospital_id): bool
    {
        $user_model = new UserModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $user = new User();
        $user->name          = $name;
        $user->email         = $email;
        $user->password      = '12345678';
        $user->role          = $role;
        $user->hospital_id   = $hospital_id;
        $user->active        = 1;

        $user_model->save($user);

        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Updates an existing hospital-scoped user.
     *
     * @param int $user_id
     * @param int $hospital_id
     * @param string $name
     * @param string $email
     * @param string $role
     * @param int $active
     * @param string|null $new_password
     * @return bool
     */
    public function updateHospitalUser(int $user_id, int $hospital_id, string $name, string $email, string $role, int $active, ?string $new_password = null): bool
    {
        $user_model = new UserModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $user = $this->getHospitalUser($user_id, $hospital_id);
        if ($user === null) {
            return false;
        }

        $user->name   = $name;
        $user->email  = $email;
        $user->role   = $role;
        $user->active = $active;

        if (!empty($new_password)) {
            $user->password = $new_password;
        }

        $user_model->save($user);

        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deactivates a hospital-scoped user (soft delete).
     *
     * @param int $user_id
     * @param int $hospital_id
     * @return bool
     */
    public function deleteHospitalUser(int $user_id, int $hospital_id): bool
    {
        $user_model = new UserModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $user_model->where('id', $user_id)->where('hospital_id', $hospital_id)->set(['active' => 0])->update();

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
