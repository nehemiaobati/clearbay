<?php

declare(strict_types=1);

namespace App\Modules\Queue\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Hospital
 *
 * Entity representing a single row in the hospitals table.
 *
 * @property int|null $id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string $status
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 * @package App\Modules\Queue\Entities
 */
class Hospital extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'       => 'integer',
        'code'     => 'string',
        'name'     => 'string',
        'category' => 'string',
        'status'   => 'string',
    ];
}
