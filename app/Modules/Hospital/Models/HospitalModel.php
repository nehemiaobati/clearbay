<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Models;

use CodeIgniter\Model;
use App\Modules\Hospital\Entities\Hospital;

/**
 * Class HospitalModel
 *
 * Model interacting with the hospitals table.
 *
 * @package App\Modules\Hospital\Models
 * @author Senior Developer
 * @since 1.0.0
 */
class HospitalModel extends Model
{
    protected $table            = 'hospitals';
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

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
