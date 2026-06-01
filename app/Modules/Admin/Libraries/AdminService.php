<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Modules\Pilot\Models\PilotSignupModel;
use App\Modules\Pilot\Entities\PilotSignup;
use App\Modules\Queue\Models\HandoverModel;
use App\Modules\Queue\Entities\Handover;
use App\Modules\Queue\Models\HospitalModel;
use App\Modules\Queue\Entities\Hospital;
use App\Modules\Queue\Models\AmbulanceModel;
use App\Modules\Queue\Entities\Ambulance;
use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Entities\User;

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

    /**
     * Resolves a single pilot signup record by primary key.
     *
     * @param int $pilot_id
     * @return PilotSignup|null
     */
    public function getPilot(int $pilot_id): ?PilotSignup
    {
        /** @var PilotSignup|null $pilot */
        $pilot = $this->pilot_model->find($pilot_id);
        return $pilot;
    }

    /**
     * Resolves a single handover record by primary key.
     *
     * @param int $handover_id
     * @return Handover|null
     */
    public function getHandover(int $handover_id): ?Handover
    {
        /** @var Handover|null $handover */
        $handover = $this->handover_model->find($handover_id);
        return $handover;
    }

    /**
     * Resolves a single hospital record by primary key.
     *
     * @param int $hospital_id
     * @return Hospital|null
     */
    public function getHospital(int $hospital_id): ?Hospital
    {
        /** @var Hospital|null $hospital */
        $hospital = $this->hospital_model->find($hospital_id);
        return $hospital;
    }

    /**
     * Resolves a single ambulance record by primary key.
     *
     * @param int $ambulance_id
     * @return Ambulance|null
     */
    public function getAmbulance(int $ambulance_id): ?Ambulance
    {
        /** @var Ambulance|null $ambulance */
        $ambulance = $this->ambulance_model->find($ambulance_id);
        return $ambulance;
    }

    /**
     * Resolves a single user record by primary key.
     *
     * @param int $user_id
     * @return User|null
     */
    public function getUser(int $user_id): ?User
    {
        /** @var User|null $user */
        $user = $this->user_model->find($user_id);
        return $user;
    }

    /**
     * Retrieves all hospitals ordered by name (for form dropdowns).
     *
     * @return array
     */
    public function getAllHospitals(): array
    {
        return $this->hospital_model->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Retrieves all ambulances ordered by unit_id (for form dropdowns).
     *
     * @return array
     */
    public function getAllAmbulances(): array
    {
        return $this->ambulance_model->orderBy('unit_id', 'ASC')->findAll();
    }

    /**
     * Retrieves all EMS providers ordered by name (for form dropdowns).
     *
     * @return array
     */
    public function getAllEmsProviders(): array
    {
        $db = \Config\Database::connect();
        return $db->table('ems_providers')
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Returns count results for pilots, handovers, hospitals, ambulances, and users.
     *
     * @return array
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
     *
     * @param int $perPage
     * @return array
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
     *
     * @param int $perPage
     * @return array
     */
    public function getHandoversList(int $perPage): array
    {
        return [
            'handovers' => $this->handover_model
                ->select('handovers.id, handovers.ambulance_id, handovers.hospital_id, handovers.patient_age, handovers.patient_gender, handovers.acuity, handovers.eta_minutes, handovers.wait_time_minutes, handovers.status, handovers.created_at, ambulances.unit_id as ambulance_unit, hospitals.name as hospital_name')
                ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
                ->join('hospitals', 'hospitals.id = handovers.hospital_id')
                ->orderBy('handovers.created_at', 'DESC')
                ->paginate($perPage, 'handovers'),
            'pager'     => $this->handover_model->pager,
        ];
    }

    /**
     * Returns paginated hospitals list and the pager instance.
     *
     * @param int $perPage
     * @return array
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
     *
     * @param int $perPage
     * @return array
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
     *
     * @param int $perPage
     * @return array
     */
    public function getUsersList(int $perPage): array
    {
        return [
            'users' => $this->user_model
                ->select('users.id, users.name, users.email, users.role, users.hospital_id, users.ems_provider_id, users.active, users.created_at, hospitals.name as hospital_name, ems_providers.name as ems_name')
                ->join('hospitals', 'hospitals.id = users.hospital_id', 'left')
                ->join('ems_providers', 'ems_providers.id = users.ems_provider_id', 'left')
                ->orderBy('users.created_at', 'DESC')
                ->paginate($perPage, 'users'),
            'pager' => $this->user_model->pager,
        ];
    }

    /**
     * Saves a pilot record wrapped in a database transaction.
     *
     * @param PilotSignup $pilot
     * @return bool
     */
    public function savePilot(PilotSignup $pilot): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->pilot_model->save($pilot);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a pilot record wrapped in a database transaction.
     *
     * @param int $id
     * @return bool
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
     *
     * @param Handover $handover
     * @return bool
     */
    public function saveHandover(Handover $handover): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->handover_model->save($handover);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a handover record wrapped in a database transaction.
     *
     * @param int $id
     * @return bool
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
     *
     * @param Hospital $hospital
     * @return bool
     */
    public function saveHospital(Hospital $hospital): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->hospital_model->save($hospital);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a hospital record wrapped in a database transaction.
     *
     * @param int $id
     * @return bool
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
     *
     * @param Ambulance $ambulance
     * @return bool
     */
    public function saveAmbulance(Ambulance $ambulance): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->ambulance_model->save($ambulance);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes an ambulance record wrapped in a database transaction.
     *
     * @param int $id
     * @return bool
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
     *
     * @param User $user
     * @return bool
     */
    public function saveUser(User $user): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        $this->user_model->save($user);
        $db->transComplete();
        return $db->transStatus() !== false;
    }

    /**
     * Deletes a user record wrapped in a database transaction.
     *
     * @param int $id
     * @return bool
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
