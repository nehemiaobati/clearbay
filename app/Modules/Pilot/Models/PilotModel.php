<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Models;

use CodeIgniter\Model;
use App\Modules\Pilot\Entities\Pilot;

/**
 * Class PilotModel
 */
class PilotModel extends Model
{
    protected $table = 'pilot';
    protected $returnType = Pilot::class;
    protected $useTimestamps = true;
    protected $allowedFields = [];
}
