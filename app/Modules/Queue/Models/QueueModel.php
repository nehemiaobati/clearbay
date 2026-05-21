<?php

declare(strict_types=1);

namespace App\Modules\Queue\Models;

use CodeIgniter\Model;
use App\Modules\Queue\Entities\Queue;

/**
 * Class QueueModel
 */
class QueueModel extends Model
{
    protected $table = 'queue';
    protected $returnType = Queue::class;
    protected $useTimestamps = true;
    protected $allowedFields = [];
}
