<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Models;

use CodeIgniter\Model;
use App\Modules\Hospital\Entities\Handover;

/**
 * Class HandoverModel
 *
 * Model interacting with the handovers table.
 */
class HandoverModel extends Model
{
    protected $table            = 'handovers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Handover::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pre_notification_id',
        'ambulance_id',
        'hospital_id',
        'patient_age',
        'patient_gender',
        'acuity',
        'eta_minutes',
        'wait_time_minutes',
        'status',
        'arrived_at',
        'handover_complete_at',
        'bay_number',
        'notes',
        'completed_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
