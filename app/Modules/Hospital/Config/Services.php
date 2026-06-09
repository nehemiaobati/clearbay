<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Hospital\Libraries\HospitalService;

/**
 * Class Services
 *
 * Service provider for the Hospital module.
 *
 * @package App\Modules\Hospital\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the HospitalService.
     *
     * @param bool $getShared
     * @return HospitalService
     */
    public static function hospitalService(bool $getShared = true): HospitalService
    {
        if ($getShared) {
            return static::getSharedInstance('hospitalService');
        }

        return new HospitalService();
    }
}
