<?php

declare(strict_types=1);

namespace App\Modules\Dispatcher\Libraries;

use App\Modules\Ambulance\Models\AmbulanceModel;
use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Dispatcher\Models\AlertModel;
use App\Modules\Dispatcher\Entities\Alert;

/**
 * Class DispatcherService
 *
 * Engine room for command centre telemetry updates, wait checks, and automated alerts.
 */
class DispatcherService
{
    /**
     * @var AmbulanceModel
     */
    private AmbulanceModel $_ambulance_model;

    /**
     * @var HospitalModel
     */
    private HospitalModel $_hospital_model;

    /**
     * @var HandoverModel
     */
    private HandoverModel $_handover_model;

    /**
     * @var AlertModel
     */
    private AlertModel $_alert_model;

    /**
     * DispatcherService constructor.
     */
    public function __construct()
    {
        $this->_ambulance_model = new AmbulanceModel();
        $this->_hospital_model  = new HospitalModel();
        $this->_handover_model  = new HandoverModel();
        $this->_alert_model     = new AlertModel();
    }

    /**
     * Retrieves a hospital's name by ID from the cached local list.
     *
     * @param int $hospital_id
     * @param array $hospitals
     * @return string
     */
    private function _getHospitalName(int $hospital_id, array $hospitals): string
    {
        foreach ($hospitals as $h) {
            if ($h->id === $hospital_id) {
                return $h->name;
            }
        }
        return 'Unknown Hospital';
    }

    /**
     * Checks all en-route/arrived handovers and generates alerts if wait time exceeds 30 minutes.
     *
     * @return void
     */
    private function _checkAndTriggerAlerts(): void
    {
        $db = \Config\Database::connect();
        
        // Find handovers that are en-route/arrived and have been waiting > 30 minutes
        // Wait time increases automatically. If arrived_at is set, compute wait.
        // For simplicity, we can query handovers with status != 'Cleared'
        /** @var \App\Modules\Queue\Entities\Handover[] $handovers */
        $handovers = $this->_handover_model
            ->where('status !=', 'Cleared')
            ->findAll();

        foreach ($handovers as $h) {
            // Calculate current wait time in minutes
            $start_time = $h->arrived_at ?? $h->created_at;
            if ($start_time === null) {
                continue;
            }
            
            $diff_seconds = time() - strtotime($start_time->toDateTimeString());
            $diff_minutes = (int) round($diff_seconds / 60);

            // Update wait_time_minutes in db for this handover so it's accurate
            $this->_handover_model->update($h->id, ['wait_time_minutes' => $diff_minutes]);

            if ($diff_minutes > 30) {
                // Check if alert already exists for this ambulance and hospital which is unacknowledged
                /** @var Alert|null $existing */
                $existing = $this->_alert_model
                    ->where('ambulance_id', $h->ambulance_id)
                    ->where('hospital_id', $h->hospital_id)
                    ->where('acknowledged_at IS NULL')
                    ->first();

                if ($existing === null) {
                    $db->transStart();

                    // Create alert
                    $alert = new Alert([
                        'ambulance_id' => $h->ambulance_id,
                        'hospital_id'  => $h->hospital_id,
                        'alert_type'   => 'Wait Time Exceeded',
                        'triggered_at' => date('Y-m-d H:i:s')
                    ]);
                    $this->_alert_model->save($alert);

                    // Update ambulance status to highlight in red
                    $db->table('ambulances')
                       ->where('id', $h->ambulance_id)
                       ->update(['status' => 'Queued']);

                    // Log audit
                    $db->table('audit_log')->insert([
                        'user_id'    => null,
                        'action'     => 'Automated alert generated: wait time > 30 min',
                        'table_name' => 'alerts',
                        'record_id'  => $this->_alert_model->getInsertID(),
                        'timestamp'  => date('Y-m-d H:i:s')
                    ]);

                    $db->transComplete();

                    // Mock SMS integration via Africa's Talking API
                    if ($db->transStatus() !== false) {
                        $ambulance = $db->table('ambulances')->where('id', $h->ambulance_id)->get()->getRow();
                        $hospital  = $db->table('hospitals')->where('id', $h->hospital_id)->get()->getRow();
                        $unit_id   = $ambulance ? $ambulance->unit_id : 'Unknown';
                        $hosp_name = $hospital ? $hospital->name : 'Unknown';

                        log_message('info', "CLEARBAY ALERT SMS: {$unit_id} has been queued at {$hosp_name} for {$diff_minutes} minutes. Please take action.");
                    }
                }
            }
        }
    }

    /**
     * Compiles complete command center telemetry payload and triggers alerts checks.
     *
     * @return array
     */
    public function getTelemetry(): array
    {
        $db = \Config\Database::connect();
        
        // 1. Fetch ambulances
        /** @var \App\Modules\Ambulance\Entities\Ambulance[] $ambulances */
        $ambulances = $this->_ambulance_model->findAll();

        // 2. Fetch hospitals
        /** @var \App\Modules\Hospital\Entities\Hospital[] $hospitals */
        $hospitals = $this->_hospital_model->where('active', 1)->findAll();

        // 3. Trigger automated 30-minute delay checks
        $this->_checkAndTriggerAlerts();

        // 4. Fetch active alerts joined with ambulance unit ID and hospital name
        /** @var Alert[] $alerts */
        $alerts = $this->_alert_model
            ->select('alerts.*, ambulances.unit_id as ambulance_unit, hospitals.name as hospital_name')
            ->join('ambulances', 'ambulances.id = alerts.ambulance_id')
            ->join('hospitals', 'hospitals.id = alerts.hospital_id')
            ->where('alerts.acknowledged_at IS NULL')
            ->orderBy('alerts.triggered_at', 'DESC')
            ->findAll();

        // 5. Fetch wait times for queued ambulances
        /** @var \App\Modules\Queue\Entities\Handover[] $active_handovers */
        $active_handovers = $this->_handover_model
            ->where('status !=', 'Cleared')
            ->findAll();

        $waits = [];
        foreach ($active_handovers as $h) {
            $waits[$h->ambulance_id] = [
                'hospital_name'     => $this->_getHospitalName((int) $h->hospital_id, $hospitals),
                'wait_time_minutes' => $h->wait_time_minutes
            ];
        }

        return [
            'ambulances' => $ambulances,
            'hospitals'  => $hospitals,
            'alerts'     => $alerts,
            'waits'      => $waits
        ];
    }

    /**
     * Acknowledges an active alert.
     *
     * @param int $alert_id
     * @param int $user_id
     * @return bool
     */
    public function acknowledgeAlert(int $alert_id, int $user_id): bool
    {
        return $this->_alert_model->update($alert_id, [
            'acknowledged_at' => date('Y-m-d H:i:s'),
            'acknowledged_by' => $user_id
        ]);
    }
}
