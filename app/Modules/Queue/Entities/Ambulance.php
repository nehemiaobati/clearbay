<?php

declare(strict_types=1);

namespace App\Modules\Queue\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Ambulance
 *
 * Entity representing a single row in the ambulances table.
 *
 * @property int|null $id
 * @property string $unit_id
 * @property string $provider
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 * @package App\Modules\Queue\Entities
 */
class Ambulance extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'       => 'integer',
        'unit_id'  => 'string',
        'provider' => 'string',
    ];
}
