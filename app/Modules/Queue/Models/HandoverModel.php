<?php

declare(strict_types=1);

namespace App\Modules\Queue\Models;

use CodeIgniter\Model;
use App\Modules\Queue\Entities\Handover;

/**
 * Class HandoverModel
 *
 * Model for the handovers table.
 *
 * @package App\Modules\Queue\Models
 */
class HandoverModel extends Model
{
    protected $table = 'handovers';
    protected $returnType = Handover::class;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'ambulance_id',
        'hospital_id',
        'patient_age',
        'patient_gender',
        'acuity',
        'eta_minutes',
        'wait_time_minutes',
        'status',
    ];

    /**
     * Retrieves the list of active handovers in the queue.
     *
     * @return array
     */
    public function getActiveQueue(): array
    {
        return $this->select('handovers.*, ambulances.unit_id, ambulances.provider, hospitals.name as hospital_name, hospitals.code as hospital_code')
            ->join('ambulances', 'ambulances.id = handovers.ambulance_id')
            ->join('hospitals', 'hospitals.id = handovers.hospital_id')
            ->where('handovers.status !=', 'Cleared')
            ->orderBy('CASE WHEN handovers.status = "Arrived" THEN 1 WHEN handovers.status = "Preparing" THEN 2 WHEN handovers.status = "Acknowledged" THEN 3 ELSE 4 END', 'ASC', false)
            ->orderBy('handovers.wait_time_minutes', 'DESC')
            ->findAll();
    }
}
