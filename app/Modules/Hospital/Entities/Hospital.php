<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Hospital
 *
 * @property int|null $id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string $status
 * @property int $bays_available
 * @property int $baseline_avg
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $address
 * @property string|null $contact_phone
 * @property int $active
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Hospital extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'             => 'integer',
        'code'           => 'string',
        'name'           => 'string',
        'category'       => 'string',
        'status'         => 'string',
        'bays_available' => 'integer',
        'baseline_avg'   => 'integer',
        'lat'            => 'float',
        'lng'            => 'float',
        'address'        => 'string',
        'contact_phone'  => 'string',
        'active'         => 'integer',
    ];
}
