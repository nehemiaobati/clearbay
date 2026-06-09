<?php

declare(strict_types=1);

namespace App\Modules\Hospital\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Class ClearBaySeeder
 *
 * Seeds users, ems_providers, and updates hospitals and ambulances.
 */
class ClearBaySeeder extends Seeder
{
    /**
     * Runs the database seeder.
     *
     * @return void
     */
    public function run(): void
    {
        $db = \Config\Database::connect();

        // 1. Seed EMS Providers
        $ems_providers = [
            [
                'name'          => 'AAR Healthcare',
                'type'          => 'Private',
                'contact_phone' => '+254711090000',
                'active'        => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Kenya Red Cross',
                'type'          => 'NGO',
                'contact_phone' => '+254700395395',
                'active'        => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Nairobi County Services',
                'type'          => 'Public',
                'contact_phone' => '+254202222181',
                'active'        => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($ems_providers as $provider) {
            $existing = $db->table('ems_providers')->where('name', $provider['name'])->get()->getRow();
            if (!$existing) {
                $db->table('ems_providers')->insert($provider);
            }
        }

        // Fetch provider IDs
        $aar_id = $db->table('ems_providers')->where('name', 'AAR Healthcare')->get()->getRow()->id;
        $krc_id = $db->table('ems_providers')->where('name', 'Kenya Red Cross')->get()->getRow()->id;
        $nbo_id = $db->table('ems_providers')->where('name', 'Nairobi County Services')->get()->getRow()->id;

        // 2. Update Hospitals with lat/lng and contact details
        $hospitals_update = [
            'KNH' => [
                'lat'            => -1.30130000,
                'lng'            => 36.80800000,
                'address'        => 'Hospital Rd, Nairobi',
                'contact_phone'  => '+254202726300',
                'bays_available' => 3,
                'active'         => 1,
            ],
            'MLK' => [
                'lat'            => -1.27850000,
                'lng'            => 36.90300000,
                'address'        => 'Kangundo Rd, Umoja',
                'contact_phone'  => '+254202100922',
                'bays_available' => 1,
                'active'         => 1,
            ],
            'MBG' => [
                'lat'            => -1.30900000,
                'lng'            => 36.80100000,
                'address'        => 'Mbagathi Way, Nairobi',
                'contact_phone'  => '+254202724712',
                'bays_available' => 0,
                'active'         => 1,
            ],
            'AKU' => [
                'lat'            => -1.26100000,
                'lng'            => 36.80900000,
                'address'        => '3rd Parklands Ave, Nairobi',
                'contact_phone'  => '+254203662000',
                'bays_available' => 5,
                'active'         => 1,
            ],
            'NBO' => [
                'lat'            => -1.29520000,
                'lng'            => 36.80480000,
                'address'        => 'Argwings Kodhek Rd, Nairobi',
                'contact_phone'  => '+254202845000',
                'bays_available' => 4,
                'active'         => 1,
            ],
        ];

        foreach ($hospitals_update as $code => $fields) {
            $db->table('hospitals')->where('code', $code)->update($fields);
        }

        // Fetch hospital IDs
        $knh_id = $db->table('hospitals')->where('code', 'KNH')->get()->getRow()->id;
        $mlk_id = $db->table('hospitals')->where('code', 'MLK')->get()->getRow()->id;
        $mbg_id = $db->table('hospitals')->where('code', 'MBG')->get()->getRow()->id;

        // 3. Update/Seed Ambulances with provider link and positions
        $ambulances_update = [
            'AAR-04' => [
                'ems_provider_id' => $aar_id,
                'registration'    => 'KBY 104A',
                'current_lat'     => -1.30800000,
                'current_lng'     => 36.80200000,
                'status'          => 'Queued',
                'last_updated'    => date('Y-m-d H:i:s'),
            ],
            'KRC-12' => [
                'ems_provider_id' => $krc_id,
                'registration'    => 'KBZ 512B',
                'current_lat'     => -1.29800000,
                'current_lng'     => 36.81500000,
                'status'          => 'Transporting',
                'last_updated'    => date('Y-m-d H:i:s'),
            ],
            'NBO-07' => [
                'ems_provider_id' => $nbo_id,
                'registration'    => 'KCG 007G',
                'current_lat'     => -1.28800000,
                'current_lng'     => 36.88500000,
                'status'          => 'Transporting',
                'last_updated'    => date('Y-m-d H:i:s'),
            ],
            'AAR-09' => [
                'ems_provider_id' => $aar_id,
                'registration'    => 'KBY 109A',
                'current_lat'     => -1.29220000,
                'current_lng'     => 36.80900000,
                'status'          => 'Transporting',
                'last_updated'    => date('Y-m-d H:i:s'),
            ],
            'KRC-05' => [
                'ems_provider_id' => $krc_id,
                'registration'    => 'KBZ 505B',
                'current_lat'     => -1.26100000,
                'current_lng'     => 36.80900000,
                'status'          => 'Available',
                'last_updated'    => date('Y-m-d H:i:s'),
            ],
            'AAR-02' => [
                'ems_provider_id' => $aar_id,
                'registration'    => 'KBY 102A',
                'current_lat'     => -1.30900000,
                'current_lng'     => 36.80100000,
                'status'          => 'Off Duty',
                'last_updated'    => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($ambulances_update as $unit_id => $fields) {
            $db->table('ambulances')->where('unit_id', $unit_id)->update($fields);
        }

        // 4. Seed Users for each of the 5 roles
        $password_hash = password_hash('12345678', PASSWORD_BCRYPT);
        $users = [
            [
                'name'            => 'Nurse Wanjiru',
                'email'           => 'nurse@clearbay.com',
                'password_hash'   => $password_hash,
                'role'            => 'nurse',
                'hospital_id'     => $knh_id,
                'ems_provider_id' => null,
                'active'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'Nurse Atieno',
                'email'           => 'nurse2@clearbay.com',
                'password_hash'   => $password_hash,
                'role'            => 'nurse',
                'hospital_id'     => $mbg_id,
                'ems_provider_id' => null,
                'active'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'KNH Administrator',
                'email'           => 'hospadmin@clearbay.com',
                'password_hash'   => $password_hash,
                'role'            => 'hospital_admin',
                'hospital_id'     => $knh_id,
                'ems_provider_id' => null,
                'active'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'Paramedic Otieno',
                'email'           => 'paramedic@clearbay.com',
                'password_hash'   => $password_hash,
                'role'            => 'paramedic',
                'hospital_id'     => null,
                'ems_provider_id' => $krc_id,
                'active'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'Dispatcher Mwangi',
                'email'           => 'dispatcher@clearbay.com',
                'password_hash'   => $password_hash,
                'role'            => 'dispatcher',
                'hospital_id'     => null,
                'ems_provider_id' => null,
                'active'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'            => 'System Admin',
                'email'           => 'admin@clearbay.com',
                'password_hash'   => $password_hash,
                'role'            => 'admin',
                'hospital_id'     => null,
                'ems_provider_id' => null,
                'active'          => 1,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($users as $user) {
            $existing = $db->table('users')->where('email', $user['email'])->get()->getRow();
            if (!$existing) {
                $db->table('users')->insert($user);
            }
        }
    }
}
