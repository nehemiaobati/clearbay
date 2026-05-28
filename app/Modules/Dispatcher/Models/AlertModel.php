<?php

declare(strict_types=1);

namespace App\Modules\Dispatcher\Models;

use CodeIgniter\Model;
use App\Modules\Dispatcher\Entities\Alert;

/**
 * Class AlertModel
 *
 * Model interacting with the alerts table.
 */
class AlertModel extends Model
{
    protected $table            = 'alerts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Alert::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ambulance_id',
        'hospital_id',
        'alert_type',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
