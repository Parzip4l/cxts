<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\EngineerSchedule;
use App\Models\Inspection;
use App\Models\InspectionEvidence;
use App\Models\InspectionTemplate;
use App\Models\ServiceCatalog;
use App\Models\Shift;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAssignment;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\TicketWorklog;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnterpriseBulkSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('id_ID');

        $this->seedDepartments();
        $this->seedVendors($faker);
        $this->seedShifts();
        $this->seedAssetCategories();
        $this->seedAssetStatuses();
        $this->seedAssetLocations($faker);
        $this->seedUsers($faker);
        $this->seedServices($faker);
        $this->seedAssets($faker);
        $this->seedEngineerSchedules();
        $this->seedInspectionTemplates();
        $this->seedInspections($faker);
        $this->seedTickets($faker);
    }

    private function seedDepartments(): void
    {
        $departments = [
            ['code' => 'IT-ENG', 'name' => 'IT Engineering', 'description' => 'Engineering operation and field support.', 'parent_code' => null],
            ['code' => 'OPS-HD', 'name' => 'Operations Helpdesk', 'description' => 'Operational helpdesk and incident triage.', 'parent_code' => 'IT-ENG'],
            ['code' => 'IT-NOC', 'name' => 'Network Operation Center', 'description' => '24x7 network monitoring and escalation.', 'parent_code' => 'IT-ENG'],
            ['code' => 'IT-INFRA', 'name' => 'Infrastructure Platform', 'description' => 'Server, storage, and virtualization support.', 'parent_code' => 'IT-ENG'],
            ['code' => 'IT-SEC', 'name' => 'Security Operations', 'description' => 'Security monitoring and hardening.', 'parent_code' => 'IT-ENG'],
            ['code' => 'OPS-FIELD', 'name' => 'Field Operations', 'description' => 'On-site engineering execution team.', 'parent_code' => 'OPS-HD'],
            ['code' => 'OPS-INS', 'name' => 'Inspection Operations', 'description' => 'Planned and routine inspection team.', 'parent_code' => 'OPS-HD'],
            ['code' => 'OPS-ASSET', 'name' => 'Asset Governance', 'description' => 'Asset lifecycle and inventory governance.', 'parent_code' => 'OPS-HD'],
            ['code' => 'CORP-FIN', 'name' => 'Finance', 'description' => 'Corporate finance and budget owner.', 'parent_code' => null],
            ['code' => 'CORP-HR', 'name' => 'Human Resources', 'description' => 'Human capital and workforce management.', 'parent_code' => null],
            ['code' => 'CORP-PROC', 'name' => 'Procurement', 'description' => 'Vendor procurement and contract administration.', 'parent_code' => null],
            ['code' => 'CORP-LEGAL', 'name' => 'Legal & Compliance', 'description' => 'Regulation, legal and compliance oversight.', 'parent_code' => null],
            ['code' => 'REG-WEST', 'name' => 'Regional West Area', 'description' => 'West region operational coordination.', 'parent_code' => 'OPS-FIELD'],
            ['code' => 'REG-CENTRAL', 'name' => 'Regional Central Area', 'description' => 'Central region operational coordination.', 'parent_code' => 'OPS-FIELD'],
            ['code' => 'REG-EAST', 'name' => 'Regional East Area', 'description' => 'East region operational coordination.', 'parent_code' => 'OPS-FIELD'],
            ['code' => 'DIGITAL-SVC', 'name' => 'Digital Services', 'description' => 'Digital platform service owner.', 'parent_code' => null],
        ];

        $idByCode = [];

        foreach ($departments as $department) {
            $model = Department::query()->updateOrCreate(
                ['code' => $department['code']],
                [
                    'name' => $department['name'],
                    'description' => $department['description'],
                    'is_active' => true,
                ]
            );

            $idByCode[$department['code']] = $model->id;
        }

        foreach ($departments as $department) {
            if ($department['parent_code'] === null) {
                continue;
            }

            Department::query()
                ->where('code', $department['code'])
                ->update(['parent_department_id' => $idByCode[$department['parent_code']] ?? null]);
        }
    }

    private function seedVendors(object $faker): void
    {
        $vendors = [
            ['code' => 'VDR-NET-001', 'name' => 'PT Nusantara Network Services', 'email' => 'support@nusantara-network.co.id'],
            ['code' => 'VDR-CCTV-001', 'name' => 'PT Visual Guard Teknologi', 'email' => 'care@visualguard.id'],
            ['code' => 'VDR-SW-001', 'name' => 'PT Akses Solusi Mandiri', 'email' => 'support@akses-solusi.co.id'],
            ['code' => 'VDR-SEC-001', 'name' => 'PT Prima Cyber Proteksi', 'email' => 'helpdesk@primacyber.co.id'],
            ['code' => 'VDR-GATE-001', 'name' => 'PT Scan Gate Indonesia', 'email' => 'ops@scangate.id'],
            ['code' => 'VDR-INF-001', 'name' => 'PT Infrastruktur Data Sentra', 'email' => 'support@datasentra.id'],
            ['code' => 'VDR-UPS-001', 'name' => 'PT Power Reliability Nusantara', 'email' => 'service@powerreliability.co.id'],
            ['code' => 'VDR-IOT-001', 'name' => 'PT IoT Integrasi Cerdas', 'email' => 'support@iotcerdas.id'],
            ['code' => 'VDR-ROUTER-001', 'name' => 'PT Metro Routing Systems', 'email' => 'noc@metrorouting.co.id'],
            ['code' => 'VDR-RADIO-001', 'name' => 'PT Radio Backbone Persada', 'email' => 'care@radiobackbone.id'],
            ['code' => 'VDR-SAT-001', 'name' => 'PT Satcom Nusantara', 'email' => 'service@satcomnusantara.co.id'],
            ['code' => 'VDR-CLOUD-001', 'name' => 'PT Cloud Platform Indonesia', 'email' => 'support@cloudplatform.id'],
            ['code' => 'VDR-CABLE-001', 'name' => 'PT Kabel Infrastruktur Prima', 'email' => 'ops@kabelprima.id'],
            ['code' => 'VDR-DC-001', 'name' => 'PT Datacenter Energi Optima', 'email' => 'help@datacenteroptima.co.id'],
            ['code' => 'VDR-CORE-001', 'name' => 'PT CoreNet Integrator', 'email' => 'support@corenet.id'],
            ['code' => 'VDR-FIELD-001', 'name' => 'PT Field Service Mitra', 'email' => 'cs@fieldservice.id'],
        ];

        foreach ($vendors as $vendor) {
            Vendor::query()->updateOrCreate(
                ['code' => $vendor['code']],
                [
                    'name' => $vendor['name'],
                    'description' => 'Primary vendor for managed service and maintenance support.',
                    'contact_person_name' => $faker->name(),
                    'contact_phone' => '+62-8'.(string) random_int(111111111, 999999999),
                    'contact_email' => $vendor['email'],
                    'address' => $faker->address(),
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedShifts(): void
    {
        $shifts = [
            ['code' => 'SHIFT-DAY', 'name' => 'Day Shift', 'start' => '08:00', 'end' => '16:00', 'overnight' => false],
            ['code' => 'SHIFT-EVE', 'name' => 'Evening Shift', 'start' => '16:00', 'end' => '00:00', 'overnight' => false],
            ['code' => 'SHIFT-NIGHT', 'name' => 'Night Shift', 'start' => '00:00', 'end' => '08:00', 'overnight' => true],
            ['code' => 'SHIFT-ONSITE', 'name' => 'Onsite Shift', 'start' => '09:00', 'end' => '17:00', 'overnight' => false],
        ];

        foreach ($shifts as $shift) {
            Shift::query()->updateOrCreate(
                ['code' => $shift['code']],
                [
                    'name' => $shift['name'],
                    'start_time' => $shift['start'],
                    'end_time' => $shift['end'],
                    'break_minutes' => 60,
                    'is_overnight' => $shift['overnight'],
                    'description' => 'Operational shift for engineering resource planning.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAssetCategories(): void
    {
        $categories = [
            ['code' => 'CAT-AP', 'name' => 'Access Point'],
            ['code' => 'CAT-SW', 'name' => 'Switch'],
            ['code' => 'CAT-RT', 'name' => 'Router'],
            ['code' => 'CAT-FW', 'name' => 'Firewall'],
            ['code' => 'CAT-CCTV', 'name' => 'CCTV Camera'],
            ['code' => 'CAT-GATE', 'name' => 'Gate Scanner'],
            ['code' => 'CAT-UPS', 'name' => 'UPS'],
            ['code' => 'CAT-SRV', 'name' => 'Server'],
            ['code' => 'CAT-STO', 'name' => 'Storage'],
            ['code' => 'CAT-NVR', 'name' => 'Network Video Recorder'],
            ['code' => 'CAT-IOT', 'name' => 'IoT Gateway'],
            ['code' => 'CAT-ACS', 'name' => 'Access Control Unit'],
            ['code' => 'CAT-RADIO', 'name' => 'Backhaul Radio'],
            ['code' => 'CAT-MODEM', 'name' => 'Managed Modem'],
            ['code' => 'CAT-RACK', 'name' => 'Rack Facility'],
            ['code' => 'CAT-CABLE', 'name' => 'Structured Cabling'],
        ];

        foreach ($categories as $category) {
            AssetCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => 'Operational asset category for enterprise monitoring.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAssetStatuses(): void
    {
        $statuses = [
            ['code' => 'ACTIVE', 'name' => 'Active', 'is_operational' => true],
            ['code' => 'MAINT', 'name' => 'Maintenance', 'is_operational' => false],
            ['code' => 'STANDBY', 'name' => 'Standby', 'is_operational' => true],
            ['code' => 'DEGRADED', 'name' => 'Degraded', 'is_operational' => true],
            ['code' => 'OFFLINE', 'name' => 'Offline', 'is_operational' => false],
            ['code' => 'RETIRED', 'name' => 'Retired', 'is_operational' => false],
        ];

        foreach ($statuses as $status) {
            AssetStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'description' => 'Asset lifecycle and operational condition status.',
                    'is_operational' => $status['is_operational'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAssetLocations(object $faker): void
    {
        $departmentIds = Department::query()->pluck('id', 'code');

        $locations = [
            ['code' => 'LOC-JKT-001', 'name' => 'Jakarta HQ', 'department_code' => 'IT-ENG'],
            ['code' => 'LOC-JKT-OPS', 'name' => 'Jakarta Operations Center', 'department_code' => 'OPS-HD'],
            ['code' => 'LOC-BDG-001', 'name' => 'Bandung Regional Office', 'department_code' => 'REG-WEST'],
            ['code' => 'LOC-BDG-NOC', 'name' => 'Bandung NOC Site', 'department_code' => 'IT-NOC'],
            ['code' => 'LOC-SBY-001', 'name' => 'Surabaya Regional Office', 'department_code' => 'REG-EAST'],
            ['code' => 'LOC-SMG-001', 'name' => 'Semarang Service Point', 'department_code' => 'REG-CENTRAL'],
            ['code' => 'LOC-DPS-001', 'name' => 'Denpasar Service Point', 'department_code' => 'REG-EAST'],
            ['code' => 'LOC-MKS-001', 'name' => 'Makassar Branch', 'department_code' => 'REG-EAST'],
            ['code' => 'LOC-PLB-001', 'name' => 'Palembang Branch', 'department_code' => 'REG-WEST'],
            ['code' => 'LOC-MDN-001', 'name' => 'Medan Branch', 'department_code' => 'REG-WEST'],
            ['code' => 'LOC-YGY-001', 'name' => 'Yogyakarta Service Point', 'department_code' => 'REG-CENTRAL'],
            ['code' => 'LOC-BPN-001', 'name' => 'Balikpapan Branch', 'department_code' => 'REG-EAST'],
            ['code' => 'LOC-MTR-001', 'name' => 'Mataram Field Hub', 'department_code' => 'REG-EAST'],
            ['code' => 'LOC-BKS-001', 'name' => 'Bekasi Depot Site', 'department_code' => 'OPS-ASSET'],
            ['code' => 'LOC-TGR-001', 'name' => 'Tangerang Maintenance Hub', 'department_code' => 'OPS-FIELD'],
            ['code' => 'LOC-JKT-DC1', 'name' => 'Jakarta Data Center 1', 'department_code' => 'IT-INFRA'],
            ['code' => 'LOC-JKT-DC2', 'name' => 'Jakarta Data Center 2', 'department_code' => 'IT-INFRA'],
            ['code' => 'LOC-BTM-001', 'name' => 'Batam Network Point', 'department_code' => 'REG-WEST'],
        ];

        foreach ($locations as $location) {
            AssetLocation::query()->updateOrCreate(
                ['code' => $location['code']],
                [
                    'name' => $location['name'],
                    'address' => $faker->address(),
                    'department_id' => $departmentIds[$location['department_code']] ?? null,
                    'description' => 'Operational asset location for service delivery and inspections.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedUsers(object $faker): void
    {
        $departmentIds = Department::query()->pluck('id', 'code');

        $fixedUsers = [
            ['name' => 'Super Admin', 'email' => 'superadmin@demo.com', 'role' => 'super_admin', 'department_code' => 'IT-ENG'],
            ['name' => 'Operational Admin', 'email' => 'opsadmin@demo.com', 'role' => 'operational_admin', 'department_code' => 'OPS-HD'],
            ['name' => 'Supervisor', 'email' => 'supervisor@demo.com', 'role' => 'supervisor', 'department_code' => 'IT-NOC'],
            ['name' => 'Engineer One', 'email' => 'engineer1@demo.com', 'role' => 'engineer', 'department_code' => 'OPS-FIELD'],
            ['name' => 'Engineer Two', 'email' => 'engineer2@demo.com', 'role' => 'engineer', 'department_code' => 'OPS-FIELD'],
            ['name' => 'Inspector', 'email' => 'inspector@demo.com', 'role' => 'inspection_officer', 'department_code' => 'OPS-INS'],
            ['name' => 'Requester User', 'email' => 'requester@demo.com', 'role' => 'requester', 'department_code' => 'OPS-HD'],
            ['name' => 'CXTS Demo Admin', 'email' => 'user@demo.com', 'role' => 'super_admin', 'department_code' => 'IT-ENG'],
        ];

        foreach ($fixedUsers as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'department_id' => $departmentIds[$user['department_code']] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }

        $engineerNames = [
            'Andi Pranata', 'Budi Santoso', 'Citra Lestari', 'Deni Firmansyah',
            'Eko Saputra', 'Farhan Ramadhan', 'Gilang Prasetyo', 'Hendra Wijaya',
            'Irfan Setiawan', 'Joko Maulana', 'Kiki Rahmawati', 'Lutfi Hidayat',
        ];

        foreach ($engineerNames as $index => $name) {
            User::query()->updateOrCreate(
                ['email' => 'engineer.'.Str::slug($name, '.').'@taplox.co.id'],
                [
                    'name' => $name,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'engineer',
                    'department_id' => $departmentIds[$index % 2 === 0 ? 'OPS-FIELD' : 'IT-ENG'] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }

        $inspectionNames = ['Rina Maharani', 'Sari Kurnia', 'Taufik Hidayat', 'Vina Ardila', 'Yoga Pratama', 'Zahra Nabila'];
        foreach ($inspectionNames as $name) {
            User::query()->updateOrCreate(
                ['email' => 'inspector.'.Str::slug($name, '.').'@taplox.co.id'],
                [
                    'name' => $name,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'inspection_officer',
                    'department_id' => $departmentIds['OPS-INS'] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }

        $requesterDepartments = ['CORP-FIN', 'CORP-HR', 'CORP-PROC', 'DIGITAL-SVC', 'OPS-HD'];
        for ($i = 1; $i <= 10; $i++) {
            $departmentCode = $requesterDepartments[$i % count($requesterDepartments)];

            User::query()->updateOrCreate(
                ['email' => sprintf('requester%02d@taplox.co.id', $i)],
                [
                    'name' => $faker->name(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => 'requester',
                    'department_id' => $departmentIds[$departmentCode] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }

        $supervisors = [
            ['email' => 'supervisor.noc@taplox.co.id', 'name' => 'Rizky Nurhalim', 'department_code' => 'IT-NOC'],
            ['email' => 'supervisor.field@taplox.co.id', 'name' => 'Dewi Puspita', 'department_code' => 'OPS-FIELD'],
            ['email' => 'opsadmin2@demo.com', 'name' => 'Operations Admin 2', 'department_code' => 'OPS-HD'],
        ];

        foreach ($supervisors as $supervisor) {
            User::query()->updateOrCreate(
                ['email' => $supervisor['email']],
                [
                    'name' => $supervisor['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => str_starts_with($supervisor['email'], 'opsadmin') ? 'operational_admin' : 'supervisor',
                    'department_id' => $departmentIds[$supervisor['department_code']] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }
    }

    private function seedServices(object $faker): void
    {
        $departmentIds = Department::query()->pluck('id', 'code');
        $vendorIds = Vendor::query()->pluck('id', 'code');
        $managerIds = User::query()
            ->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->all();

        $services = [
            ['code' => 'SRV-WIFI-CORE', 'name' => 'Public WiFi Core Service', 'category' => 'Connectivity', 'ownership' => ServiceCatalog::OWNERSHIP_HYBRID, 'department' => 'IT-ENG', 'vendor' => 'VDR-NET-001'],
            ['code' => 'SRV-WIFI-AREA', 'name' => 'Managed Area Hotspot Service', 'category' => 'Connectivity', 'ownership' => ServiceCatalog::OWNERSHIP_HYBRID, 'department' => 'OPS-FIELD', 'vendor' => 'VDR-NET-001'],
            ['code' => 'SRV-CCTV-MON', 'name' => 'CCTV Monitoring Service', 'category' => 'Surveillance', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-SEC', 'vendor' => 'VDR-CCTV-001'],
            ['code' => 'SRV-GATE-SCAN', 'name' => 'Gate Scanner Operation Service', 'category' => 'Security', 'ownership' => ServiceCatalog::OWNERSHIP_HYBRID, 'department' => 'OPS-INS', 'vendor' => 'VDR-GATE-001'],
            ['code' => 'SRV-LAN-EDGE', 'name' => 'LAN Edge Switching Service', 'category' => 'Infrastructure', 'ownership' => ServiceCatalog::OWNERSHIP_INTERNAL, 'department' => 'IT-NOC', 'vendor' => 'VDR-SW-001'],
            ['code' => 'SRV-WAN-UPLINK', 'name' => 'WAN Uplink Service', 'category' => 'Connectivity', 'ownership' => ServiceCatalog::OWNERSHIP_HYBRID, 'department' => 'IT-NOC', 'vendor' => 'VDR-ROUTER-001'],
            ['code' => 'SRV-FW-PERIM', 'name' => 'Perimeter Firewall Service', 'category' => 'Security', 'ownership' => ServiceCatalog::OWNERSHIP_INTERNAL, 'department' => 'IT-SEC', 'vendor' => 'VDR-SEC-001'],
            ['code' => 'SRV-UPS-POWER', 'name' => 'UPS Power Reliability Service', 'category' => 'Facility', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-INFRA', 'vendor' => 'VDR-UPS-001'],
            ['code' => 'SRV-DC-NET', 'name' => 'Datacenter Network Service', 'category' => 'Infrastructure', 'ownership' => ServiceCatalog::OWNERSHIP_INTERNAL, 'department' => 'IT-INFRA', 'vendor' => 'VDR-DC-001'],
            ['code' => 'SRV-IOT-EDGE', 'name' => 'IoT Edge Gateway Service', 'category' => 'Digital', 'ownership' => ServiceCatalog::OWNERSHIP_HYBRID, 'department' => 'DIGITAL-SVC', 'vendor' => 'VDR-IOT-001'],
            ['code' => 'SRV-ACS-CONTROL', 'name' => 'Access Control System Service', 'category' => 'Security', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-SEC', 'vendor' => 'VDR-GATE-001'],
            ['code' => 'SRV-NVR-STORE', 'name' => 'NVR Storage Service', 'category' => 'Surveillance', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-INFRA', 'vendor' => 'VDR-CCTV-001'],
            ['code' => 'SRV-RADIO-BH', 'name' => 'Backhaul Radio Link Service', 'category' => 'Connectivity', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-NOC', 'vendor' => 'VDR-RADIO-001'],
            ['code' => 'SRV-SAT-BACKUP', 'name' => 'Satellite Backup Service', 'category' => 'Connectivity', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-NOC', 'vendor' => 'VDR-SAT-001'],
            ['code' => 'SRV-CLOUD-GW', 'name' => 'Cloud Gateway Service', 'category' => 'Digital', 'ownership' => ServiceCatalog::OWNERSHIP_HYBRID, 'department' => 'DIGITAL-SVC', 'vendor' => 'VDR-CLOUD-001'],
            ['code' => 'SRV-FIELD-MAINT', 'name' => 'Field Preventive Maintenance Service', 'category' => 'Operations', 'ownership' => ServiceCatalog::OWNERSHIP_INTERNAL, 'department' => 'OPS-FIELD', 'vendor' => 'VDR-FIELD-001'],
            ['code' => 'SRV-ASSET-DEPLOY', 'name' => 'Asset Deployment Service', 'category' => 'Operations', 'ownership' => ServiceCatalog::OWNERSHIP_INTERNAL, 'department' => 'OPS-ASSET', 'vendor' => 'VDR-FIELD-001'],
            ['code' => 'SRV-CABLE-MGMT', 'name' => 'Structured Cabling Service', 'category' => 'Infrastructure', 'ownership' => ServiceCatalog::OWNERSHIP_VENDOR, 'department' => 'IT-INFRA', 'vendor' => 'VDR-CABLE-001'],
        ];

        foreach ($services as $index => $service) {
            ServiceCatalog::query()->updateOrCreate(
                ['code' => $service['code']],
                [
                    'name' => $service['name'],
                    'service_category' => $service['category'],
                    'description' => $faker->sentence(10),
                    'ownership_model' => $service['ownership'],
                    'department_owner_id' => $departmentIds[$service['department']] ?? null,
                    'vendor_id' => $vendorIds[$service['vendor']] ?? null,
                    'service_manager_user_id' => $managerIds[$index % max(1, count($managerIds))] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAssets(object $faker): void
    {
        $categories = AssetCategory::query()->orderBy('id')->get(['id', 'code', 'name']);
        $statuses = AssetStatus::query()->pluck('id', 'code');
        $services = ServiceCatalog::query()->orderBy('id')->pluck('id')->all();
        $vendors = Vendor::query()->orderBy('id')->pluck('id')->all();
        $locations = AssetLocation::query()->orderBy('id')->get(['id', 'code', 'name', 'department_id']);
        $departments = Department::query()->orderBy('id')->pluck('id')->all();

        if ($categories->isEmpty() || $locations->isEmpty()) {
            return;
        }

        $brands = ['Cisco', 'Juniper', 'Aruba', 'MikroTik', 'Fortinet', 'Huawei', 'Axis', 'Bosch', 'HP', 'Dell'];
        $models = ['X100', 'X200', 'Edge-24', 'Secure-500', 'NOC-Pro', 'Vision-1080', 'CloudGate', 'NetCore-10'];
        $criticalities = Asset::criticalityOptions();

        for ($i = 1; $i <= 48; $i++) {
            $category = $categories[$i % $categories->count()];
            $location = $locations[$i % $locations->count()];
            $brand = $brands[$i % count($brands)];
            $model = $models[$i % count($models)];
            $installDate = Carbon::today()->subDays(180 + ($i * 21));

            $statusCode = match (true) {
                $i % 17 === 0 => 'RETIRED',
                $i % 11 === 0 => 'OFFLINE',
                $i % 7 === 0 => 'DEGRADED',
                $i % 5 === 0 => 'MAINT',
                default => 'ACTIVE',
            };

            Asset::query()->updateOrCreate(
                ['code' => sprintf('AST-OPS-%04d', $i)],
                [
                    'name' => sprintf('%s %s Unit %02d', $category->name, $location->name, ($i % 12) + 1),
                    'asset_category_id' => $category->id,
                    'service_id' => $services[$i % max(1, count($services))] ?? null,
                    'department_owner_id' => $location->department_id ?? $departments[$i % max(1, count($departments))] ?? null,
                    'vendor_id' => $vendors[$i % max(1, count($vendors))] ?? null,
                    'asset_location_id' => $location->id,
                    'serial_number' => sprintf('SN-OPS-%06d', $i),
                    'brand' => $brand,
                    'model' => $model,
                    'install_date' => $installDate->toDateString(),
                    'warranty_end_date' => $installDate->copy()->addYears(3)->toDateString(),
                    'criticality' => $criticalities[$i % count($criticalities)],
                    'asset_status_id' => $statuses[$statusCode] ?? $statuses['ACTIVE'] ?? null,
                    'notes' => $faker->sentence(10),
                    'is_active' => $statusCode !== 'RETIRED',
                ]
            );
        }
    }

    private function seedEngineerSchedules(): void
    {
        $engineers = User::query()->where('role', 'engineer')->orderBy('id')->pluck('id')->all();
        $shiftIds = Shift::query()->pluck('id', 'code');
        $assignedById = User::query()->where('email', 'superadmin@demo.com')->value('id')
            ?? User::query()->where('role', 'super_admin')->value('id');

        if ($engineers === [] || $shiftIds->isEmpty()) {
            return;
        }

        $start = Carbon::today();

        foreach ($engineers as $engineerId) {
            for ($offset = 0; $offset < 21; $offset++) {
                $workDate = $start->copy()->addDays($offset);
                $isWeekend = in_array($workDate->dayOfWeekIso, [6, 7], true);

                $status = match (true) {
                    $isWeekend => EngineerSchedule::STATUS_OFF,
                    $offset % 13 === 0 => EngineerSchedule::STATUS_LEAVE,
                    $offset % 9 === 0 => EngineerSchedule::STATUS_SICK,
                    default => EngineerSchedule::STATUS_ASSIGNED,
                };

                $shiftCode = match ($offset % 3) {
                    0 => 'SHIFT-DAY',
                    1 => 'SHIFT-EVE',
                    default => 'SHIFT-NIGHT',
                };

                EngineerSchedule::query()->updateOrCreate(
                    [
                        'user_id' => $engineerId,
                        'work_date' => $workDate->toDateString(),
                    ],
                    [
                        'shift_id' => $shiftIds[$shiftCode] ?? $shiftIds->first(),
                        'status' => $status,
                        'notes' => $status === EngineerSchedule::STATUS_ASSIGNED
                            ? 'Scheduled from enterprise realistic seed data.'
                            : 'Generated schedule status: '.$status,
                        'assigned_by_id' => $assignedById,
                    ]
                );
            }
        }
    }

    private function seedInspectionTemplates(): void
    {
        $categoryIds = AssetCategory::query()->pluck('id', 'code');

        $templates = [
            ['code' => 'INSP-WIFI-DAILY', 'name' => 'Daily Free Public WiFi Inspection', 'category' => 'CAT-AP'],
            ['code' => 'INSP-AP-SIGNAL', 'name' => 'Access Point Signal Health Check', 'category' => 'CAT-AP'],
            ['code' => 'INSP-SWITCH-EDGE', 'name' => 'Edge Switch Daily Inspection', 'category' => 'CAT-SW'],
            ['code' => 'INSP-ROUTER-UPLINK', 'name' => 'Router Uplink Stability Inspection', 'category' => 'CAT-RT'],
            ['code' => 'INSP-FW-RULE', 'name' => 'Firewall Rule & Session Inspection', 'category' => 'CAT-FW'],
            ['code' => 'INSP-CCTV-DAILY', 'name' => 'CCTV Daily Operational Inspection', 'category' => 'CAT-CCTV'],
            ['code' => 'INSP-GATE-ENTRY', 'name' => 'Gate Scanner Entry Inspection', 'category' => 'CAT-GATE'],
            ['code' => 'INSP-UPS-RUNTIME', 'name' => 'UPS Runtime and Battery Inspection', 'category' => 'CAT-UPS'],
            ['code' => 'INSP-SERVER-ROOM', 'name' => 'Server Room Equipment Inspection', 'category' => 'CAT-SRV'],
            ['code' => 'INSP-STORAGE-IO', 'name' => 'Storage I/O and Capacity Inspection', 'category' => 'CAT-STO'],
            ['code' => 'INSP-NVR-REC', 'name' => 'NVR Recording Integrity Inspection', 'category' => 'CAT-NVR'],
            ['code' => 'INSP-IOT-NODE', 'name' => 'IoT Gateway Node Inspection', 'category' => 'CAT-IOT'],
            ['code' => 'INSP-ACS-DOOR', 'name' => 'Access Control Door Inspection', 'category' => 'CAT-ACS'],
            ['code' => 'INSP-RADIO-LINK', 'name' => 'Backhaul Radio Link Inspection', 'category' => 'CAT-RADIO'],
            ['code' => 'INSP-MODEM-LINE', 'name' => 'Managed Modem Line Quality Inspection', 'category' => 'CAT-MODEM'],
            ['code' => 'INSP-CABLE-TRAY', 'name' => 'Structured Cabling Inspection', 'category' => 'CAT-CABLE'],
        ];

        foreach ($templates as $templateData) {
            $template = InspectionTemplate::query()->updateOrCreate(
                ['code' => $templateData['code']],
                [
                    'name' => $templateData['name'],
                    'description' => 'Routine inspection template for asset operation readiness.',
                    'asset_category_id' => $categoryIds[$templateData['category']] ?? null,
                    'is_active' => true,
                ]
            );

            $template->items()->delete();

            $items = [
                ['sequence' => 1, 'item_label' => 'Power indicator is normal', 'item_type' => 'boolean', 'expected_value' => 'ON'],
                ['sequence' => 2, 'item_label' => 'Device reachable from monitoring network', 'item_type' => 'boolean', 'expected_value' => 'REACHABLE'],
                ['sequence' => 3, 'item_label' => 'Operational metric threshold', 'item_type' => 'number', 'expected_value' => '<80'],
                ['sequence' => 4, 'item_label' => 'Physical condition is acceptable', 'item_type' => 'boolean', 'expected_value' => 'PASS'],
                ['sequence' => 5, 'item_label' => 'Inspector notes', 'item_type' => 'text', 'expected_value' => null],
            ];

            foreach ($items as $item) {
                $template->items()->create([
                    ...$item,
                    'is_required' => true,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function seedInspections(object $faker): void
    {
        $templates = InspectionTemplate::query()->with('items')->where('is_active', true)->get();
        $assets = Asset::query()->get(['id', 'asset_category_id', 'asset_location_id']);
        $officerIds = User::query()
            ->whereIn('role', ['inspection_officer', 'engineer'])
            ->orderBy('id')
            ->pluck('id')
            ->all();
        $schedulerIds = User::query()
            ->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($templates->isEmpty() || $officerIds === []) {
            return;
        }

        for ($i = 1; $i <= 30; $i++) {
            $template = $templates[$i % $templates->count()];
            $asset = $assets
                ->firstWhere('asset_category_id', $template->asset_category_id)
                ?? $assets[$i % $assets->count()];

            $status = match (true) {
                $i % 5 === 0 => Inspection::STATUS_DRAFT,
                $i % 4 === 0 => Inspection::STATUS_IN_PROGRESS,
                default => Inspection::STATUS_SUBMITTED,
            };

            $finalResult = $status === Inspection::STATUS_SUBMITTED
                ? ($i % 6 === 0 ? Inspection::FINAL_RESULT_ABNORMAL : Inspection::FINAL_RESULT_NORMAL)
                : null;

            $inspectionDate = match ($status) {
                Inspection::STATUS_DRAFT => Carbon::today()->addDays($i % 5),
                default => Carbon::today()->subDays($i % 12),
            };

            $scheduleType = $i % 3 === 0 ? Inspection::SCHEDULE_TYPE_WEEKLY : Inspection::SCHEDULE_TYPE_DAILY;
            $scheduleInterval = $i % 3 === 0 ? 1 : 2;
            $scheduleWeekdays = $scheduleType === Inspection::SCHEDULE_TYPE_WEEKLY ? [1, 3, 5] : null;

            $inspection = Inspection::query()->updateOrCreate(
                ['inspection_number' => sprintf('INSP-OPS-%04d', $i)],
                [
                    'inspection_template_id' => $template->id,
                    'asset_id' => $asset?->id,
                    'asset_location_id' => $asset?->asset_location_id,
                    'inspection_officer_id' => $officerIds[$i % count($officerIds)],
                    'scheduled_by_id' => $schedulerIds[$i % max(1, count($schedulerIds))] ?? null,
                    'inspection_date' => $inspectionDate->toDateString(),
                    'schedule_next_date' => $status === Inspection::STATUS_SUBMITTED ? Carbon::parse($inspectionDate)->addDays($scheduleInterval)->toDateString() : null,
                    'status' => $status,
                    'schedule_type' => $scheduleType,
                    'schedule_interval' => $scheduleInterval,
                    'schedule_weekdays' => $scheduleWeekdays,
                    'final_result' => $finalResult,
                    'started_at' => $status !== Inspection::STATUS_DRAFT ? Carbon::parse($inspectionDate)->setTime(9, 0) : null,
                    'submitted_at' => $status === Inspection::STATUS_SUBMITTED ? Carbon::parse($inspectionDate)->setTime(10, 10) : null,
                    'summary_notes' => $finalResult === Inspection::FINAL_RESULT_ABNORMAL
                        ? 'Ditemukan kondisi abnormal dan perlu follow-up ticket.'
                        : $faker->sentence(12),
                    'created_by_id' => $officerIds[$i % count($officerIds)],
                    'updated_by_id' => $officerIds[$i % count($officerIds)],
                ]
            );

            $inspection->items()->delete();
            foreach ($template->items as $templateItem) {
                $resultStatus = null;
                $resultValue = null;
                $checkedAt = null;
                $notes = null;

                if ($status === Inspection::STATUS_SUBMITTED) {
                    $isFail = $finalResult === Inspection::FINAL_RESULT_ABNORMAL && $templateItem->sequence === 2;
                    $resultStatus = $isFail ? 'fail' : 'pass';
                    $resultValue = $isFail ? 'FAIL' : 'PASS';
                    $checkedAt = Carbon::parse($inspectionDate)->setTime(9, 45);
                    $notes = $isFail ? 'Intermitten packet loss terdeteksi saat pemeriksaan.' : 'Hasil pemeriksaan normal.';
                }

                $inspection->items()->create([
                    'inspection_template_item_id' => $templateItem->id,
                    'sequence' => $templateItem->sequence,
                    'item_label' => $templateItem->item_label,
                    'item_type' => $templateItem->item_type,
                    'expected_value' => $templateItem->expected_value,
                    'result_status' => $resultStatus,
                    'result_value' => $resultValue,
                    'notes' => $notes,
                    'checked_at' => $checkedAt,
                    'checked_by_id' => $inspection->inspection_officer_id,
                ]);
            }

            if ($finalResult === Inspection::FINAL_RESULT_ABNORMAL) {
                $failedItemId = $inspection->items()->where('result_status', 'fail')->value('id');

                InspectionEvidence::query()->updateOrCreate(
                    [
                        'inspection_id' => $inspection->id,
                        'original_name' => sprintf('abnormal-inspection-%04d.jpg', $i),
                    ],
                    [
                        'inspection_item_id' => $failedItemId,
                        'uploaded_by_id' => $inspection->inspection_officer_id,
                        'file_path' => sprintf('inspection-evidences/abnormal-inspection-%04d.jpg', $i),
                        'mime_type' => 'image/jpeg',
                        'file_size' => 285000 + ($i * 100),
                        'notes' => 'Foto temuan abnormal saat inspeksi lapangan.',
                    ]
                );
            }
        }
    }

    private function seedTickets(object $faker): void
    {
        $requesters = User::query()->where('role', 'requester')->pluck('id')->all();
        $engineers = User::query()->where('role', 'engineer')->pluck('id')->all();
        $supervisors = User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->pluck('id')->all();
        $categories = TicketCategory::query()->pluck('id', 'code');
        $priorities = TicketPriority::query()->pluck('id', 'code');
        $statuses = TicketStatus::query()->pluck('id', 'code');
        $assets = Asset::query()->pluck('id')->all();
        $locations = AssetLocation::query()->pluck('id')->all();
        $services = ServiceCatalog::query()->pluck('id')->all();

        if ($requesters === [] || $categories->isEmpty() || $priorities->isEmpty() || $statuses->isEmpty()) {
            return;
        }

        $subcategoryByCategory = TicketSubcategory::query()
            ->get(['id', 'ticket_category_id'])
            ->groupBy('ticket_category_id')
            ->map(fn ($items) => $items->pluck('id')->values()->all())
            ->all();
        $detailSubcategoryByCategory = TicketDetailSubcategory::query()
            ->get(['id', 'ticket_subcategory_id'])
            ->groupBy('ticket_subcategory_id')
            ->map(fn ($items) => $items->pluck('id')->values()->all())
            ->all();

        $abnormalInspections = Inspection::query()
            ->where('final_result', Inspection::FINAL_RESULT_ABNORMAL)
            ->orderBy('id')
            ->get(['id', 'inspection_number']);

        $titleTemplates = [
            'WiFi tidak stabil di area operasional',
            'Latency tinggi pada akses internet kantor',
            'Switch edge mengalami port flapping',
            'CCTV offline di titik pengawasan utama',
            'Gate scanner gagal membaca kartu akses',
            'Firewall alarm high session utilization',
            'UPS alarm battery health warning',
            'Router uplink mengalami packet loss',
            'Permintaan instalasi AP baru area meeting room',
            'Permintaan relokasi perangkat jaringan ke lantai 2',
        ];
        $standardSlaPolicy = SlaPolicy::query()->where('name', 'STANDARD_24X7')->first();

        for ($i = 1; $i <= 36; $i++) {
            $createdAt = Carbon::now()->subDays($i % 14)->subMinutes($i * 5);
            $requesterId = $requesters[$i % count($requesters)];

            $categoryCode = match (true) {
                $i % 7 === 0 => 'MAINTENANCE',
                $i % 5 === 0 => 'REQUEST',
                default => 'INCIDENT',
            };
            $categoryId = $categories[$categoryCode] ?? $categories->first();
            $subcategories = $subcategoryByCategory[$categoryId] ?? [];
            $selectedSubcategoryId = $subcategories[$i % max(1, count($subcategories))] ?? null;
            $detailSubcategories = $selectedSubcategoryId !== null
                ? ($detailSubcategoryByCategory[$selectedSubcategoryId] ?? [])
                : [];

            $priorityCode = match (true) {
                $i % 11 === 0 => 'P1',
                $i % 4 === 0 => 'P2',
                $i % 3 === 0 => 'P3',
                default => 'P4',
            };

            $statusCode = match (true) {
                $i % 9 === 0 => 'CLOSED',
                $i % 8 === 0 => 'COMPLETED',
                $i % 5 === 0 => 'ON_HOLD',
                $i % 4 === 0 => 'IN_PROGRESS',
                $i % 3 === 0 => 'ASSIGNED',
                default => 'NEW',
            };

            $assignedEngineerId = in_array($statusCode, ['ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'COMPLETED', 'CLOSED'], true)
                ? ($engineers[$i % max(1, count($engineers))] ?? null)
                : null;

            $startedAt = in_array($statusCode, ['IN_PROGRESS', 'ON_HOLD', 'COMPLETED', 'CLOSED'], true)
                ? $createdAt->copy()->addMinutes(30)
                : null;
            $completedAt = in_array($statusCode, ['COMPLETED', 'CLOSED'], true)
                ? $createdAt->copy()->addHours(3)
                : null;
            $closedAt = $statusCode === 'CLOSED'
                ? $createdAt->copy()->addHours(4)
                : null;
            $responseDueAt = $createdAt->copy()->addMinutes(45);
            $resolutionDueAt = $createdAt->copy()->addHours(8);
            $responseBreachedAt = $startedAt !== null && $startedAt->gt($responseDueAt)
                ? $responseDueAt
                : ($startedAt === null && $responseDueAt->lt(Carbon::now()) ? $responseDueAt : null);
            $resolutionBreachedAt = $completedAt !== null && $completedAt->gt($resolutionDueAt)
                ? $resolutionDueAt
                : ($completedAt === null && $resolutionDueAt->lt(Carbon::now()) ? $resolutionDueAt : null);
            $slaStatus = $responseBreachedAt !== null || $resolutionBreachedAt !== null
                ? Ticket::SLA_STATUS_BREACHED
                : Ticket::SLA_STATUS_ON_TIME;

            $inspection = $abnormalInspections->get(($i - 1) % max(1, $abnormalInspections->count()));
            $inspectionId = $i <= $abnormalInspections->count() ? $inspection?->id : null;

            $ticket = Ticket::query()->updateOrCreate(
                ['ticket_number' => sprintf('TCK-OPS-%04d', $i)],
                [
                    'title' => $inspectionId !== null
                        ? sprintf('Follow-up abnormal inspection %s', $inspection?->inspection_number)
                        : $titleTemplates[$i % count($titleTemplates)],
                    'description' => $inspectionId !== null
                        ? 'Tiket otomatis untuk tindak lanjut hasil inspeksi abnormal.'
                        : $faker->paragraph(),
                    'requester_id' => $requesterId,
                    'requester_department_id' => User::query()->whereKey($requesterId)->value('department_id'),
                    'ticket_category_id' => $categoryId,
                    'ticket_subcategory_id' => $selectedSubcategoryId,
                    'ticket_detail_subcategory_id' => $detailSubcategories[$i % max(1, count($detailSubcategories))] ?? null,
                    'ticket_priority_id' => $priorities[$priorityCode] ?? $priorities->first(),
                    'service_id' => $services[$i % max(1, count($services))] ?? null,
                    'asset_id' => $assets[$i % max(1, count($assets))] ?? null,
                    'asset_location_id' => $locations[$i % max(1, count($locations))] ?? null,
                    'inspection_id' => $inspectionId,
                    'ticket_status_id' => $statuses[$statusCode] ?? $statuses->first(),
                    'assigned_team_name' => $assignedEngineerId ? 'Engineering Operations' : null,
                    'assigned_engineer_id' => $assignedEngineerId,
                    'sla_policy_id' => $standardSlaPolicy?->id,
                    'sla_policy_name' => 'STANDARD_24X7',
                    'sla_name_snapshot' => $standardSlaPolicy?->name ?? 'STANDARD_24X7',
                    'response_due_at' => $responseDueAt,
                    'responded_at' => $startedAt,
                    'breached_response_at' => $responseBreachedAt,
                    'resolution_due_at' => $resolutionDueAt,
                    'source' => $i % 3 === 0 ? 'api' : 'web',
                    'impact' => ['low', 'medium', 'high'][$i % 3],
                    'urgency' => ['low', 'medium', 'high'][($i + 1) % 3],
                    'started_at' => $startedAt,
                    'resolved_at' => $completedAt,
                    'sla_status' => $slaStatus,
                    'breached_resolution_at' => $resolutionBreachedAt,
                    'completed_at' => $completedAt,
                    'closed_at' => $closedAt,
                    'last_status_changed_at' => $closedAt ?? $completedAt ?? $startedAt ?? $createdAt,
                    'created_by_id' => $requesterId,
                    'updated_by_id' => $supervisors[$i % max(1, count($supervisors))] ?? null,
                    'created_at' => $createdAt,
                    'updated_at' => $closedAt ?? $completedAt ?? $createdAt->copy()->addHours(1),
                ]
            );

            TicketActivity::query()->updateOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'activity_type' => 'ticket_created',
                ],
                [
                    'actor_user_id' => $requesterId,
                    'old_status_id' => null,
                    'new_status_id' => $ticket->ticket_status_id,
                    'metadata' => ['source' => 'enterprise_realistic_seeder'],
                ]
            );

            if ($assignedEngineerId !== null) {
                TicketAssignment::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'assigned_engineer_id' => $assignedEngineerId,
                    ],
                    [
                        'previous_engineer_id' => null,
                        'assigned_by_id' => $supervisors[$i % max(1, count($supervisors))] ?? null,
                        'assigned_at' => $createdAt->copy()->addMinutes(15),
                        'notes' => 'Auto seeded assignment for engineering execution.',
                    ]
                );

                TicketActivity::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'activity_type' => 'ticket_assigned',
                    ],
                    [
                        'actor_user_id' => $supervisors[$i % max(1, count($supervisors))] ?? null,
                        'old_status_id' => $statuses['NEW'] ?? null,
                        'new_status_id' => $ticket->ticket_status_id,
                        'metadata' => [
                            'assigned_engineer_id' => $assignedEngineerId,
                            'assigned_team_name' => 'Engineering Operations',
                        ],
                    ]
                );
            }

            if ($startedAt !== null) {
                TicketWorklog::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'description' => 'Initial diagnosis and corrective action started.',
                    ],
                    [
                        'user_id' => $assignedEngineerId,
                        'log_type' => 'progress',
                        'started_at' => $startedAt,
                        'ended_at' => $completedAt,
                        'duration_minutes' => $completedAt !== null ? $startedAt->diffInMinutes($completedAt) : 60,
                    ]
                );

                TicketActivity::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'activity_type' => 'work_started',
                    ],
                    [
                        'actor_user_id' => $assignedEngineerId,
                        'old_status_id' => $statuses['ASSIGNED'] ?? null,
                        'new_status_id' => $statuses['IN_PROGRESS'] ?? null,
                        'metadata' => ['channel' => 'mobile_engineering'],
                    ]
                );
            }

            if ($completedAt !== null) {
                TicketActivity::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'activity_type' => 'work_completed',
                    ],
                    [
                        'actor_user_id' => $assignedEngineerId,
                        'old_status_id' => $statuses['IN_PROGRESS'] ?? null,
                        'new_status_id' => $statuses[$statusCode] ?? null,
                        'metadata' => ['result' => 'resolved'],
                    ]
                );
            }
        }
    }
}
