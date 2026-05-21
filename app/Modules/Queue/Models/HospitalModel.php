<?php

declare(strict_types=1);

namespace App\Modules\Queue\Models;

use CodeIgniter\Model;
use App\Modules\Queue\Entities\Hospital;

/**
 * Class HospitalModel
 *
 * Model for the hospitals table.
 *
 * @package App\Modules\Queue\Models
 */
class HospitalModel extends Model
{
    protected $table = 'hospitals';
    protected $returnType = Hospital::class;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'code',
        'name',
        'category',
        'status',
    ];
}
