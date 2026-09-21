<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OfficerDirectory;

/**
 * Seeds sample officers into the NIS ID Card Portal directory (the local
 * verification source used when no external portal API is configured).
 *
 * Run on an existing database with:
 *   php artisan db:seed --class=OfficerDirectorySeeder
 *
 * Idempotent — safe to run repeatedly. NIS Service Numbers are numeric, 2–5 digits.
 */
class OfficerDirectorySeeder extends Seeder
{
    public function run(): void
    {
        $officers = [
            [
                'service_number' => '48213',
                'rank' => 'Assistant Superintendent of Immigration II (ASI-II)',
                'command' => 'FCT Command, Abuja',
                'first_name' => 'Musa', 'middle_name' => 'Adamu', 'last_name' => 'Ibrahim',
                'gender' => 'Male', 'date_of_birth' => '1988-03-14',
                'phone' => '08034567812', 'email' => 'musa.ibrahim@immigration.gov.ng',
                'nin' => '20345678911', 'marital_status' => 'Married',
                'state' => 'Kano', 'lga' => 'Nassarawa', 'city' => 'Kano',
                'address' => 'No. 14 Zaria Road, Nassarawa GRA, Kano',
            ],
            [
                'service_number' => '9072',
                'rank' => 'Inspector of Immigration (II)',
                'command' => 'Lagos Command, Ikeja',
                'first_name' => 'Grace', 'middle_name' => 'Ngozi', 'last_name' => 'Okonkwo',
                'gender' => 'Female', 'date_of_birth' => '1991-07-22',
                'phone' => '08123456780', 'email' => 'grace.okonkwo@immigration.gov.ng',
                'nin' => '30456789122', 'marital_status' => 'Single',
                'state' => 'Anambra', 'lga' => 'Onitsha North', 'city' => 'Lagos',
                'address' => 'Flat 3B, NIS Barracks, Ikeja, Lagos',
            ],
            [
                'service_number' => '615',
                'rank' => 'Deputy Superintendent of Immigration (DSI)',
                'command' => 'Kaduna Command',
                'first_name' => 'Sagir', 'middle_name' => null, 'last_name' => 'Abdullahi',
                'gender' => 'Male', 'date_of_birth' => '1983-11-02',
                'phone' => '07098765432', 'email' => 'sagir.abdullahi@immigration.gov.ng',
                'nin' => '10567891233', 'marital_status' => 'Married',
                'state' => 'Kaduna', 'lga' => 'Kaduna North', 'city' => 'Kaduna',
                'address' => 'No. 7 Ahmadu Bello Way, Kaduna',
            ],
        ];

        foreach ($officers as $o) {
            OfficerDirectory::updateOrCreate(
                ['service_number' => $o['service_number']],
                $o + ['status' => 'active']
            );
        }

        // Clean up any old sample officers created with the non-numeric format.
        OfficerDirectory::whereIn('service_number', ['NIS/2015/3921', 'NIS/2017/4455', 'NIS/2010/1188'])->delete();

        $this->command?->info('Seeded ' . count($officers) . ' officers into the ID Card Portal directory: ' .
            implode(', ', array_column($officers, 'service_number')));
    }
}
