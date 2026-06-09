<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Models;

use CodeIgniter\Model;
use App\Modules\Pilot\Entities\PilotSignup;

/**
 * Class PilotSignupModel
 *
 * Model for the pilot_signups table.
 *
 * @package App\Modules\Pilot\Models
 */
class PilotSignupModel extends Model
{
    /**
     * @var string Database table name
     */
    protected $table = 'pilot_signups';

    /**
     * @var string Entity return type
     */
    protected $returnType = PilotSignup::class;

    /**
     * @var bool Enable auto timestamps
     */
    protected $useTimestamps = true;

    /**
     * @var array Allowed fields for insertions and updates
     */
    protected $allowedFields = [
        'full_name',
        'email_address',
        'organisation',
        'user_role',
        'phone_number',
        'message',
    ];
}
