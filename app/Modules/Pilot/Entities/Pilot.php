<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Pilot
 */
class Pilot extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [];
}
