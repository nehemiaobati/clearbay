<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Models;

use App\Modules\Queue\Models\HospitalModel as BaseHospitalModel;
use App\Modules\Hospital\Entities\Hospital;

/**
 * Class HospitalModel
 *
 * Model interacting with the hospitals table.
 */
class HospitalModel extends BaseHospitalModel
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Hospital::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'name',
        'category',
        'status',
        'bays_available',
        'lat',
        'lng',
        'address',
        'contact_phone',
        'active',
    ];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}

