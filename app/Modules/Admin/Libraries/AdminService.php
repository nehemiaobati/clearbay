<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Modules\Pilot\Entities\PilotSignup;
use App\Modules\Hospital\Entities\Handover;
use App\Modules\Hospital\Entities\Hospital;
use App\Modules\Ambulance\Entities\Ambulance;
use App\Modules\Auth\Entities\User;

/**
 * Class AdminService
 *
 * Facade orchestrating administrative sub-services for each domain.
 *
 * @package App\Modules\Admin\Libraries
 * @author Senior Developer
 * @since 1.0.0
 */
class AdminService
{
    private PilotAdminService $pilot_service;
    private HandoverAdminService $handover_service;
    private HospitalAdminService $hospital_service;
    private AmbulanceAdminService $ambulance_service;
    private UserAdminService $user_service;

    public function __construct()
    {
        $this->pilot_service     = service('pilotAdminService');
        $this->handover_service  = service('handoverAdminService');
        $this->hospital_service  = service('hospitalAdminService');
        $this->ambulance_service = service('ambulanceAdminService');
        $this->user_service      = service('userAdminService');
    }

    /**
     * Resolves a single pilot signup record by primary key.
     */
    public function getPilot(int $pilot_id): ?PilotSignup
    {
        return $this->pilot_service->getPilot($pilot_id);
    }

    /**
     * Resolves a single handover record by primary key.
     */
    public function getHandover(int $handover_id): ?Handover
    {
        return $this->handover_service->getHandover($handover_id);
    }

    /**
     * Resolves a single hospital record by primary key.
     */
    public function getHospital(int $hospital_id): ?Hospital
    {
        return $this->hospital_service->getHospital($hospital_id);
    }

    /**
     * Resolves a single ambulance record by primary key.
     */
    public function getAmbulance(int $ambulance_id): ?Ambulance
    {
        return $this->ambulance_service->getAmbulance($ambulance_id);
    }

    /**
     * Resolves a single user record by primary key.
     */
    public function getUser(int $user_id): ?User
    {
        return $this->user_service->getUser($user_id);
    }

    /**
     * Retrieves all hospitals ordered by name.
     */
    public function getAllHospitals(): array
    {
        return $this->hospital_service->getAllHospitals();
    }

    /**
     * Retrieves all ambulances ordered by unit_id.
     */
    public function getAllAmbulances(): array
    {
        return $this->ambulance_service->getAllAmbulances();
    }

    /**
     * Retrieves all EMS providers.
     */
    public function getAllEmsProviders(): array
    {
        return $this->ambulance_service->getAllEmsProviders();
    }

    /**
     * Returns count results for pilots, handovers, hospitals, ambulances, and users.
     */
    public function getDashboardMetrics(): array
    {
        return [
            'pilotCount'      => $this->pilot_service->countPilots(),
            'handoverCount'   => $this->handover_service->countHandovers(),
            'hospitalCount'   => $this->hospital_service->countHospitals(),
            'ambulanceCount'  => $this->ambulance_service->countAmbulances(),
            'userCount'       => $this->user_service->countUsers(),
        ];
    }

    /**
     * Returns paginated pilots list and the pager instance.
     */
    public function getPilotsList(int $perPage): array
    {
        return $this->pilot_service->getPilotsList($perPage);
    }

    /**
     * Returns paginated handovers list and the pager instance.
     */
    public function getHandoversList(int $perPage): array
    {
        return $this->handover_service->getHandoversList($perPage);
    }

    /**
     * Returns paginated hospitals list and the pager instance.
     */
    public function getHospitalsList(int $perPage): array
    {
        return $this->hospital_service->getHospitalsList($perPage);
    }

    /**
     * Returns paginated ambulances list and the pager instance.
     */
    public function getAmbulancesList(int $perPage): array
    {
        return $this->ambulance_service->getAmbulancesList($perPage);
    }

    /**
     * Returns paginated users list and the pager instance.
     */
    public function getUsersList(int $perPage): array
    {
        return $this->user_service->getUsersList($perPage);
    }

    /**
     * Saves a pilot record.
     */
    public function savePilot(PilotSignup $pilot): bool
    {
        $result = $this->pilot_service->savePilot($pilot);
        return $result['status'] === 'success';
    }

    /**
     * Deletes a pilot record.
     */
    public function deletePilot(int $id): bool
    {
        $result = $this->pilot_service->deletePilot($id);
        return $result['status'] === 'success';
    }

    /**
     * Saves a handover record.
     */
    public function saveHandover(Handover $handover): bool
    {
        $result = $this->handover_service->saveHandover($handover);
        return $result['status'] === 'success';
    }

    /**
     * Deletes a handover record.
     */
    public function deleteHandover(int $id): bool
    {
        $result = $this->handover_service->deleteHandover($id);
        return $result['status'] === 'success';
    }

    /**
     * Saves a hospital record.
     */
    public function saveHospital(Hospital $hospital): bool
    {
        $result = $this->hospital_service->saveHospital($hospital);
        return $result['status'] === 'success';
    }

    /**
     * Deletes a hospital record.
     */
    public function deleteHospital(int $id): bool
    {
        $result = $this->hospital_service->deleteHospital($id);
        return $result['status'] === 'success';
    }

    /**
     * Saves an ambulance record.
     */
    public function saveAmbulance(Ambulance $ambulance): bool
    {
        $result = $this->ambulance_service->saveAmbulance($ambulance);
        return $result['status'] === 'success';
    }

    /**
     * Deletes an ambulance record.
     */
    public function deleteAmbulance(int $id): bool
    {
        $result = $this->ambulance_service->deleteAmbulance($id);
        return $result['status'] === 'success';
    }

    /**
     * Saves a user record.
     */
    public function saveUser(User $user): bool
    {
        $result = $this->user_service->saveUser($user);
        return $result['status'] === 'success';
    }

    /**
     * Deactivates a user account.
     */
    public function deleteUser(int $id): bool
    {
        $result = $this->user_service->deleteUser($id);
        return $result['status'] === 'success';
    }
}
