<?php

declare(strict_types=1);

namespace App\Modules\Auth\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Auth\Libraries\AuthService;

/**
 * Class Services
 *
 * Service provider for the Auth module.
 *
 * @package App\Modules\Auth\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the AuthService.
     *
     * @param bool $getShared
     * @return AuthService
     */
    public static function authService(bool $getShared = true): AuthService
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new AuthService();
    }
}
