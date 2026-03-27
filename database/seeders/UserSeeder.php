<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->pluck('id', 'code');

        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@demo.com', 'role' => 'super_admin', 'department_code' => 'IT-ENG'],
            ['name' => 'Nadia Permata', 'email' => 'opsadmin@demo.com', 'role' => 'operational_admin', 'department_code' => 'OPS-HD'],
            ['name' => 'Arif Setiawan', 'email' => 'supervisor@demo.com', 'role' => 'supervisor', 'department_code' => 'IT-ENG'],
            ['name' => 'Andi Pranata', 'email' => 'engineer1@demo.com', 'role' => 'engineer', 'department_code' => 'IT-FOPS'],
            ['name' => 'Citra Lestari', 'email' => 'engineer2@demo.com', 'role' => 'engineer', 'department_code' => 'IT-FOPS'],
            ['name' => 'Kiki Rahmawati', 'email' => 'kiki.rahmawati@demo.com', 'role' => 'engineer', 'department_code' => 'IT-NOC'],
            ['name' => 'Eko Saputra', 'email' => 'eko.saputra@demo.com', 'role' => 'engineer', 'department_code' => 'IT-INF'],
            ['name' => 'Gilang Prasetyo', 'email' => 'gilang.prasetyo@demo.com', 'role' => 'engineer', 'department_code' => 'IT-SEC'],
            ['name' => 'Irfan Setiawan', 'email' => 'irfan.setiawan@demo.com', 'role' => 'engineer', 'department_code' => 'IT-NOC'],
            ['name' => 'Ade Puspa Agustina S.Gz', 'email' => 'ade.puspa@demo.com', 'role' => 'supervisor', 'department_code' => 'OPS-HD'],
            ['name' => 'Rizal Kurniawan', 'email' => 'inspector@demo.com', 'role' => 'inspection_officer', 'department_code' => 'IT-FOPS'],
            ['name' => 'Bagas Nugraha', 'email' => 'requester@demo.com', 'role' => 'requester', 'department_code' => 'OPS-TERM'],
            ['name' => 'Sarah Maharani', 'email' => 'sarah.maharani@demo.com', 'role' => 'requester', 'department_code' => 'CORP-GA'],
            ['name' => 'Dini Febrianti', 'email' => 'dini.febrianti@demo.com', 'role' => 'requester', 'department_code' => 'FIN-PROC'],
            ['name' => 'CXTS Demo Owner', 'email' => 'user@demo.com', 'role' => 'super_admin', 'department_code' => 'IT-ENG'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'department_id' => $departments[$user['department_code']] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }

        $this->syncDepartmentHeads();
        $this->syncServiceManagers();
    }

    private function syncDepartmentHeads(): void
    {
        $headMap = [
            'IT-ENG' => 'supervisor@demo.com',
            'OPS-HD' => 'opsadmin@demo.com',
            'IT-NOC' => 'irfan.setiawan@demo.com',
            'IT-FOPS' => 'engineer1@demo.com',
            'IT-SEC' => 'gilang.prasetyo@demo.com',
            'IT-INF' => 'eko.saputra@demo.com',
            'OPS-TERM' => 'ade.puspa@demo.com',
            'CORP-GA' => 'supervisor@demo.com',
            'FIN-PROC' => 'opsadmin@demo.com',
        ];

        foreach ($headMap as $departmentCode => $email) {
            $headUserId = User::query()->where('email', $email)->value('id');
            Department::query()->where('code', $departmentCode)->update([
                'head_user_id' => $headUserId,
            ]);
        }
    }

    private function syncServiceManagers(): void
    {
        $managerMap = [
            'SRV-WIFI-CORE' => 'irfan.setiawan@demo.com',
            'SRV-WIFI-AREA' => 'engineer1@demo.com',
            'SRV-CCTV-MON' => 'gilang.prasetyo@demo.com',
            'SRV-GATE-SCAN' => 'gilang.prasetyo@demo.com',
            'SRV-LAN-EDGE' => 'irfan.setiawan@demo.com',
            'SRV-WAN-UPLINK' => 'irfan.setiawan@demo.com',
            'SRV-FW-PERIM' => 'gilang.prasetyo@demo.com',
            'SRV-UPS-POWER' => 'eko.saputra@demo.com',
            'SRV-DC-NET' => 'eko.saputra@demo.com',
            'SRV-IOT-EDGE' => 'engineer2@demo.com',
            'SRV-ACS-CONTROL' => 'gilang.prasetyo@demo.com',
            'SRV-NVR-STORE' => 'gilang.prasetyo@demo.com',
            'SRV-RADIO-BH' => 'irfan.setiawan@demo.com',
            'SRV-SAT-BACKUP' => 'irfan.setiawan@demo.com',
            'SRV-CLOUD-GW' => 'eko.saputra@demo.com',
            'SRV-FIELD-MAINT' => 'engineer1@demo.com',
            'SRV-ASSET-DEPLOY' => 'engineer2@demo.com',
            'SRV-CABLE-MGMT' => 'engineer1@demo.com',
        ];

        foreach ($managerMap as $serviceCode => $email) {
            $managerId = User::query()->where('email', $email)->value('id');
            ServiceCatalog::query()->where('code', $serviceCode)->update([
                'service_manager_user_id' => $managerId,
            ]);
        }
    }
}
