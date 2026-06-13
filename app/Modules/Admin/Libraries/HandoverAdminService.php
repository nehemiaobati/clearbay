<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Libraries\DatabaseTransactionTrait;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Hospital\Entities\Handover;
use Config\Database;
use Throwable;

/**
 * Class HandoverAdminService
 *
 * Handles administrative actions related to queue handovers.
 *
 * @package App\Modules\Admin\Libraries
 * @author Senior Developer
 * @since 1.0.0
 */
class HandoverAdminService
{
    use DatabaseTransactionTrait;

    private HandoverModel $handover_model;

    public function __construct()
    {
        $this->handover_model = new HandoverModel();
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
     * Saves a handover record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param Handover $handover
     * @return array
     */
    public function saveHandover(Handover $handover): array
    {
        return $this->wrapInTransaction(
            fn() => $this->handover_model->save($handover),
            'saving handover'
        );
    }

    /**
     * Deletes a handover record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param int $id
     * @return array
     */
    public function deleteHandover(int $id): array
    {
        return $this->wrapInTransaction(
            fn() => $this->handover_model->delete($id),
            'deleting handover'
        );
    }

    /**
     * Count handovers.
     *
     * @return int
     */
    public function countHandovers(): int
    {
        return $this->handover_model->countAllResults();
    }
}
