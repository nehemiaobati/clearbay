<?php

declare(strict_types=1);

namespace App\Modules\Dispatcher\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Alert
 *
 * @property int|null $id
 * @property int $ambulance_id
 * @property int $hospital_id
 * @property string $alert_type
 * @property \CodeIgniter\I18n\Time|null $triggered_at
 * @property \CodeIgniter\I18n\Time|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Alert extends Entity
{
    protected $datamap = [];
    protected $dates = ['triggered_at', 'acknowledged_at', 'created_at', 'updated_at'];
    protected $casts = [
        'id'              => 'integer',
        'ambulance_id'    => 'integer',
        'hospital_id'     => 'integer',
        'alert_type'      => 'string',
        'acknowledged_by' => 'integer',
    ];
}
