<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Modules\Pilot\Models\PilotSignupModel;
use App\Modules\Queue\Models\HandoverModel;
use App\Modules\Queue\Models\HospitalModel;
use App\Modules\Queue\Models\AmbulanceModel;
use App\Modules\Auth\Models\UserModel;

/**
 * Class AdminService
 */
class AdminService
{
    /**
     * @var PilotSignupModel
     */
    private PilotSignupModel $pilot_model;

    /**
     * @var HandoverModel
     */
    private HandoverModel $handover_model;

    /**
     * @var HospitalModel
     */
    private HospitalModel $hospital_model;

    /**
     * @var AmbulanceModel
     */
    private AmbulanceModel $ambulance_model;

    /**
     * @var UserModel
     */
    private UserModel $user_model;

    /**
     * AdminService constructor.
     */
    public function __construct()
    {
        $this->pilot_model = new PilotSignupModel();
        $this->handover_model = new HandoverModel();
        $this->hospital_model = new HospitalModel();
        $this->ambulance_model = new AmbulanceModel();
        $this->user_model = new UserModel();
    }

    public function getPilotModel(): PilotSignupModel
    {
        return $this->pilot_model;
    }

    public function getHandoverModel(): HandoverModel
    {
        return $this->handover_model;
    }

    public function getHospitalModel(): HospitalModel
    {
        return $this->hospital_model;
    }

    public function getAmbulanceModel(): AmbulanceModel
    {
        return $this->ambulance_model;
    }

    public function getUserModel(): UserModel
    {
        return $this->user_model;
    }

    /**
     * Returns count results for pilots, handovers, hospitals, ambulances, and users.
     */
    public function getDashboardMetrics(): array
    {
        return [
            'pilotCount'      => $this->pilot_model->countAllResults(),
            'handoverCount'   => $this->handover_model->countAllResults(),
            'hospitalCount'   => $this->hospital_model->countAllResults(),
            'ambulanceCount'  => $this->ambulance_model->countAllResults(),
            'userCount'       => $this->user_model->countAllResults(),
        ];
    }

    /**
     * Returns paginated pilots list and the pager instance.
     */
    public function getPilotsList(int $perPage): array
    {
        return [
            'pilots' => $this->pilot_model->orderBy('created_at', 'DESC')->paginate($perPage, 'pilots'),
            'pager'  => $this->pilot_model->pager,
        ];
    }

    /**
     * Returns paginated handovers list and the pager instance.
     */
    public function getHandoversList(int $perPage): array
    {
        return [
            'handovers' => $this->handover_model
                ->select('handovers.*, ambulances.unit_id as ambulance_unit, hospitals.name as hospital_name')
                ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
                ->join('hospitals', 'hospitals.id = handovers.hospital_id')
                ->orderBy('handovers.created_at', 'DESC')
                ->paginate($perPage, 'handovers'),
            'pager'     => $this->handover_model->pager,
        ];
    }

    /**
     * Returns paginated hospitals list and the pager instance.
     */
    public function getHospitalsList(int $perPage): array
    {
        return [
            'hospitals' => $this->hospital_model->orderBy('name', 'ASC')->paginate($perPage, 'hospitals'),
            'pager'     => $this->hospital_model->pager,
        ];
    }

    /**
     * Returns paginated ambulances list and the pager instance.
     */
    public function getAmbulancesList(int $perPage): array
    {
        return [
            'ambulances' => $this->ambulance_model->orderBy('unit_id', 'ASC')->paginate($perPage, 'ambulances'),
            'pager'      => $this->ambulance_model->pager,
        ];
    }

    /**
     * Returns paginated users list and the pager instance.
     */
    public function getUsersList(int $perPage): array
    {
        return [
            'users' => $this->user_model
                ->select('users.*, hospitals.name as hospital_name, ems_providers.name as ems_name')
                ->join('hospitals', 'hospitals.id = users.hospital_id', 'left')
                ->join('ems_providers', 'ems_providers.id = users.ems_provider_id', 'left')
                ->orderBy('users.created_at', 'DESC')
                ->paginate($perPage, 'users'),
            'pager' => $this->user_model->pager,
        ];
    }

    /**
     * Saves a pilot record wrapped in a database transaction.
     */
    public function savePilot(\App\Modules\Pilot\Entities\PilotSignup $pilot): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->pilot_model->save($pilot);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a pilot record wrapped in a database transaction.
     */
    public function deletePilot(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->pilot_model->delete($id);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Saves a handover record wrapped in a database transaction.
     */
    public function saveHandover(\App\Modules\Queue\Entities\Handover $handover): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->handover_model->save($handover);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a handover record wrapped in a database transaction.
     */
    public function deleteHandover(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->handover_model->delete($id);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Saves a hospital record wrapped in a database transaction.
     */
    public function saveHospital(\App\Modules\Queue\Entities\Hospital $hospital): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->hospital_model->save($hospital);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a hospital record wrapped in a database transaction.
     */
    public function deleteHospital(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->hospital_model->delete($id);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Saves an ambulance record wrapped in a database transaction.
     */
    public function saveAmbulance(\App\Modules\Queue\Entities\Ambulance $ambulance): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->ambulance_model->save($ambulance);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes an ambulance record wrapped in a database transaction.
     */
    public function deleteAmbulance(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->ambulance_model->delete($id);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Saves a user record wrapped in a database transaction.
     */
    public function saveUser(\App\Modules\Auth\Entities\User $user): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->user_model->save($user);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a user record wrapped in a database transaction.
     */
    public function deleteUser(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->user_model->delete($id);
        $db->transComplete();
        return $db->transStatus() !== false;
    }
}

