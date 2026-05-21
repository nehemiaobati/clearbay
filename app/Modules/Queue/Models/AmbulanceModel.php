<?php

declare(strict_types=1);

namespace App\Modules\Queue\Models;

use CodeIgniter\Model;
use App\Modules\Queue\Entities\Ambulance;

/**
 * Class AmbulanceModel
 *
 * Model for the ambulances table.
 *
 * @package App\Modules\Queue\Models
 */
class AmbulanceModel extends Model
{
    protected $table = 'ambulances';
    protected $returnType = Ambulance::class;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'unit_id',
        'provider',
    ];
}
