<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Pilot\Libraries\PilotService;

/**
 * Class Services
 *
 * Service provider for the Pilot module.
 *
 * @package App\Modules\Pilot\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the PilotService.
     *
     * @param bool $getShared
     * @return PilotService
     */
    public static function pilotService(bool $getShared = true): PilotService
    {
        if ($getShared) {
            return static::getSharedInstance('pilotService');
        }

        return new PilotService();
    }
}
