<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Libraries\DatabaseTransactionTrait;
use App\Modules\Pilot\Models\PilotSignupModel;
use App\Modules\Pilot\Entities\PilotSignup;
use Config\Database;
use Throwable;

/**
 * Class PilotAdminService
 *
 * Handles administrative actions related to pilot signups.
 *
 * @package App\Modules\Admin\Libraries
 * @author Senior Developer
 * @since 1.0.0
 */
class PilotAdminService
{
    use DatabaseTransactionTrait;

    private PilotSignupModel $pilot_model;

    public function __construct()
    {
        $this->pilot_model = new PilotSignupModel();
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
     * Saves a pilot record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param PilotSignup $pilot
     * @return array
     */
    public function savePilot(PilotSignup $pilot): array
    {
        return $this->wrapInTransaction(
            fn() => $this->pilot_model->save($pilot),
            'saving pilot signup'
        );
    }

    /**
     * Deletes a pilot record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param int $id
     * @return array
     */
    public function deletePilot(int $id): array
    {
        return $this->wrapInTransaction(
            fn() => $this->pilot_model->delete($id),
            'deleting pilot signup'
        );
    }

    /**
     * Count pilots.
     *
     * @return int
     */
    public function countPilots(): int
    {
        return $this->pilot_model->countAllResults();
    }
}
