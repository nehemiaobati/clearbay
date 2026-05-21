<?php

declare(strict_types=1);

namespace App\Modules\Queue\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Handover
 *
 * Entity representing a single row in the handovers table.
 *
 * @property int|null $id
 * @property int $ambulance_id
 * @property int $hospital_id
 * @property int $patient_age
 * @property string $patient_gender
 * @property string $acuity
 * @property int $eta_minutes
 * @property int $wait_time_minutes
 * @property string $status
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 * @package App\Modules\Queue\Entities
 */
class Handover extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'                => 'integer',
        'ambulance_id'      => 'integer',
        'hospital_id'       => 'integer',
        'patient_age'       => 'integer',
        'patient_gender'    => 'string',
        'acuity'            => 'string',
        'eta_minutes'       => 'integer',
        'wait_time_minutes' => 'integer',
        'status'            => 'string',
    ];
}
