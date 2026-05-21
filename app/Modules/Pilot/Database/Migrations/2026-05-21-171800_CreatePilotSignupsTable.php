<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class CreatePilotSignupsTable
 *
 * Migration to create the pilot_signups database table.
 */
class CreatePilotSignupsTable extends Migration
{
    /**
     * Runs the migration up.
     *
     * @return void
     */
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'email_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'organisation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'user_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('email_address');
        $this->forge->addKey('created_at');

        $this->forge->createTable('pilot_signups');
    }

    /**
     * Runs the migration down.
     *
     * @return void
     */
    public function down(): void
    {
        $this->forge->dropTable('pilot_signups');
    }
}
