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
        'active'          => 'integer',
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
