<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Class MainSeeder
 *
 * Root orchestration seeder. Calls all module-level seeders in dependency order.
 * Run via: php spark db:seed MainSeeder
 */
class MainSeeder extends Seeder
{
    /**
     * Runs all module seeders in the correct dependency order.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call('App\Modules\Pilot\Database\Seeds\PilotSignupSeeder');
        $this->call('App\Modules\Queue\Database\Seeds\QueueSeeder');
    }
}
