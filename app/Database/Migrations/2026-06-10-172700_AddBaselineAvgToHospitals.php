<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class AddBaselineAvgToHospitals
 *
 * Adds a configurable baseline_avg column to the hospitals table
 * so each facility can define its own expected average off-load wait time
 * instead of using a hardcoded system-wide value.
 *
 * @package App\Database\Migrations
 * @author  Senior Developer
 * @since   1.0.0
 */
class AddBaselineAvgToHospitals extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('hospitals', [
            'baseline_avg' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 60,
                'null'       => false,
                'after'      => 'bays_available',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('hospitals', 'baseline_avg');
    }
}
