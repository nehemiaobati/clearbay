<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class AddCompositeIndexes
 *
 * Adds composite indexes to optimize telemetry and queue lookups:
 * - handovers: [hospital_id, status] for dashboard queue queries
 * - ambulances: [ems_provider_id, status] for provider-based status scans
 */
class AddCompositeIndexes extends Migration
{
    public function up(): void
    {
        $db = \Config\Database::connect();

        // Composite index on handovers for paired hospital + status queries
        $db->query('ALTER TABLE handovers ADD INDEX handovers_hosp_status (hospital_id, status)');

        // Composite index on ambulances for provider + status lookups
        $db->query('ALTER TABLE ambulances ADD INDEX ambulances_provider_status (ems_provider_id, status)');
    }

    public function down(): void
    {
        $db = \Config\Database::connect();
        $db->query('ALTER TABLE handovers DROP INDEX handovers_hosp_status');
        $db->query('ALTER TABLE ambulances DROP INDEX ambulances_provider_status');
    }
}
