<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class Hospital
 *
 * @property int|null $id
 * @property string $code
 * @property string $name
 * @property string $category
 * @property string $status
 * @property int $bays_available
 * @property int $baseline_avg
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $address
 * @property string|null $contact_phone
 * @property int $active
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 */
class Hospital extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id'             => 'integer',
        'code'           => 'string',
        'name'           => 'string',
        'category'       => 'string',
        'status'         => 'string',
        'bays_available' => 'integer',
        'baseline_avg'   => 'integer',
        'lat'            => 'float',
        'lng'            => 'float',
        'address'        => 'string',
        'contact_phone'  => 'string',
        'active'         => 'integer',
    ];

    /** Validation rules for create and update operations. */
    public const VALIDATION_RULES = [
        'code'           => 'required|min_length[2]|max_length[10]|is_unique[hospitals.code]',
        'name'           => 'required|min_length[3]|max_length[255]',
        'category'       => 'required|min_length[3]|max_length[255]',
        'status'         => 'required|in_list[Green,Amber,Red,Recruiting]',
        'bays_available' => 'permit_empty|integer|greater_than_equal_to[0]',
        'baseline_avg'   => 'permit_empty|integer|greater_than_equal_to[0]',
        'lat'            => 'permit_empty|decimal',
        'lng'            => 'permit_empty|decimal',
        'address'        => 'permit_empty|max_length[500]',
        'contact_phone'  => 'permit_empty|max_length[50]',
        'active'         => 'permit_empty|in_list[0,1]',
    ];

    /** Validation rules for update (with ID exclusion for unique checks). */
    public const UPDATE_RULES = [
        'code'           => 'required|min_length[2]|max_length[10]',
        'name'           => 'required|min_length[3]|max_length[255]',
        'category'       => 'required|min_length[3]|max_length[255]',
        'status'         => 'required|in_list[Green,Amber,Red,Recruiting]',
        'bays_available' => 'permit_empty|integer|greater_than_equal_to[0]',
        'baseline_avg'   => 'permit_empty|integer|greater_than_equal_to[0]',
        'lat'            => 'permit_empty|decimal',
        'lng'            => 'permit_empty|decimal',
        'address'        => 'permit_empty|max_length[500]',
        'contact_phone'  => 'permit_empty|max_length[50]',
        'active'         => 'permit_empty|in_list[0,1]',
    ];
}
