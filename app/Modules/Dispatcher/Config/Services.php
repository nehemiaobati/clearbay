<?php

declare(strict_types=1);

namespace App\Modules\Dispatcher\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Dispatcher\Libraries\DispatcherService;

/**
 * Class Services
 *
 * Service provider for the Dispatcher module.
 *
 * @package App\Modules\Dispatcher\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the DispatcherService.
     *
     * @param bool $getShared
     * @return DispatcherService
     */
    public static function dispatcherService(bool $getShared = true): DispatcherService
    {
        if ($getShared) {
            return static::getSharedInstance('dispatcherService');
        }

        return new DispatcherService();
    }
}
