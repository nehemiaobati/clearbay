<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Libraries;

use App\Modules\Ambulance\Models\AmbulanceModel;
use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Models\PreNotificationModel;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Hospital\Entities\PreNotification;
use App\Modules\Hospital\Entities\Handover;
use App\Modules\Auth\Models\UserModel;

/**
 * Class AmbulanceService
 *
 * Coordinates ambulance GPS telemetry, hospital lists, and pre-notifications.
 */
class AmbulanceService
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
     * @var PreNotificationModel
     */
    private PreNotificationModel $_pre_model;

    /**
     * @var HandoverModel
     */
    private HandoverModel $_handover_model;

    /**
     * @var UserModel
     */
    private UserModel $_user_model;

    /**
     * AmbulanceService constructor.
     */
    public function __construct()
    {
        $this->_ambulance_model = new AmbulanceModel();
        $this->_hospital_model  = new HospitalModel();
        $this->_pre_model       = new PreNotificationModel();
        $this->_handover_model  = new HandoverModel();
        $this->_user_model       = new UserModel();
    }

    /**
     * Updates active ambulance coordinates.
     *
     * @param int $ambulance_id
     * @param float $lat
     * @param float $lng
     * @return bool
     */
    public function updateLocation(int $ambulance_id, float $lat, float $lng): bool
    {
        return $this->_ambulance_model->update($ambulance_id, [
            'current_lat'  => $lat,
            'current_lng'  => $lng,
            'last_updated' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Retrieves all active hospitals.
     *
     * @return array
     */
    public function getHospitals(): array
    {
        return $this->_hospital_model->where('active', 1)->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Retrieves detailed hospital specs and active queue sizes.
     *
     * @param int $hospital_id
     * @return array
     */
    public function getHospitalDetails(int $hospital_id): array
    {
        /** @var \App\Modules\Hospital\Entities\Hospital|null $hospital */
        $hospital = $this->_hospital_model->find($hospital_id);
        if ($hospital === null) {
            return [];
        }

        $queue_count = $this->_handover_model
            ->where('hospital_id', $hospital_id)
            ->where('status !=', 'Cleared')
            ->countAllResults();

        // Calculate average wait time today (in minutes) for estimation
        $today_start = date('Y-m-d 00:00:00');
        $completed_today = $this->_handover_model
            ->where('hospital_id', $hospital_id)
            ->where('status', 'Cleared')
            ->where('updated_at >=', $today_start)
            ->findAll();

        $total_wait = 0;
        $completed_count = count($completed_today);
        foreach ($completed_today as $h) {
            $total_wait += (int) $h->wait_time_minutes;
        }
        $avg_wait_today = $completed_count > 0 ? (int) round($total_wait / $completed_count) : 8; // default to 8 min

        return [
            'hospital'    => $hospital,
            'queue_count' => $queue_count,
            'avg_wait'    => $avg_wait_today,
        ];
    }

    /**
     * Dispatches a pre-notification en route and creates related queue handover record.
     *
     * @param int $paramedic_id
     * @param int $hospital_id
     * @param int $patient_age
     * @param string $patient_sex
     * @param string $chief_complaint
     * @param string $acuity
     * @param string $notes
     * @param int $eta_minutes
     * @return int|null
     */
    public function sendPreNotification(
        int $paramedic_id,
        int $hospital_id,
        int $patient_age,
        string $patient_sex,
        string $chief_complaint,
        string $acuity,
        string $notes,
        int $eta_minutes
    ): ?int {
        // Fetch paramedic user
        /** @var \App\Modules\Auth\Entities\User|null $user */
        $user = $this->_user_model->find($paramedic_id);
        if ($user === null || $user->ems_provider_id === null) {
            return null;
        }

        // Fetch corresponding ambulance
        /** @var \App\Modules\Ambulance\Entities\Ambulance|null $ambulance */
        $ambulance = $this->_ambulance_model->where('ems_provider_id', $user->ems_provider_id)->first();
        if ($ambulance === null) {
            return null;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $sent_at = date('Y-m-d H:i:s');

        // A. Insert Pre-Notification
        $pre = new PreNotification([
            'ambulance_id'    => $ambulance->id,
            'hospital_id'     => $hospital_id,
            'paramedic_id'    => $paramedic_id,
            'patient_age'     => $patient_age,
            'patient_sex'     => $patient_sex,
            'chief_complaint' => $chief_complaint,
            'acuity'          => $acuity,
            'notes'           => $notes,
            'eta_minutes'     => $eta_minutes,
            'status'          => 'Pending',
            'sent_at'         => $sent_at,
        ]);
        $this->_pre_model->save($pre);
        $pre_id = (int) $this->_pre_model->getInsertID();

        // B. Insert corresponding Handover row to populate ED queue dashboard
        $handover = new Handover([
            'pre_notification_id' => $pre_id,
            'ambulance_id'        => $ambulance->id,
            'hospital_id'         => $hospital_id,
            'patient_age'         => $patient_age,
            'patient_gender'      => $patient_sex === 'Male' ? 'M' : ($patient_sex === 'Female' ? 'F' : 'M'),
            'acuity'              => $acuity,
            'eta_minutes'         => $eta_minutes,
            'wait_time_minutes'   => 0,
            'status'              => 'En route',
            'arrived_at'          => null,
        ]);
        $this->_handover_model->save($handover);

        // C. Update ambulance status
        $this->_ambulance_model->update($ambulance->id, [
            'status'       => 'Transporting',
            'last_updated' => $sent_at,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return null;
        }

        return $pre_id;
    }

    /**
     * Checks telemetry en route and hospital sign-off status.
     *
     * @param int $pre_id
     * @return array
     */
    public function getActiveRunStatus(int $pre_id): array
    {
        /** @var PreNotification|null $pre */
        $pre = $this->_pre_model->find($pre_id);
        if ($pre === null) {
            return [];
        }

        /** @var \App\Modules\Hospital\Entities\Hospital|null $hospital */
        $hospital = $this->_hospital_model->find($pre->hospital_id);

        /** @var Handover|null $handover */
        $handover = $this->_handover_model->where('pre_notification_id', $pre_id)->first();
        
        $status = 'En route';
        if ($handover !== null) {
            $status = $handover->status;
        }

        return [
            'status'            => $status,
            'eta_minutes'       => $pre->eta_minutes,
            'hospital_name'     => $hospital ? $hospital->name : '',
            'hospital_status'   => $hospital ? $hospital->status : 'GREEN',
            'hospital_wait'     => $hospital ? $hospital->status : 'GREEN',
            'bay_preparation'   => ($status === 'Preparing' || $status === 'Arrived' || $status === 'Acknowledged') ? true : false,
        ];
    }
}
