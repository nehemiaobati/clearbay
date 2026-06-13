<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Ambulance
 *
 * @property int|null $id
 * @property int|null $ems_provider_id
 * @property string $unit_id
 * @property string|null $registration
 * @property float|null $current_lat
 * @property float|null $current_lng
 * @property string $status
 * @property \CodeIgniter\I18n\Time|null $last_updated
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Ambulance extends Entity
{
    protected $datamap = [];
    protected $dates = ['last_updated', 'created_at', 'updated_at'];
    protected $casts = [
        'id'              => 'integer',
        'ems_provider_id' => 'integer',
        'unit_id'         => 'string',
        'registration'    => 'string',
        'current_lat'     => 'float',
        'current_lng'     => 'float',
        'status'          => 'string',
    ];

    /** Validation rules for create operations. */
    public const VALIDATION_RULES = [
        'unitId'         => 'required|min_length[3]|max_length[50]|is_unique[ambulances.unit_id]',
        'provider'       => 'required|min_length[2]|max_length[255]',
        'ems_provider_id' => 'permit_empty|integer',
        'registration'   => 'permit_empty|max_length[50]',
        'status'         => 'permit_empty|in_list[Available,Transporting,On Scene,Queued,Off Duty]',
        'current_lat'    => 'permit_empty|decimal',
        'current_lng'    => 'permit_empty|decimal',
    ];

    /** Validation rules for update operations (without unique checks). */
    public const UPDATE_RULES = [
        'unitId'         => 'required|min_length[3]|max_length[50]',
        'provider'       => 'required|min_length[2]|max_length[255]',
        'ems_provider_id' => 'permit_empty|integer',
        'registration'   => 'permit_empty|max_length[50]',
        'status'         => 'permit_empty|in_list[Available,Transporting,On Scene,Queued,Off Duty]',
        'current_lat'    => 'permit_empty|decimal',
        'current_lng'    => 'permit_empty|decimal',
    ];
}
