<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'code' => 'VDR-NET-001',
                'name' => 'Nusantara Network Services',
                'description' => 'Managed network infrastructure and field troubleshooting partner.',
                'contact_person_name' => 'Rani Pratama',
                'contact_phone' => '+62-811-1000-900',
                'contact_email' => 'support@nusantara-network.test',
                'address' => 'Jakarta Smart Office Tower, South Jakarta',
            ],
            [
                'code' => 'VDR-ISP-001',
                'name' => 'Metro Fiberlink Indonesia',
                'description' => 'Primary internet uplink, metro ethernet, and branch connectivity provider.',
                'contact_person_name' => 'Dian Putri',
                'contact_phone' => '+62-811-2000-100',
                'contact_email' => 'noc@metrofiberlink.test',
                'address' => 'Central Business District, Jakarta',
            ],
            [
                'code' => 'VDR-SEC-001',
                'name' => 'Sentinel Vision Integrator',
                'description' => 'CCTV, NVR, and security surveillance systems implementation partner.',
                'contact_person_name' => 'Fajar Nugroho',
                'contact_phone' => '+62-811-3000-220',
                'contact_email' => 'care@sentinelvision.test',
                'address' => 'Bekasi Industrial Corridor',
            ],
            [
                'code' => 'VDR-UPS-001',
                'name' => 'PowerGuard Systems',
                'description' => 'UPS, power conditioning, and critical electrical resilience vendor.',
                'contact_person_name' => 'Maya Wijaya',
                'contact_phone' => '+62-811-4000-330',
                'contact_email' => 'service@powerguard.test',
                'address' => 'Surabaya Industrial Estate',
            ],
            [
                'code' => 'VDR-ACS-001',
                'name' => 'GateSecure Automation',
                'description' => 'Gate scanner, access control, and barrier automation specialist.',
                'contact_person_name' => 'Rizky Hidayat',
                'contact_phone' => '+62-811-5000-440',
                'contact_email' => 'support@gatesecure.test',
                'address' => 'Bandung Tech Valley',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::query()->updateOrCreate(
                ['code' => $vendor['code']],
                [
                    'name' => $vendor['name'],
                    'description' => $vendor['description'],
                    'contact_person_name' => $vendor['contact_person_name'],
                    'contact_phone' => $vendor['contact_phone'],
                    'contact_email' => $vendor['contact_email'],
                    'address' => $vendor['address'],
                    'is_active' => true,
                ]
            );
        }
    }
}
