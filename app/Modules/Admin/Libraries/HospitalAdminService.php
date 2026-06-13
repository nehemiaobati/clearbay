<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Libraries\DatabaseTransactionTrait;
use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Hospital\Entities\Hospital;
use Config\Database;
use Throwable;

/**
 * Class HospitalAdminService
 *
 * Handles administrative actions related to hospitals.
 *
 * @package App\Modules\Admin\Libraries
 * @author Senior Developer
 * @since 1.0.0
 */
class HospitalAdminService
{
    use DatabaseTransactionTrait;

    private HospitalModel $hospital_model;

    public function __construct()
    {
        $this->hospital_model = new HospitalModel();
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
     * Retrieves all hospitals ordered by name (for form dropdowns).
     *
     * @return array
     */
    public function getAllHospitals(): array
    {
        return $this->hospital_model->orderBy('name', 'ASC')->findAll();
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
     * Saves a hospital record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param Hospital $hospital
     * @return array
     */
    public function saveHospital(Hospital $hospital): array
    {
        return $this->wrapInTransaction(
            fn() => $this->hospital_model->save($hospital),
            'saving hospital'
        );
    }

    /**
     * Deletes a hospital record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param int $id
     * @return array
     */
    public function deleteHospital(int $id): array
    {
        return $this->wrapInTransaction(
            fn() => $this->hospital_model->delete($id),
            'deleting hospital'
        );
    }

    /**
     * Count hospitals.
     *
     * @return int
     */
    public function countHospitals(): int
    {
        return $this->hospital_model->countAllResults();
    }
}
