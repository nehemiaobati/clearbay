<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class HospitalStatus
 *
 * @property int|null $id
 * @property int $hospital_id
 * @property string $status
 * @property int $bays_available
 * @property int $updated_by
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class HospitalStatus extends Entity
{
    protected $datamap = [];
    protected $dates = ['updated_at'];
    protected $casts = [
        'id'             => 'integer',
        'hospital_id'    => 'integer',
        'status'         => 'string',
        'bays_available' => 'integer',
        'updated_by'     => 'integer',
    ];
}
