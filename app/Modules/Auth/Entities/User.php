<?php

declare(strict_types=1);

namespace App\Modules\Auth\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class User
 *
 * Entity representing a user in the database.
 *
 * @property int|null $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property int|null $hospital_id
 * @property int|null $ems_provider_id
 * @property int|null $ambulance_id
 * @property int $active
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class User extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'              => 'integer',
        'name'            => 'string',
        'email'           => 'string',
        'password_hash'   => 'string',
        'role'            => 'string',
        'hospital_id'     => 'integer',
        'ems_provider_id' => 'integer',
        'ambulance_id'    => '?integer',
        'active'          => 'integer',
    ];

    /** Validation rules for create operations. */
    public const VALIDATION_RULES = [
        'name'            => 'required|min_length[3]|max_length[255]',
        'email'           => 'required|valid_email|max_length[255]|is_unique[users.email]',
        'role'            => 'required|in_list[nurse,hospital_admin,paramedic,dispatcher,admin]',
        'hospital_id'     => 'permit_empty|integer',
        'ems_provider_id' => 'permit_empty|integer',
        'ambulance_id'    => 'permit_empty|integer',
        'active'          => 'required|in_list[0,1]',
    ];

    /** Validation rules for update operations (without unique email check). */
    public const UPDATE_RULES = [
        'name'            => 'required|min_length[3]|max_length[255]',
        'email'           => 'required|valid_email|max_length[255]',
        'role'            => 'required|in_list[nurse,hospital_admin,paramedic,dispatcher,admin]',
        'hospital_id'     => 'permit_empty|integer',
        'ems_provider_id' => 'permit_empty|integer',
        'ambulance_id'    => 'permit_empty|integer',
        'active'          => 'required|in_list[0,1]',
    ];

    /** Password validation rules (update context). */
    public const PASSWORD_RULES = [
        'new_password' => 'required|min_length[6]',
    ];

    /**
     * Hashes the password automatically when set.
     *
     * @param string $pass
     * @return self
     */
    public function setPassword(string $pass): self
    {
        $this->attributes['password_hash'] = password_hash($pass, PASSWORD_BCRYPT);
        return $this;
    }
}
