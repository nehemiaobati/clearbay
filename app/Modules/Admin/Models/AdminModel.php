<?php

declare(strict_types=1);

namespace App\Modules\Admin\Models;

use CodeIgniter\Model;
use App\Modules\Admin\Entities\Admin;

/**
 * Class AdminModel
 */
class AdminModel extends Model
{
    protected $table = 'admin';
    protected $returnType = Admin::class;
    protected $useTimestamps = true;
    protected $allowedFields = [];
}
