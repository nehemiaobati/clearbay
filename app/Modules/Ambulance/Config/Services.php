<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Ambulance\Libraries\AmbulanceService;

/**
 * Class Services
 *
 * Service provider for the Ambulance module.
 *
 * @package App\Modules\Ambulance\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the AmbulanceService.
     *
     * @param bool $getShared
     * @return AmbulanceService
     */
    public static function ambulanceService(bool $getShared = true): AmbulanceService
    {
        if ($getShared) {
            return static::getSharedInstance('ambulanceService');
        }

        return new AmbulanceService();
    }
}
