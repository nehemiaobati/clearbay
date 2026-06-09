<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class PreNotification
 *
 * @property int|null $id
 * @property int $ambulance_id
 * @property int $hospital_id
 * @property int $paramedic_id
 * @property int $patient_age
 * @property string $patient_sex
 * @property string $chief_complaint
 * @property string $acuity
 * @property string|null $notes
 * @property int $eta_minutes
 * @property string $status
 * @property \CodeIgniter\I18n\Time|null $sent_at
 * @property \CodeIgniter\I18n\Time|null $received_at
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class PreNotification extends Entity
{
    protected $datamap = [];
    protected $dates = ['sent_at', 'received_at', 'created_at', 'updated_at'];
    protected $casts = [
        'id'              => 'integer',
        'ambulance_id'    => 'integer',
        'hospital_id'     => 'integer',
        'paramedic_id'    => 'integer',
        'patient_age'     => 'integer',
        'patient_sex'     => 'string',
        'chief_complaint' => 'string',
        'acuity'          => 'string',
        'notes'           => 'string',
        'eta_minutes'     => 'integer',
        'status'          => 'string',
    ];
}
