<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Libraries\DatabaseTransactionTrait;
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
    use DatabaseTransactionTrait;

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
        return $this->wrapInTransaction(
            fn() => $this->ambulance_model->save($ambulance),
            'saving ambulance'
        );
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
        return $this->wrapInTransaction(
            fn() => $this->ambulance_model->delete($id),
            'deleting ambulance'
        );
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
     * Retrieves all ambulances annotated with their current paramedic assignment.
     * Each row includes 'assigned_to_name' and 'assigned_to_id' if an active
     * paramedic user already holds this ambulance.
     *
     * @param int|null $exclude_user_id Exclude this user from the conflict check (for edit forms).
     * @return array
     */
    public function getAmbulancesWithAssignments(?int $exclude_user_id = null): array
    {
        $db = Database::connect();
        $builder = $db->table('ambulances')
            ->select('ambulances.id, ambulances.unit_id, ambulances.provider, ambulances.ems_provider_id, ambulances.registration, ambulances.status, users.id as assigned_to_id, users.name as assigned_to_name')
            ->join('users', 'users.ambulance_id = ambulances.id AND users.active = 1 AND users.role = "paramedic"', 'left')
            ->orderBy('ambulances.unit_id', 'ASC');

        // When editing a specific user, exclude them so their own assignment doesn't show as a conflict
        if ($exclude_user_id !== null) {
            $builder->where('(users.id IS NULL OR users.id = ' . (int) $exclude_user_id . ')');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Checks if an ambulance is already assigned to another active paramedic.
     * Returns the conflicting user's name, or null if no conflict.
     *
     * @param int      $ambulance_id
     * @param int|null $current_user_id The user being edited (excluded from conflict).
     * @return string|null
     */
    public function checkAmbulanceConflict(int $ambulance_id, ?int $current_user_id): ?string
    {
        $db = Database::connect();
        $builder = $db->table('users')
            ->select('name')
            ->where('ambulance_id', $ambulance_id)
            ->where('active', 1)
            ->where('role', 'paramedic');

        if ($current_user_id !== null) {
            $builder->where('id !=', $current_user_id);
        }

        $row = $builder->get()->getRow();
        return $row ? $row->name : null;
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
