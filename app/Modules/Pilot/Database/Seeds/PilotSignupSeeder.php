<?php

declare(strict_types=1);

namespace App\Modules\Pilot\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Modules\Pilot\Models\PilotSignupModel;
use App\Modules\Pilot\Entities\PilotSignup;

/**
 * Class PilotSignupSeeder
 *
 * Seeds the pilot_signups table with representative demo applicants
 * matching real-world Kenya ED and ambulance service roles.
 *
 * Run via: php spark db:seed MainSeeder
 */
class PilotSignupSeeder extends Seeder
{
    /**
     * Runs the pilot signup seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $model = new PilotSignupModel();

        $signups = [
            [
                'full_name'     => 'Dr. Wanjiru Kamau',
                'email_address' => 'wanjiru@mbagathi.ke',
                'organisation'  => 'Mbagathi County Hospital',
                'user_role'     => 'ED Manager / Charge Nurse',
                'phone_number'  => '+254 712 345678',
                'message'       => 'We would love to participate in the off-load management pilot!',
            ],
            [
                'full_name'     => 'James Otieno',
                'email_address' => 'j.otieno@kenyaredcross.or.ke',
                'organisation'  => 'Kenya Red Cross',
                'user_role'     => 'Ambulance Paramedic',
                'phone_number'  => '+254 722 100200',
                'message'       => 'Our crews spend too long in bays. ClearBay could fix this.',
            ],
            [
                'full_name'     => 'Dr. Amina Hassan',
                'email_address' => 'a.hassan@knh.or.ke',
                'organisation'  => 'Kenyatta National Hospital',
                'user_role'     => 'Hospital Administrator',
                'phone_number'  => '+254 733 456789',
                'message'       => 'Interested in piloting the system across our emergency department.',
            ],
            [
                'full_name'     => 'Peter Mwangi',
                'email_address' => 'pmwangi@aarhealth.co.ke',
                'organisation'  => 'AAR Healthcare',
                'user_role'     => 'Ambulance Dispatcher',
                'phone_number'  => '+254 700 998877',
                'message'       => 'We currently track handovers manually. This would be a great improvement.',
            ],
        ];

        foreach ($signups as $data) {
            // Skip if already seeded (idempotent by email)
            $exists = $model->where('email_address', $data['email_address'])->first();
            if ($exists !== null) {
                continue;
            }

            $entity = new PilotSignup($data);
            $model->save($entity);
        }
    }
}
