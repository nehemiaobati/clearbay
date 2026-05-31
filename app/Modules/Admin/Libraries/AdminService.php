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
    private PilotSignupModel $pilotModel;

    /**
     * @var HandoverModel
     */
    private HandoverModel $handoverModel;

    /**
     * @var HospitalModel
     */
    private HospitalModel $hospitalModel;

    /**
     * @var AmbulanceModel
     */
    private AmbulanceModel $ambulanceModel;

    /**
     * @var UserModel
     */
    private UserModel $userModel;

    /**
     * AdminService constructor.
     */
    public function __construct()
    {
        $this->pilotModel = new PilotSignupModel();
        $this->handoverModel = new HandoverModel();
        $this->hospitalModel = new HospitalModel();
        $this->ambulanceModel = new AmbulanceModel();
        $this->userModel = new UserModel();
    }

    public function getPilotModel(): PilotSignupModel
    {
        return $this->pilotModel;
    }

    public function getHandoverModel(): HandoverModel
    {
        return $this->handoverModel;
    }

    public function getHospitalModel(): HospitalModel
    {
        return $this->hospitalModel;
    }

    public function getAmbulanceModel(): AmbulanceModel
    {
        return $this->ambulanceModel;
    }

    public function getUserModel(): UserModel
    {
        return $this->userModel;
    }

    /**
     * Returns count results for pilots, handovers, hospitals, ambulances, and users.
     */
    public function getDashboardMetrics(): array
    {
        return [
            'pilotCount'      => $this->pilotModel->countAllResults(),
            'handoverCount'   => $this->handoverModel->countAllResults(),
            'hospitalCount'   => $this->hospitalModel->countAllResults(),
            'ambulanceCount'  => $this->ambulanceModel->countAllResults(),
            'userCount'       => $this->userModel->countAllResults(),
        ];
    }

    /**
     * Returns paginated pilots list and the pager instance.
     */
    public function getPilotsList(int $perPage): array
    {
        return [
            'pilots' => $this->pilotModel->orderBy('created_at', 'DESC')->paginate($perPage, 'pilots'),
            'pager'  => $this->pilotModel->pager,
        ];
    }

    /**
     * Returns paginated handovers list and the pager instance.
     */
    public function getHandoversList(int $perPage): array
    {
        return [
            'handovers' => $this->handoverModel
                ->select('handovers.*, ambulances.unit_id as ambulance_unit, hospitals.name as hospital_name')
                ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
                ->join('hospitals', 'hospitals.id = handovers.hospital_id')
                ->orderBy('handovers.created_at', 'DESC')
                ->paginate($perPage, 'handovers'),
            'pager'     => $this->handoverModel->pager,
        ];
    }

    /**
     * Returns paginated hospitals list and the pager instance.
     */
    public function getHospitalsList(int $perPage): array
    {
        return [
            'hospitals' => $this->hospitalModel->orderBy('name', 'ASC')->paginate($perPage, 'hospitals'),
            'pager'     => $this->hospitalModel->pager,
        ];
    }

    /**
     * Returns paginated ambulances list and the pager instance.
     */
    public function getAmbulancesList(int $perPage): array
    {
        return [
            'ambulances' => $this->ambulanceModel->orderBy('unit_id', 'ASC')->paginate($perPage, 'ambulances'),
            'pager'      => $this->ambulanceModel->pager,
        ];
    }

    /**
     * Returns paginated users list and the pager instance.
     */
    public function getUsersList(int $perPage): array
    {
        return [
            'users' => $this->userModel
                ->select('users.*, hospitals.name as hospital_name, ems_providers.name as ems_name')
                ->join('hospitals', 'hospitals.id = users.hospital_id', 'left')
                ->join('ems_providers', 'ems_providers.id = users.ems_provider_id', 'left')
                ->orderBy('users.created_at', 'DESC')
                ->paginate($perPage, 'users'),
            'pager' => $this->userModel->pager,
        ];
    }
}

