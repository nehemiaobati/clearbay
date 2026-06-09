<?php

declare(strict_types=1);

namespace App\Modules\Queue\Config;

use CodeIgniter\Config\BaseService;
use App\Modules\Queue\Libraries\QueueService;

/**
 * Class Services
 *
 * Service provider for the Queue module.
 *
 * @package App\Modules\Queue\Config
 * @author Senior Developer
 * @since 1.0.0
 */
class Services extends BaseService
{
    /**
     * Resolves the QueueService.
     *
     * @param bool $getShared
     * @return QueueService
     */
    public static function queueService(bool $getShared = true): QueueService
    {
        if ($getShared) {
            return static::getSharedInstance('queueService');
        }

        return new QueueService();
    }
}
