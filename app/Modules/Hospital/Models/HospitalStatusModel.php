<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Models;

use CodeIgniter\Model;
use App\Modules\Hospital\Entities\HospitalStatus;

/**
 * Class HospitalStatusModel
 *
 * Model interacting with the hospital_status capacity logging table.
 */
class HospitalStatusModel extends Model
{
    protected $table            = 'hospital_status';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = HospitalStatus::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'hospital_id',
        'status',
        'bays_available',
        'updated_by',
        'updated_at',
    ];

    protected $useTimestamps = false;
}
