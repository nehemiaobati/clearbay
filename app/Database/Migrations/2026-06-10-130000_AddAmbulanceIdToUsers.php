<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class AddAmbulanceIdToUsers
 *
 * Adds an ambulance_id foreign key column to the users table,
 * allowing paramedic users to be assigned to a specific ambulance unit.
 */
class AddAmbulanceIdToUsers extends Migration
{
    /**
     * Runs the migration up.
     *
     * @return void
     */
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'ambulance_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'ems_provider_id',
            ],
        ]);
    }

    /**
     * Runs the migration down.
     *
     * @return void
     */
    public function down(): void
    {
        $this->forge->dropColumn('users', 'ambulance_id');
    }
}
