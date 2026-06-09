<?php

declare(strict_types=1);

namespace App\Modules\Admin\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Admin\Libraries\AdminService;
use App\Modules\Admin\Libraries\PilotAdminService;
use App\Modules\Admin\Libraries\HandoverAdminService;
use App\Modules\Admin\Libraries\HospitalAdminService;
use App\Modules\Admin\Libraries\AmbulanceAdminService;
use App\Modules\Admin\Libraries\UserAdminService;

/**
 * Class Services
 *
 * Service provider for the Admin module.
 *
 * @package App\Modules\Admin\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the main AdminService facade.
     *
     * @param bool $getShared
     * @return AdminService
     */
    public static function adminService(bool $getShared = true): AdminService
    {
        if ($getShared) {
            return static::getSharedInstance('adminService');
        }

        return new AdminService();
    }

    /**
     * Resolves the PilotAdminService.
     *
     * @param bool $getShared
     * @return PilotAdminService
     */
    public static function pilotAdminService(bool $getShared = true): PilotAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('pilotAdminService');
        }

        return new PilotAdminService();
    }

    /**
     * Resolves the HandoverAdminService.
     *
     * @param bool $getShared
     * @return HandoverAdminService
     */
    public static function handoverAdminService(bool $getShared = true): HandoverAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('handoverAdminService');
        }

        return new HandoverAdminService();
    }

    /**
     * Resolves the HospitalAdminService.
     *
     * @param bool $getShared
     * @return HospitalAdminService
     */
    public static function hospitalAdminService(bool $getShared = true): HospitalAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('hospitalAdminService');
        }

        return new HospitalAdminService();
    }

    /**
     * Resolves the AmbulanceAdminService.
     *
     * @param bool $getShared
     * @return AmbulanceAdminService
     */
    public static function ambulanceAdminService(bool $getShared = true): AmbulanceAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('ambulanceAdminService');
        }

        return new AmbulanceAdminService();
    }

    /**
     * Resolves the UserAdminService.
     *
     * @param bool $getShared
     * @return UserAdminService
     */
    public static function userAdminService(bool $getShared = true): UserAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('userAdminService');
        }

        return new UserAdminService();
    }
}
