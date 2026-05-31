<?php

declare(strict_types=1);

namespace App\Modules\Ambulance\Models;

use App\Modules\Queue\Models\AmbulanceModel as BaseAmbulanceModel;
use App\Modules\Ambulance\Entities\Ambulance;

/**
 * Class AmbulanceModel
 *
 * Model interacting with the ambulances table.
 */
class AmbulanceModel extends BaseAmbulanceModel
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Ambulance::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ems_provider_id',
        'unit_id',
        'registration',
        'current_lat',
        'current_lng',
        'status',
        'last_updated',
    ];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

