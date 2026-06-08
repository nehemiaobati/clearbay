<?php

declare(strict_types=1);

namespace App\Modules\Queue\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Modules\Hospital\Models\HospitalModel;
use App\Modules\Ambulance\Models\AmbulanceModel;
use App\Modules\Hospital\Models\HandoverModel;
use App\Modules\Hospital\Entities\Hospital;
use App\Modules\Ambulance\Entities\Ambulance;
use App\Modules\Hospital\Entities\Handover;

/**
 * Class QueueSeeder
 *
 * Seed database with initial hospitals, ambulances, and handovers.
 */
class QueueSeeder extends Seeder
{
    /**
     * Runs the database seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $hospital_model  = new HospitalModel();
        $ambulance_model = new AmbulanceModel();
        $handover_model  = new HandoverModel();

        // 1. Seed Hospitals
        $hospitals_data = [
            [
                'code'     => 'KNH',
                'name'     => 'Kenyatta National Hospital',
                'category' => 'National Referral · Public',
                'status'   => 'Red',
            ],
            [
                'code'     => 'MLK',
                'name'     => 'Mama Lucy Kibaki Hospital',
                'category' => 'County Referral · Public',
                'status'   => 'Amber',
            ],
            [
                'code'     => 'MBG',
                'name'     => 'Mbagathi County Hospital',
                'category' => 'County Referral · Public',
                'status'   => 'Red',
            ],
            [
                'code'     => 'AKU',
                'name'     => 'Aga Khan University Hospital',
                'category' => 'Teaching Hospital · Private',
                'status'   => 'Green',
            ],
            [
                'code'     => 'NBO',
                'name'     => 'Nairobi Hospital',
                'category' => 'Referral Hospital · Private',
                'status'   => 'Green',
            ],
        ];

        $hospitals = [];
        foreach ($hospitals_data as $data) {
            // Check if hospital already exists
            $existing = $hospital_model->where('code', $data['code'])->first();
            if ($existing) {
                $hospitals[$data['code']] = $existing;
                continue;
            }

            $hospital = new Hospital($data);
            $hospital_model->save($hospital);
            $new_id = $hospital_model->getInsertID();
            $hospital->id = (int) $new_id;
            $hospitals[$data['code']] = $hospital;
        }

        // 2. Seed Ambulances
        $ambulances_data = [
            ['unit_id' => 'AAR-04', 'provider' => 'AAR Healthcare'],
            ['unit_id' => 'KRC-12', 'provider' => 'Kenya Red Cross'],
            ['unit_id' => 'NBO-07', 'provider' => 'Nairobi County'],
            ['unit_id' => 'AAR-09', 'provider' => 'AAR Healthcare'],
            ['unit_id' => 'KRC-05', 'provider' => 'Kenya Red Cross'],
            ['unit_id' => 'AAR-02', 'provider' => 'AAR Healthcare'],
        ];

        $ambulances = [];
        foreach ($ambulances_data as $data) {
            $existing = $ambulance_model->where('unit_id', $data['unit_id'])->first();
            if ($existing) {
                $ambulances[$data['unit_id']] = $existing;
                continue;
            }

            // Provide minimum default active value
            $data['status'] = 'Available';
            $data['ems_provider_id'] = 1; // Default EMS provider reference

            $ambulance = new Ambulance($data);
            $ambulance_model->save($ambulance);
            $new_id = $ambulance_model->getInsertID();
            $ambulance->id = (int) $new_id;
            $ambulances[$data['unit_id']] = $ambulance;
        }

        // 3. Clear existing handovers to prevent duplicates on multiple seeds
        $handover_model->truncate();

        // 4. Seed Active Handovers matching the Platform Preview mockup in index.php
        $active_handovers = [
            [
                'ambulance_id'      => $ambulances['AAR-04']->id,
                'hospital_id'       => $hospitals['MBG']->id, // Mbagathi
                'patient_age'       => 58,
                'patient_gender'    => 'M',
                'acuity'            => 'Critical',
                'eta_minutes'       => 0,
                'wait_time_minutes' => 52,
                'status'            => 'Arrived',
            ],
            [
                'ambulance_id'      => $ambulances['KRC-12']->id,
                'hospital_id'       => $hospitals['KNH']->id, // Kenyatta
                'patient_age'       => 34,
                'patient_gender'    => 'F',
                'acuity'            => 'Serious',
                'eta_minutes'       => 6,
                'wait_time_minutes' => 18,
                'status'            => 'En route',
            ],
            [
                'ambulance_id'      => $ambulances['NBO-07']->id,
                'hospital_id'       => $hospitals['MLK']->id, // Mama Lucy
                'patient_age'       => 71,
                'patient_gender'    => 'M',
                'acuity'            => 'Stable',
                'eta_minutes'       => 14,
                'wait_time_minutes' => 0,
                'status'            => 'En route',
            ],
            [
                'ambulance_id'      => $ambulances['AAR-09']->id,
                'hospital_id'       => $hospitals['NBO']->id, // Nairobi Hospital
                'patient_age'       => 26,
                'patient_gender'    => 'F',
                'acuity'            => 'Serious',
                'eta_minutes'       => 22,
                'wait_time_minutes' => 0,
                'status'            => 'En route',
            ],
        ];

        foreach ($active_handovers as $data) {
            $handover = new Handover($data);
            $handover_model->save($handover);
        }

        // 5. Seed 14 Completed Handovers (Cleared today) to match the mockup metric "Handovers completed today: 14"
        // Also gives wait times that average around 38 minutes to match "Avg wait today (min): 38"
        // Seed wait times: [40, 45, 50, 35, 38, 42, 55, 30, 48, 52, 40, 45, 50, 44] -> Sum: 614.
        $completed_wait_times = [40, 45, 50, 35, 38, 42, 55, 30, 48, 52, 40, 45, 50, 44];
        
        $genders = ['M', 'F'];
        $acuities = ['Critical', 'Serious', 'Stable'];
        
        for ($i = 0; $i < 14; $i++) {
            // Assign dummy ambulances and hospitals
            $amb_key = array_keys($ambulances)[$i % count($ambulances)];
            $hosp_key = array_keys($hospitals)[$i % count($hospitals)];

            $completed = new Handover([
                'ambulance_id'      => $ambulances[$amb_key]->id,
                'hospital_id'       => $hospitals[$hosp_key]->id,
                'patient_age'       => rand(18, 90),
                'patient_gender'    => $genders[$i % 2],
                'acuity'            => $acuities[$i % 3],
                'eta_minutes'       => 0,
                'wait_time_minutes' => $completed_wait_times[$i],
                'status'            => 'Cleared',
                'created_at'        => date('Y-m-d H:i:s', strtotime('-' . ($i + 1) . ' hours')),
                'updated_at'        => date('Y-m-d H:i:s', strtotime('-' . ($i + 1) . ' hours')),
            ]);

            $handover_model->save($completed);
        }
    }
}
