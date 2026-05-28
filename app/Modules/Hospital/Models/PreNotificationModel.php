<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Models;

use CodeIgniter\Model;
use App\Modules\Hospital\Entities\PreNotification;

/**
 * Class PreNotificationModel
 *
 * Model interacting with the pre_notifications table.
 */
class PreNotificationModel extends Model
{
    protected $table            = 'pre_notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = PreNotification::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ambulance_id',
        'hospital_id',
        'paramedic_id',
        'patient_age',
        'patient_sex',
        'chief_complaint',
        'acuity',
        'notes',
        'eta_minutes',
        'status',
        'sent_at',
        'received_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
