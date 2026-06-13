<?php

declare(strict_types=1);

namespace App\Modules\Admin\Libraries;

use App\Libraries\DatabaseTransactionTrait;
use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Entities\User;
use Config\Database;
use Throwable;

/**
 * Class UserAdminService
 *
 * Handles administrative actions related to user accounts.
 *
 * @package App\Modules\Admin\Libraries
 * @author Senior Developer
 * @since 1.0.0
 */
class UserAdminService
{
    use DatabaseTransactionTrait;

    private UserModel $user_model;

    public function __construct()
    {
        $this->user_model = new UserModel();
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
     * Saves a user record wrapped in a database transaction.
     * Returns a standardized array with status and message.
     *
     * @param User $user
     * @return array
     */
    public function saveUser(User $user): array
    {
        return $this->wrapInTransaction(
            fn() => $this->user_model->save($user),
            'saving user'
        );
    }

    /**
     * Deactivates a user account (soft delete) to prevent login without data loss.
     * Returns a standardized array with status and message.
     *
     * @param int $id
     * @return array
     */
    public function deleteUser(int $id): array
    {
        return $this->wrapInTransaction(
            fn() => $this->user_model->update($id, ['active' => 0]),
            'deactivating user'
        );
    }

    /**
     * Count users.
     *
     * @return int
     */
    public function countUsers(): int
    {
        return $this->user_model->countAllResults();
    }
}
