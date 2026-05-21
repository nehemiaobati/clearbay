<?php

declare(strict_types=1);

namespace App\Modules\Queue\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class CreateQueueTables
 *
 * Migration to create hospitals, ambulances, and handovers tables.
 */
class CreateQueueTables extends Migration
{
    /**
     * Runs the migration up.
     *
     * @return void
     */
    public function up(): void
    {
        // 1. Create Hospitals Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'Green', // Green, Amber, Red
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
        $this->forge->addKey('code');
        $this->forge->createTable('hospitals');

        // 2. Create Ambulances Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'unit_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
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
        $this->forge->addKey('unit_id');
        $this->forge->createTable('ambulances');

        // 3. Create Handovers Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ambulance_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'hospital_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'patient_age' => [
                'type'       => 'INT',
                'constraint' => 3,
                'null'       => false,
            ],
            'patient_gender' => [
                'type'       => 'CHAR',
                'constraint' => 1,
                'null'       => false,
            ],
            'acuity' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false, // Critical, Serious, Stable
            ],
            'eta_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'wait_time_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false, // En route, Arrived, Acknowledged, Preparing, Cleared
                'default'    => 'En route',
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
        $this->forge->addKey('ambulance_id');
        $this->forge->addKey('hospital_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('handovers');
    }

    /**
     * Runs the migration down.
     *
     * @return void
     */
    public function down(): void
    {
        $this->forge->dropTable('handovers');
        $this->forge->dropTable('ambulances');
        $this->forge->dropTable('hospitals');
    }
}
