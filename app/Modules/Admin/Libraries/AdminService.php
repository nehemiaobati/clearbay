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

    public function __construct(
        ?PilotAdminService $pilot_service = null,
        ?HandoverAdminService $handover_service = null,
        ?HospitalAdminService $hospital_service = null,
        ?AmbulanceAdminService $ambulance_service = null,
        ?UserAdminService $user_service = null
    ) {
        $this->pilot_service     = $pilot_service ?? service('pilotAdminService');
        $this->handover_service  = $handover_service ?? service('handoverAdminService');
        $this->hospital_service  = $hospital_service ?? service('hospitalAdminService');
        $this->ambulance_service = $ambulance_service ?? service('ambulanceAdminService');
        $this->user_service      = $user_service ?? service('userAdminService');
    }

    // --- Single Record Resolvers ---

    public function getPilot(int $pilot_id): ?PilotSignup
    {
        return $this->pilot_service->getPilot($pilot_id);
    }

    public function getHandover(int $handover_id): ?Handover
    {
        return $this->handover_service->getHandover($handover_id);
    }

    public function getHospital(int $hospital_id): ?Hospital
    {
        return $this->hospital_service->getHospital($hospital_id);
    }

    public function getAmbulance(int $ambulance_id): ?Ambulance
    {
        return $this->ambulance_service->getAmbulance($ambulance_id);
    }

    public function getUser(int $user_id): ?User
    {
        return $this->user_service->getUser($user_id);
    }

    // --- Collection Retrieval ---

    public function getAllHospitals(): array
    {
        return $this->hospital_service->getAllHospitals();
    }

    public function getAllAmbulances(): array
    {
        return $this->ambulance_service->getAllAmbulances();
    }

    public function getAllEmsProviders(): array
    {
        return $this->ambulance_service->getAllEmsProviders();
    }

    /**
     * Retrieves all ambulances annotated with their current paramedic
     * assignment (assigned_to_name and assigned_to_id).
     */
    public function getAmbulancesWithAssignments(?int $exclude_user_id = null): array
    {
        return $this->ambulance_service->getAmbulancesWithAssignments($exclude_user_id);
    }

    /**
     * Checks if an ambulance is assigned to another active paramedic.
     * Returns the conflicting user's name, or null if no conflict.
     */
    public function checkAmbulanceConflict(int $ambulance_id, ?int $current_user_id): ?string
    {
        return $this->ambulance_service->checkAmbulanceConflict($ambulance_id, $current_user_id);
    }

    // --- Dashboard ---

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

    // --- Paginated Lists ---

    public function getPilotsList(int $perPage): array
    {
        return $this->pilot_service->getPilotsList($perPage);
    }

    public function getHandoversList(int $perPage): array
    {
        return $this->handover_service->getHandoversList($perPage);
    }

    public function getHospitalsList(int $perPage): array
    {
        return $this->hospital_service->getHospitalsList($perPage);
    }

    public function getAmbulancesList(int $perPage): array
    {
        return $this->ambulance_service->getAmbulancesList($perPage);
    }

    public function getUsersList(int $perPage): array
    {
        return $this->user_service->getUsersList($perPage);
    }

    // --- Save / Delete Passthroughs ---

    public function savePilot(PilotSignup $pilot): bool
    {
        $result = $this->pilot_service->savePilot($pilot);
        return $result['status'] === 'success';
    }

    public function deletePilot(int $id): bool
    {
        $result = $this->pilot_service->deletePilot($id);
        return $result['status'] === 'success';
    }

    public function saveHandover(Handover $handover): bool
    {
        $result = $this->handover_service->saveHandover($handover);
        return $result['status'] === 'success';
    }

    public function deleteHandover(int $id): bool
    {
        $result = $this->handover_service->deleteHandover($id);
        return $result['status'] === 'success';
    }

    public function saveHospital(Hospital $hospital): bool
    {
        $result = $this->hospital_service->saveHospital($hospital);
        return $result['status'] === 'success';
    }

    public function deleteHospital(int $id): bool
    {
        $result = $this->hospital_service->deleteHospital($id);
        return $result['status'] === 'success';
    }

    public function saveAmbulance(Ambulance $ambulance): bool
    {
        $result = $this->ambulance_service->saveAmbulance($ambulance);
        return $result['status'] === 'success';
    }

    public function deleteAmbulance(int $id): bool
    {
        $result = $this->ambulance_service->deleteAmbulance($id);
        return $result['status'] === 'success';
    }

    public function saveUser(User $user): bool
    {
        $result = $this->user_service->saveUser($user);
        return $result['status'] === 'success';
    }

    public function deleteUser(int $id): bool
    {
        $result = $this->user_service->deleteUser($id);
        return $result['status'] === 'success';
    }

    // --- CSV Report Generation ---

    /**
     * Generates a plain-text CSV string from analytics data.
     *
     * @param array $analytics The analytics dataset from HospitalService::getAnalytics()
     * @return string
     */
    public function generateCsvReport(array $analytics): string
    {
        $lines = [];
        $lines[] = "ClearBay Global Off-Load Performance Report";
        $lines[] = "Facility,All Facilities";
        $lines[] = "Date Range,Past 30 Days";
        $lines[] = "Report Date," . date('Y-m-d H:i:s') . " EAT";
        $lines[] = "";

        // Hospital Breakdown
        $lines[] = "Facility Summary";
        $lines[] = "Hospital Name,Handovers Completed,Average Wait Time (Minutes)";
        foreach ($analytics['facility_performance'] as $row) {
            $lines[] = sprintf(
                "%s,%d,%s",
                $this->_csvEscape((string) $row['hospital_name']),
                (int) $row['total_handovers'],
                (string) $row['avg_wait']
            );
        }
        $lines[] = "";

        // Provider Breakdown
        $lines[] = "EMS Provider Summary";
        $lines[] = "Provider,Handovers Completed,Ambulances";
        foreach ($analytics['provider_performance'] as $row) {
            $lines[] = sprintf(
                "%s,%d,%d",
                $this->_csvEscape((string) $row['provider']),
                (int) ($row['total_handovers'] ?? 0),
                (int) ($row['total_ambulances'] ?? 0)
            );
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * CSV cell escaping utility.
     *
     * @param string $val
     * @return string
     */
    private function _csvEscape(string $val): string
    {
        $val = str_replace('"', '""', $val);
        if (str_contains($val, ',') || str_contains($val, '"') || str_contains($val, "\n") || str_contains($val, "\r")) {
            return '"' . $val . '"';
        }
        return $val;
    }
}