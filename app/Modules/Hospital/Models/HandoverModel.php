<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Models;

use CodeIgniter\Model;
use App\Modules\Hospital\Entities\Handover;

/**
 * Class HandoverModel
 *
 * Model interacting with the handovers table.
 *
 * @package App\Modules\Hospital\Models
 * @author Senior Developer
 * @since 1.0.0
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

    /**
     * Retrieves the list of active handovers in the queue with explicit selections.
     *
     * @return array
     */
    public function getActiveQueue(): array
    {
        return $this->select('handovers.id, handovers.ambulance_id, handovers.hospital_id, handovers.patient_age, handovers.patient_gender, handovers.acuity, handovers.eta_minutes, handovers.wait_time_minutes, handovers.status, handovers.created_at, ambulances.unit_id, ambulances.provider, hospitals.name as hospital_name, hospitals.code as hospital_code')
            ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
            ->join('hospitals', 'hospitals.id = handovers.hospital_id')
            ->where('handovers.status !=', 'Cleared')
            ->orderBy('CASE WHEN handovers.status = "Arrived" THEN 1 WHEN handovers.status = "Preparing" THEN 2 WHEN handovers.status = "Acknowledged" THEN 3 ELSE 4 END', 'ASC', false)
            ->orderBy('handovers.wait_time_minutes', 'DESC')
            ->findAll();
    }
}
