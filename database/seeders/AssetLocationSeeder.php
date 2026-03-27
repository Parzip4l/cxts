<?php

namespace Database\Seeders;

use App\Models\AssetLocation;
use App\Models\Department;
use Illuminate\Database\Seeder;

class AssetLocationSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->pluck('id', 'code');

        $locations = [
            ['code' => 'LOC-JKT-001', 'name' => 'Jakarta Head Office', 'department_code' => 'IT-ENG', 'address' => 'Jl. Sudirman Kav. 88, Jakarta', 'latitude' => -6.2146200, 'longitude' => 106.8451300, 'description' => 'Head office data room, corporate operations floor, and command center.'],
            ['code' => 'LOC-BKS-001', 'name' => 'Bekasi Depot Site', 'department_code' => 'OPS-AREA', 'address' => 'Kawasan Industri Bekasi Timur, Bekasi', 'latitude' => -6.2415860, 'longitude' => 107.0001080, 'description' => 'Operational depot with gate scanner, CCTV, WiFi area, and field support devices.'],
            ['code' => 'LOC-CKR-001', 'name' => 'Cikarang Logistics Hub', 'department_code' => 'OPS-AREA', 'address' => 'Jl. Industri Delta Silicon, Cikarang', 'latitude' => -6.3227300, 'longitude' => 107.1355900, 'description' => 'Large logistics hub with radio backhaul and warehouse coverage.'],
            ['code' => 'LOC-SBY-001', 'name' => 'Surabaya Main Gate', 'department_code' => 'OPS-TERM', 'address' => 'Jl. Perak Barat, Surabaya', 'latitude' => -7.2115000, 'longitude' => 112.7348000, 'description' => 'Main operational gate equipped with surveillance and access control.'],
            ['code' => 'LOC-BDG-001', 'name' => 'Bandung Branch Office', 'department_code' => 'CORP-GA', 'address' => 'Jl. Asia Afrika No. 50, Bandung', 'latitude' => -6.9218000, 'longitude' => 107.6075000, 'description' => 'Regional branch office with office LAN, WiFi, and access control devices.'],
            ['code' => 'LOC-SMG-001', 'name' => 'Semarang Data Room', 'department_code' => 'IT-INF', 'address' => 'Jl. Arteri Yos Sudarso, Semarang', 'latitude' => -6.9667000, 'longitude' => 110.4167000, 'description' => 'Regional infrastructure room hosting server, rack, UPS, and WAN modem.'],
            ['code' => 'LOC-MDN-001', 'name' => 'Medan Yard Control Point', 'department_code' => 'OPS-AREA', 'address' => 'Jl. KL Yos Sudarso, Medan', 'latitude' => 3.5952000, 'longitude' => 98.6722000, 'description' => 'Remote yard control point with CCTV, gate scanner, and satellite backup.'],
            ['code' => 'LOC-MKS-001', 'name' => 'Makassar Operations Hub', 'department_code' => 'OPS-AREA', 'address' => 'Jl. Perintis Kemerdekaan, Makassar', 'latitude' => -5.1477000, 'longitude' => 119.4327000, 'description' => 'Eastern region hub supporting field maintenance and branch connectivity.'],
        ];

        foreach ($locations as $location) {
            AssetLocation::query()->updateOrCreate(
                ['code' => $location['code']],
                [
                    'name' => $location['name'],
                    'address' => $location['address'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'department_id' => $departments[$location['department_code']] ?? null,
                    'description' => $location['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
