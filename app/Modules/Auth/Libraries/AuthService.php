<?php

declare(strict_types=1);

namespace App\Modules\Auth\Libraries;

use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Entities\User;

/**
 * Class AuthService
 *
 * Handles user authentication business logic.
 */
class AuthService
{
    /**
     * @var UserModel
     */
    private UserModel $_user_model;

    /**
     * AuthService constructor.
     */
    public function __construct()
    {
        $this->_user_model = new UserModel();
    }

    /**
     * Authenticates a user by email and password.
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function login(string $email, string $password): ?User
    {
        /** @var User|null $user */
        $user = $this->_user_model->where('email', $email)->where('active', 1)->first();

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user->password_hash)) {
            return null;
        }

        $session = session();
        $session->set([
            'is_logged_in'     => true,
            'user_id'          => $user->id,
            'user_role'        => $user->role,
            'user_name'        => $user->name,
            'hospital_id'      => $user->hospital_id,
            'ems_provider_id'  => $user->ems_provider_id,
        ]);

        return $user;
    }

    /**
     * Logs out the current user.
     *
     * @return void
     */
    public function logout(): void
    {
        session()->destroy();
    }

    /**
     * Gets the currently authenticated user.
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return null;
        }

        $user_id = $session->get('user_id');
        if ($user_id === null) {
            return null;
        }

        /** @var User|null $user */
        $user = $this->_user_model->find((int) $user_id);
        return $user;
    }

    /**
     * Checks if a user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return (bool) session()->get('is_logged_in');
    }
}
