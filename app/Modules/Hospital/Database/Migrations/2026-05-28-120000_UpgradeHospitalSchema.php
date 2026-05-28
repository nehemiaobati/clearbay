<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class UpgradeHospitalSchema
 *
 * Upgrades the hospitals, ambulances, and handovers tables,
 * and creates the users, ems_providers, hospital_status, pre_notifications, alerts, and audit_log tables.
 */
class UpgradeHospitalSchema extends Migration
{
    /**
     * Runs the migration up.
     *
     * @return void
     */
    public function up(): void
    {
        // 1. Create EMS Providers Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('active');
        $this->forge->createTable('ems_providers');

        // 2. Create Users Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'hospital_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ems_provider_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('email');
        $this->forge->addKey('role');
        $this->forge->addKey('active');
        $this->forge->createTable('users');

        // 3. Upgrade Hospitals Table
        $hospital_fields = [
            'bays_available' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'status',
            ],
            'lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => true,
                'after'      => 'bays_available',
            ],
            'lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'after'      => 'lat',
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'lng',
            ],
            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'address',
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'after'      => 'contact_phone',
            ],
        ];
        $this->forge->addColumn('hospitals', $hospital_fields);

        // 4. Upgrade Ambulances Table
        $ambulance_fields = [
            'ems_provider_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'provider',
            ],
            'registration' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'ems_provider_id',
            ],
            'current_lat' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => true,
                'after'      => 'registration',
            ],
            'current_lng' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'after'      => 'current_lat',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Available',
                'after'      => 'current_lng',
            ],
            'last_updated' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status',
            ],
        ];
        $this->forge->addColumn('ambulances', $ambulance_fields);

        // 5. Create Hospital Status History Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'hospital_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'bays_available' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'updated_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('hospital_id');
        $this->forge->addKey('status');
        $this->forge->createTable('hospital_status');

        // 6. Create Pre-Notifications Table
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
            'paramedic_id' => [
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
            'patient_sex' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'chief_complaint' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'acuity' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'eta_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Pending',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'received_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('ambulance_id');
        $this->forge->addKey('hospital_id');
        $this->forge->addKey('paramedic_id');
        $this->forge->addKey('status');
        $this->forge->addKey('sent_at');
        $this->forge->createTable('pre_notifications');

        // 7. Upgrade Handovers Table
        $handover_fields = [
            'pre_notification_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
            'arrived_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'status',
            ],
            'handover_complete_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'arrived_at',
            ],
            'bay_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'handover_complete_at',
            ],
            'notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'bay_number',
            ],
            'completed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'notes',
            ],
        ];
        $this->forge->addColumn('handovers', $handover_fields);

        // 8. Create Alerts Table
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
            'alert_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'triggered_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'acknowledged_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'acknowledged_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addKey('triggered_at');
        $this->forge->createTable('alerts');

        // 9. Create Audit Log Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'table_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'record_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'timestamp' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('table_name');
        $this->forge->addKey('timestamp');
        $this->forge->createTable('audit_log');
    }

    /**
     * Runs the migration down.
     *
     * @return void
     */
    public function down(): void
    {
        $this->forge->dropTable('audit_log');
        $this->forge->dropTable('alerts');

        $this->forge->dropColumn('handovers', [
            'pre_notification_id',
            'arrived_at',
            'handover_complete_at',
            'bay_number',
            'notes',
            'completed_by'
        ]);

        $this->forge->dropTable('pre_notifications');
        $this->forge->dropTable('hospital_status');

        $this->forge->dropColumn('ambulances', [
            'ems_provider_id',
            'registration',
            'current_lat',
            'current_lng',
            'status',
            'last_updated'
        ]);

        $this->forge->dropColumn('hospitals', [
            'bays_available',
            'lat',
            'lng',
            'address',
            'contact_phone',
            'active'
        ]);

        $this->forge->dropTable('users');
        $this->forge->dropTable('ems_providers');
    }
}
