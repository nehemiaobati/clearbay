<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class PilotSignup
 *
 * Entity representing a single row in the pilot_signups table.
 *
 * @property int|null $id
 * @property string $full_name
 * @property string $email_address
 * @property string $organisation
 * @property string $user_role
 * @property string|null $phone_number
 * @property string|null $message
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 * @package App\Modules\Pilot\Entities
 */
class PilotSignup extends Entity
{
    /** @var array Maps names to database columns */
    protected $datamap = [];

    /** @var array Dates columns to cast to Time */
    protected $dates = ['created_at', 'updated_at'];

    /** @var array Casting rules */
    protected $casts = [
        'id'            => 'integer',
        'full_name'     => 'string',
        'email_address' => 'string',
        'organisation'  => 'string',
        'user_role'     => 'string',
        'phone_number'  => 'string',
        'message'       => 'string',
    ];

    /** Validation rules for create and update operations. */
    public const VALIDATION_RULES = [
        'fullName'     => 'required|min_length[3]|max_length[255]',
        'emailAddress' => 'required|valid_email|max_length[255]',
        'organisation' => 'required|min_length[3]|max_length[255]',
        'userRole'     => 'required|in_list[Hospital Administrator,ED Manager / Charge Nurse,Emergency Physician,Paramedic / EMT,EMS Dispatcher / Operations Manager,Investor / Funder,Researcher / Academic,Other]',
        'phoneNumber'  => 'permit_empty|min_length[7]|max_length[50]',
        'message'      => 'permit_empty|max_length[2000]',
    ];
}
