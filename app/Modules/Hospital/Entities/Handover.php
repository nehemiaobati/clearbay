<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Handover
 *
 * @property int|null $id
 * @property int|null $pre_notification_id
 * @property int $ambulance_id
 * @property int $hospital_id
 * @property int $patient_age
 * @property string $patient_gender
 * @property string $acuity
 * @property int $eta_minutes
 * @property int $wait_time_minutes
 * @property string $status
 * @property \CodeIgniter\I18n\Time|null $arrived_at
 * @property \CodeIgniter\I18n\Time|null $handover_complete_at
 * @property string|null $bay_number
 * @property string|null $notes
 * @property int|null $completed_by
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Handover extends Entity
{
    protected $datamap = [];
    protected $dates = ['arrived_at', 'handover_complete_at', 'created_at', 'updated_at'];
    protected $casts = [
        'id'                  => 'integer',
        'pre_notification_id' => 'integer',
        'ambulance_id'        => 'integer',
        'hospital_id'         => 'integer',
        'patient_age'         => 'integer',
        'patient_gender'      => 'string',
        'acuity'              => 'string',
        'eta_minutes'         => 'integer',
        'wait_time_minutes'   => 'integer',
        'status'              => 'string',
        'bay_number'          => 'string',
        'notes'               => 'string',
        'completed_by'        => 'integer',
    ];
}
