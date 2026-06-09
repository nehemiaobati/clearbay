<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Modules\Ambulance\Models\AmbulanceModel;
use App\Modules\Ambulance\Entities\Ambulance;
use Config\Database;
use Throwable;

/**
 * Class AmbulanceAdminService
 *
 * Handles administrative actions related to ambulances.
 *
 * @package App\Modules\Admin\Libraries
 * @author Senior Developer
 * @since 1.0.0
 */
class AmbulanceAdminService
{
    private AmbulanceModel $ambulance_model;

    public function __construct()
    {
        $this->ambulance_model = new AmbulanceModel();
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
     * Retrieves all ambulances ordered by unit_id (for form dropdowns).
     *
     * @return array
     */
    public function getAllAmbulances(): array
    {
        return $this->ambulance_model->orderBy('unit_id', 'ASC')->findAll();
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
     * Saves an ambulance record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param Ambulance $ambulance
     * @return array
     */
    public function saveAmbulance(Ambulance $ambulance): array
    {
        $db = Database::connect();
        $db->transStart();

        try {
            $this->ambulance_model->save($ambulance);
            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['status' => 'error', 'message' => 'Transaction failed while saving ambulance.'];
            }
            return ['status' => 'success', 'message' => 'Ambulance saved successfully.'];
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to save ambulance', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Deletes an ambulance record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param int $id
     * @return array
     */
    public function deleteAmbulance(int $id): array
    {
        $db = Database::connect();
        $db->transStart();

        try {
            $this->ambulance_model->delete($id);
            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['status' => 'error', 'message' => 'Transaction failed while deleting ambulance.'];
            }
            return ['status' => 'success', 'message' => 'Ambulance deleted successfully.'];
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to delete ambulance', [
                'id'        => $id,
                'exception' => $e->getMessage(),
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Retrieves all EMS providers ordered by name (for form dropdowns).
     *
     * @return array
     */
    public function getAllEmsProviders(): array
    {
        $db = Database::connect();
        return $db->table('ems_providers')
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Count ambulances.
     *
     * @return int
     */
    public function countAmbulances(): int
    {
        return $this->ambulance_model->countAllResults();
    }
}
