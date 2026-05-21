<?php

declare(strict_types=1);

namespace App\Modules\Queue\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Queue
 */
class Queue extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [];
}
