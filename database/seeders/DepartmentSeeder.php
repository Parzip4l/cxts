<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'code' => 'IT-ENG',
                'name' => 'IT & Digital Operations',
                'description' => 'Central technology operations, service governance, and enterprise platform support.',
                'parent_code' => null,
            ],
            [
                'code' => 'OPS-HD',
                'name' => 'Service Desk & Monitoring',
                'description' => 'Ticket intake, first-level triage, monitoring, and user communication.',
                'parent_code' => 'IT-ENG',
            ],
            [
                'code' => 'IT-NOC',
                'name' => 'Network Operations Center',
                'description' => 'Core LAN, WAN, firewall, wireless backbone, and connectivity monitoring.',
                'parent_code' => 'IT-ENG',
            ],
            [
                'code' => 'IT-FOPS',
                'name' => 'Field Operations Engineering',
                'description' => 'On-site deployment, corrective action, preventive visits, and asset handling.',
                'parent_code' => 'IT-ENG',
            ],
            [
                'code' => 'IT-SEC',
                'name' => 'Security & Surveillance Systems',
                'description' => 'CCTV, access control, gate automation, and perimeter security technology.',
                'parent_code' => 'IT-ENG',
            ],
            [
                'code' => 'IT-INF',
                'name' => 'Infrastructure & Data Center',
                'description' => 'Server room, UPS, rack, storage, edge compute, and core infrastructure services.',
                'parent_code' => 'IT-ENG',
            ],
            [
                'code' => 'OPS-TERM',
                'name' => 'Terminal Operations',
                'description' => 'Operational site teams that consume critical field and monitoring services.',
                'parent_code' => null,
            ],
            [
                'code' => 'OPS-AREA',
                'name' => 'Regional Site Operations',
                'description' => 'Regional and remote site operators across depots, gates, and satellite facilities.',
                'parent_code' => null,
            ],
            [
                'code' => 'CORP-GA',
                'name' => 'Corporate Services & GA',
                'description' => 'General affairs, office support, and non-operational corporate service consumers.',
                'parent_code' => null,
            ],
            [
                'code' => 'FIN-PROC',
                'name' => 'Finance & Procurement',
                'description' => 'Procurement approvals, vendor coordination, and commercial administration.',
                'parent_code' => null,
            ],
        ];

        $departmentsByCode = collect();

        foreach ($definitions as $definition) {
            $department = Department::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'parent_department_id' => null,
                    'is_active' => true,
                ]
            );

            $departmentsByCode->put($definition['code'], $department);
        }

        foreach ($definitions as $definition) {
            if ($definition['parent_code'] === null) {
                continue;
            }

            $department = $departmentsByCode->get($definition['code']);
            $parent = $departmentsByCode->get($definition['parent_code']);

            if ($department !== null && $parent !== null) {
                $department->update([
                    'parent_department_id' => $parent->id,
                ]);
            }
        }
    }
}
