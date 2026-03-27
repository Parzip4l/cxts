<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\ServiceCatalog;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAssignment;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketStatus;
use App\Models\TicketWorklog;
use App\Models\User;
use App\Services\SLA\SLAResolverService;
use App\Services\Tickets\TicketFlowPolicyResolverService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedInspections();
        $this->seedTickets();
    }

    private function seedInspections(): void
    {
        $inspector = User::query()->where('email', 'inspector@demo.com')->first();
        $opsAdmin = User::query()->where('email', 'opsadmin@demo.com')->first();
        $wifiTemplate = InspectionTemplate::query()->where('code', 'INSP-WIFI-DAILY')->with('items')->first();
        $cctvTemplate = InspectionTemplate::query()->where('code', 'INSP-CCTV-WEEKLY')->with('items')->first();
        $upsTemplate = InspectionTemplate::query()->where('code', 'INSP-UPS-WEEKLY')->with('items')->first();

        $apAsset = Asset::query()->where('code', 'AST-AP-002')->first();
        $cctvAsset = Asset::query()->where('code', 'AST-CCTV-002')->first();
        $upsAsset = Asset::query()->where('code', 'AST-UPS-001')->first();

        $today = CarbonImmutable::now();

        if ($inspector !== null && $wifiTemplate !== null) {
            $inspection = $this->upsertInspection(
                number: 'INSP-DEMO-1001',
                payload: [
                    'inspection_template_id' => $wifiTemplate->id,
                    'asset_id' => $apAsset?->id,
                    'asset_location_id' => $apAsset?->asset_location_id,
                    'inspection_officer_id' => $inspector->id,
                    'scheduled_by_id' => $opsAdmin?->id,
                    'inspection_date' => $today->toDateString(),
                    'status' => Inspection::STATUS_IN_PROGRESS,
                    'schedule_type' => Inspection::SCHEDULE_TYPE_NONE,
                    'started_at' => $today->subMinutes(45),
                    'summary_notes' => 'Inspection task demo untuk AP warehouse yang masih berjalan.',
                    'created_by_id' => $opsAdmin?->id ?? $inspector->id,
                    'updated_by_id' => $inspector->id,
                    'created_at' => $today->subHours(2),
                    'updated_at' => $today->subMinutes(15),
                ]
            );

            $this->syncInspectionItems($inspection, $wifiTemplate, [
                ['status' => 'pass', 'value' => null, 'notes' => 'LED normal'],
                ['status' => 'pass', 'value' => null, 'notes' => 'SSID terlihat'],
                ['status' => 'pass', 'value' => null, 'notes' => 'Test koneksi berhasil'],
                ['status' => 'pass', 'value' => '18', 'notes' => 'Latency stabil'],
                ['status' => null, 'value' => 'Area gudang sedang ramai, perlu re-check sore hari.', 'notes' => null],
            ]);
        }

        if ($inspector !== null && $cctvTemplate !== null) {
            $inspection = $this->upsertInspection(
                number: 'INSP-DEMO-1002',
                payload: [
                    'inspection_template_id' => $cctvTemplate->id,
                    'asset_id' => $cctvAsset?->id,
                    'asset_location_id' => $cctvAsset?->asset_location_id,
                    'inspection_officer_id' => $inspector->id,
                    'scheduled_by_id' => $opsAdmin?->id,
                    'inspection_date' => $today->subDay()->toDateString(),
                    'status' => Inspection::STATUS_SUBMITTED,
                    'final_result' => Inspection::FINAL_RESULT_ABNORMAL,
                    'schedule_type' => Inspection::SCHEDULE_TYPE_WEEKLY,
                    'schedule_interval' => 1,
                    'schedule_weekdays' => [3],
                    'schedule_next_date' => $today->addDays(6)->toDateString(),
                    'started_at' => $today->subDay()->setTime(9, 10),
                    'submitted_at' => $today->subDay()->setTime(9, 50),
                    'summary_notes' => 'Feed kamera perimeter tidak stabil pada malam hari dan rekaman putus-putus.',
                    'created_by_id' => $opsAdmin?->id ?? $inspector->id,
                    'updated_by_id' => $inspector->id,
                    'created_at' => $today->subDays(2),
                    'updated_at' => $today->subDay()->setTime(9, 50),
                ]
            );

            $this->syncInspectionItems($inspection, $cctvTemplate, [
                ['status' => 'fail', 'value' => null, 'notes' => 'Feed blank intermittently'],
                ['status' => 'pass', 'value' => null, 'notes' => 'Housing aman'],
                ['status' => 'fail', 'value' => null, 'notes' => 'IR tidak konsisten'],
                ['status' => 'fail', 'value' => null, 'notes' => 'NVR kehilangan stream 3 kali'],
            ]);
        }

        if ($inspector !== null && $upsTemplate !== null) {
            $inspection = $this->upsertInspection(
                number: 'INSP-DEMO-1003',
                payload: [
                    'inspection_template_id' => $upsTemplate->id,
                    'asset_id' => $upsAsset?->id,
                    'asset_location_id' => $upsAsset?->asset_location_id,
                    'inspection_officer_id' => $inspector->id,
                    'scheduled_by_id' => $opsAdmin?->id,
                    'inspection_date' => $today->subDays(3)->toDateString(),
                    'status' => Inspection::STATUS_SUBMITTED,
                    'final_result' => Inspection::FINAL_RESULT_NORMAL,
                    'schedule_type' => Inspection::SCHEDULE_TYPE_WEEKLY,
                    'schedule_interval' => 1,
                    'schedule_weekdays' => [1],
                    'schedule_next_date' => $today->addDays(4)->toDateString(),
                    'started_at' => $today->subDays(3)->setTime(8, 45),
                    'submitted_at' => $today->subDays(3)->setTime(9, 5),
                    'summary_notes' => 'UPS server room dalam kondisi normal dan runtime masih aman.',
                    'created_by_id' => $opsAdmin?->id ?? $inspector->id,
                    'updated_by_id' => $inspector->id,
                    'created_at' => $today->subDays(4),
                    'updated_at' => $today->subDays(3)->setTime(9, 5),
                ]
            );

            $this->syncInspectionItems($inspection, $upsTemplate, [
                ['status' => 'pass', 'value' => null, 'notes' => 'No active alarm'],
                ['status' => 'pass', 'value' => null, 'notes' => 'Battery health good'],
                ['status' => 'pass', 'value' => '52', 'notes' => 'Normal operational load'],
                ['status' => null, 'value' => 'Runtime estimate 31 minutes at current load.', 'notes' => null],
            ]);
        }
    }

    private function seedTickets(): void
    {
        $flowResolver = app(TicketFlowPolicyResolverService::class);
        $slaResolver = app(SLAResolverService::class);
        $statuses = TicketStatus::query()->pluck('id', 'code');

        $users = User::query()->pluck('id', 'email');
        $requesters = User::query()->get()->keyBy('email');
        $services = ServiceCatalog::query()->pluck('id', 'code');
        $assets = Asset::query()->get()->keyBy('code');
        $locations = AssetLocation::query()->pluck('id', 'code');
        $details = TicketDetailSubcategory::query()->with('category.category')->get()->keyBy('code');
        $priorities = \App\Models\TicketPriority::query()->pluck('id', 'code');
        $inspections = Inspection::query()->pluck('id', 'inspection_number');

        $now = CarbonImmutable::now();

        $scenarios = [
            [
                'number' => 'TCK-DEMO-1001',
                'title' => 'WiFi warehouse Cikarang putus total saat jam bongkar muat',
                'description' => 'Handheld scanner tidak bisa terkoneksi ke SSID operasional sejak pukul 09:10 WIB. Aktivitas inbound mulai terhambat.',
                'detail_code' => 'WIRELESS_OUTAGE',
                'priority_code' => 'P1',
                'requester_email' => 'requester@demo.com',
                'service_code' => 'SRV-WIFI-AREA',
                'asset_code' => 'AST-AP-002',
                'location_code' => 'LOC-CKR-001',
                'source' => 'web',
                'impact' => 'high',
                'urgency' => 'high',
                'status_code' => 'IN_PROGRESS',
                'assigned_engineer_email' => 'irfan.setiawan@demo.com',
                'assigned_team_name' => 'NOC / Wireless',
                'created_at' => $now->subHours(3),
                'started_at' => $now->subHours(2)->subMinutes(30),
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'assignment_ready' => true,
                'updated_by_email' => 'irfan.setiawan@demo.com',
                'activities' => [
                    ['type' => 'ticket_created', 'actor' => 'requester@demo.com', 'old' => null, 'new' => 'NEW', 'metadata' => ['channel' => 'web']],
                    ['type' => 'ticket_assigned', 'actor' => 'supervisor@demo.com', 'old' => 'NEW', 'new' => 'IN_PROGRESS', 'metadata' => ['assigned_team_name' => 'NOC / Wireless']],
                    ['type' => 'work_started', 'actor' => 'irfan.setiawan@demo.com', 'old' => 'ASSIGNED', 'new' => 'IN_PROGRESS', 'metadata' => ['notes' => 'Remote diagnosis dan cek AP controller']]],
                'assignment' => ['assigned_by' => 'supervisor@demo.com', 'assigned_at' => $now->subHours(2)->subMinutes(40), 'notes' => 'Prioritaskan area warehouse dan handheld scanner route.'],
                'worklogs' => [
                    ['user' => 'irfan.setiawan@demo.com', 'type' => 'diagnosis', 'description' => 'Cek AP heartbeat dan uplink switch warehouse.', 'started_at' => $now->subHours(2)->subMinutes(30), 'ended_at' => $now->subHours(2), 'duration' => 30],
                    ['user' => 'irfan.setiawan@demo.com', 'type' => 'progress', 'description' => 'Koordinasi field check untuk kemungkinan gangguan power injector.', 'started_at' => $now->subHours(1)->subMinutes(20), 'ended_at' => $now->subMinutes(55), 'duration' => 25],
                ],
            ],
            [
                'number' => 'TCK-DEMO-1002',
                'title' => 'Permintaan instalasi access point baru di ruang dispatch terminal',
                'description' => 'Tim terminal meminta tambahan access point baru untuk area dispatch karena coverage existing tidak mencukupi.',
                'detail_code' => 'NEW_DEVICE_INSTALL',
                'priority_code' => 'P3',
                'requester_email' => 'requester@demo.com',
                'service_code' => 'SRV-ASSET-DEPLOY',
                'asset_code' => null,
                'location_code' => 'LOC-BKS-001',
                'source' => 'web',
                'impact' => 'medium',
                'urgency' => 'medium',
                'status_code' => 'PENDING_APPROVAL',
                'created_at' => $now->subHours(5),
                'approval_status' => Ticket::APPROVAL_STATUS_PENDING,
                'approval_requested_at' => $now->subHours(5),
                'updated_by_email' => 'requester@demo.com',
                'activities' => [
                    ['type' => 'ticket_created', 'actor' => 'requester@demo.com', 'old' => null, 'new' => 'PENDING_APPROVAL', 'metadata' => ['channel' => 'web']],
                    ['type' => 'approval_requested', 'actor' => 'requester@demo.com', 'old' => 'NEW', 'new' => 'PENDING_APPROVAL', 'metadata' => ['notes' => 'Menunggu persetujuan kepala departemen requester']]],
            ],
            [
                'number' => 'TCK-DEMO-1003',
                'title' => 'Aktivasi monitoring untuk access control site Bandung',
                'description' => 'Perangkat access control site Bandung sudah terpasang dan perlu aktivasi monitoring ke dashboard pusat.',
                'detail_code' => 'MONITORING_ENABLEMENT',
                'priority_code' => 'P3',
                'requester_email' => 'sarah.maharani@demo.com',
                'service_code' => 'SRV-ACS-CONTROL',
                'asset_code' => 'AST-ACS-001',
                'location_code' => 'LOC-BDG-001',
                'source' => 'web',
                'impact' => 'medium',
                'urgency' => 'low',
                'status_code' => 'NEW',
                'created_at' => $now->subDay()->setTime(10, 15),
                'approval_status' => Ticket::APPROVAL_STATUS_APPROVED,
                'approval_requested_at' => $now->subDay()->setTime(10, 20),
                'approved_at' => $now->subDay()->setTime(11, 5),
                'approved_by_email' => 'gilang.prasetyo@demo.com',
                'approval_notes' => 'Aktivasi disetujui setelah perangkat dinyatakan siap operasional.',
                'updated_by_email' => 'gilang.prasetyo@demo.com',
                'activities' => [
                    ['type' => 'ticket_created', 'actor' => 'sarah.maharani@demo.com', 'old' => null, 'new' => 'PENDING_APPROVAL', 'metadata' => ['channel' => 'web']],
                    ['type' => 'approval_requested', 'actor' => 'sarah.maharani@demo.com', 'old' => 'NEW', 'new' => 'PENDING_APPROVAL', 'metadata' => ['notes' => 'Menunggu service manager']],
                    ['type' => 'ticket_approved', 'actor' => 'gilang.prasetyo@demo.com', 'old' => 'PENDING_APPROVAL', 'new' => 'NEW', 'metadata' => ['notes' => 'Boleh diproses setelah resource siap']]],
            ],
            [
                'number' => 'TCK-DEMO-1004',
                'title' => 'Preventive health check rack dan UPS site Bekasi sudah siap dijadwalkan',
                'description' => 'Task preventive mingguan untuk rack dan UPS site Bekasi sudah diverifikasi dan siap di-assign ke field engineer.',
                'detail_code' => 'HEALTH_CHECK',
                'priority_code' => 'P4',
                'requester_email' => 'opsadmin@demo.com',
                'service_code' => 'SRV-FIELD-MAINT',
                'asset_code' => 'AST-UPS-001',
                'location_code' => 'LOC-BKS-001',
                'source' => 'internal',
                'impact' => 'low',
                'urgency' => 'low',
                'status_code' => 'NEW',
                'created_at' => $now->subDays(2)->setTime(14, 0),
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'assignment_ready' => true,
                'assignment_ready_at' => $now->subDays(2)->setTime(14, 30),
                'assignment_ready_by_email' => 'supervisor@demo.com',
                'approval_notes' => 'Checklist preventive sudah diverifikasi, tinggal penugasan.',
                'updated_by_email' => 'supervisor@demo.com',
                'activities' => [
                    ['type' => 'ticket_created', 'actor' => 'opsadmin@demo.com', 'old' => null, 'new' => 'NEW', 'metadata' => ['channel' => 'internal']],
                    ['type' => 'assignment_ready', 'actor' => 'supervisor@demo.com', 'old' => 'NEW', 'new' => 'NEW', 'metadata' => ['notes' => 'Task siap diberikan ke field engineer']]],
            ],
            [
                'number' => 'TCK-DEMO-1005',
                'title' => 'Blind spot CCTV perimeter Medan sudah ditangani dan ditutup',
                'description' => 'Kamera perimeter diganti dan stream NVR sudah stabil. Ticket dipakai untuk menampilkan histori assignment, worklog, dan closure.',
                'detail_code' => 'CCTV_BLIND_SPOT',
                'priority_code' => 'P2',
                'requester_email' => 'opsadmin@demo.com',
                'service_code' => 'SRV-CCTV-MON',
                'asset_code' => 'AST-CCTV-002',
                'location_code' => 'LOC-MDN-001',
                'source' => 'monitoring',
                'impact' => 'high',
                'urgency' => 'medium',
                'status_code' => 'CLOSED',
                'assigned_engineer_email' => 'gilang.prasetyo@demo.com',
                'assigned_team_name' => 'Security Systems',
                'created_at' => $now->subDays(3)->setTime(20, 5),
                'started_at' => $now->subDays(3)->setTime(21, 0),
                'responded_at' => $now->subDays(3)->setTime(21, 0),
                'resolved_at' => $now->subDays(2)->setTime(10, 40),
                'completed_at' => $now->subDays(2)->setTime(10, 40),
                'closed_at' => $now->subDays(2)->setTime(13, 15),
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'assignment_ready' => true,
                'sla_status' => Ticket::SLA_STATUS_BREACHED,
                'updated_by_email' => 'supervisor@demo.com',
                'activities' => [
                    ['type' => 'ticket_created', 'actor' => 'opsadmin@demo.com', 'old' => null, 'new' => 'NEW', 'metadata' => ['channel' => 'monitoring']],
                    ['type' => 'ticket_assigned', 'actor' => 'supervisor@demo.com', 'old' => 'NEW', 'new' => 'ASSIGNED', 'metadata' => ['assigned_team_name' => 'Security Systems']],
                    ['type' => 'work_started', 'actor' => 'gilang.prasetyo@demo.com', 'old' => 'ASSIGNED', 'new' => 'IN_PROGRESS', 'metadata' => ['notes' => 'Cek kamera, PSU, dan NVR stream']],
                    ['type' => 'work_completed', 'actor' => 'gilang.prasetyo@demo.com', 'old' => 'IN_PROGRESS', 'new' => 'COMPLETED', 'metadata' => ['notes' => 'Kamera berhasil diganti dan stream kembali normal']],
                    ['type' => 'ticket_closed', 'actor' => 'supervisor@demo.com', 'old' => 'COMPLETED', 'new' => 'CLOSED', 'metadata' => ['notes' => 'User menerima hasil penanganan']]],
                'assignment' => ['assigned_by' => 'supervisor@demo.com', 'assigned_at' => $now->subDays(3)->setTime(20, 20), 'notes' => 'Ambil spare camera dan pastikan rekaman normal.'],
                'worklogs' => [
                    ['user' => 'gilang.prasetyo@demo.com', 'type' => 'onsite', 'description' => 'Pengecekan kamera perimeter dan koneksi ke NVR.', 'started_at' => $now->subDays(3)->setTime(21, 0), 'ended_at' => $now->subDays(3)->setTime(22, 0), 'duration' => 60],
                    ['user' => 'gilang.prasetyo@demo.com', 'type' => 'replacement', 'description' => 'Penggantian kamera dan verifikasi hasil rekaman.', 'started_at' => $now->subDays(2)->setTime(9, 30), 'ended_at' => $now->subDays(2)->setTime(10, 40), 'duration' => 70],
                ],
            ],
            [
                'number' => 'TCK-DEMO-1006',
                'title' => 'Permintaan akses VPN vendor proyek ditolak',
                'description' => 'Permintaan akses VPN sementara untuk vendor proyek ditolak karena dokumen persetujuan belum lengkap.',
                'detail_code' => 'VPN_ACCESS',
                'priority_code' => 'P3',
                'requester_email' => 'dini.febrianti@demo.com',
                'service_code' => 'SRV-CLOUD-GW',
                'asset_code' => null,
                'location_code' => 'LOC-JKT-001',
                'source' => 'web',
                'impact' => 'medium',
                'urgency' => 'medium',
                'status_code' => 'REJECTED',
                'created_at' => $now->subDay()->setTime(8, 30),
                'approval_status' => Ticket::APPROVAL_STATUS_REJECTED,
                'approval_requested_at' => $now->subDay()->setTime(8, 45),
                'rejected_at' => $now->subDay()->setTime(9, 20),
                'rejected_by_email' => 'opsadmin@demo.com',
                'approval_notes' => 'Ditolak karena surat permintaan akses dari vendor belum dilampirkan.',
                'updated_by_email' => 'opsadmin@demo.com',
                'activities' => [
                    ['type' => 'ticket_created', 'actor' => 'dini.febrianti@demo.com', 'old' => null, 'new' => 'PENDING_APPROVAL', 'metadata' => ['channel' => 'web']],
                    ['type' => 'approval_requested', 'actor' => 'dini.febrianti@demo.com', 'old' => 'NEW', 'new' => 'PENDING_APPROVAL', 'metadata' => ['notes' => 'Menunggu approval akses VPN']],
                    ['type' => 'ticket_rejected', 'actor' => 'opsadmin@demo.com', 'old' => 'PENDING_APPROVAL', 'new' => 'REJECTED', 'metadata' => ['notes' => 'Dokumen belum lengkap']]],
            ],
            [
                'number' => 'TCK-DEMO-1007',
                'title' => 'Follow up abnormal inspection CCTV perimeter Medan',
                'description' => 'Ticket follow up dari hasil inspection abnormal untuk kamera perimeter Medan yang mengalami blank feed dan IR tidak stabil.',
                'detail_code' => 'ABNORMAL_INSPECTION',
                'priority_code' => 'P2',
                'requester_email' => 'inspector@demo.com',
                'service_code' => 'SRV-CCTV-MON',
                'asset_code' => 'AST-CCTV-002',
                'location_code' => 'LOC-MDN-001',
                'inspection_number' => 'INSP-DEMO-1002',
                'source' => 'inspection',
                'impact' => 'high',
                'urgency' => 'medium',
                'status_code' => 'ASSIGNED',
                'assigned_engineer_email' => 'engineer2@demo.com',
                'assigned_team_name' => 'Field Operations',
                'created_at' => $now->subHours(20),
                'approval_status' => Ticket::APPROVAL_STATUS_NOT_REQUIRED,
                'assignment_ready' => true,
                'updated_by_email' => 'supervisor@demo.com',
                'activities' => [
                    ['type' => 'ticket_created_from_inspection', 'actor' => 'inspector@demo.com', 'old' => null, 'new' => 'NEW', 'metadata' => ['inspection_number' => 'INSP-DEMO-1002']],
                    ['type' => 'ticket_assigned', 'actor' => 'supervisor@demo.com', 'old' => 'NEW', 'new' => 'ASSIGNED', 'metadata' => ['assigned_team_name' => 'Field Operations']]],
                'assignment' => ['assigned_by' => 'supervisor@demo.com', 'assigned_at' => $now->subHours(19), 'notes' => 'Jadwalkan kunjungan site Medan di shift berikutnya.'],
            ],
        ];

        foreach ($scenarios as $scenario) {
            $detail = $details->get($scenario['detail_code']);
            if ($detail === null) {
                continue;
            }

            $requester = $requesters->get($scenario['requester_email']);
            if ($requester === null) {
                continue;
            }

            $ticket = Ticket::query()->firstOrNew(['ticket_number' => $scenario['number']]);
            $createdAt = $scenario['created_at'];
            $flow = $flowResolver->resolve([
                'ticket_category_id' => $detail->category?->category?->id,
                'ticket_subcategory_id' => $detail->category?->id,
                'ticket_detail_subcategory_id' => $detail->id,
                'requester_department_id' => $requester->department_id,
                'service_id' => $services[$scenario['service_code']] ?? null,
            ]);

            $sla = $slaResolver->resolveSLA([
                'ticket_category_id' => $detail->category?->category?->id,
                'ticket_subcategory_id' => $detail->category?->id,
                'ticket_detail_subcategory_id' => $detail->id,
                'service_id' => $services[$scenario['service_code']] ?? null,
                'priority_id' => $priorities[$scenario['priority_code']] ?? null,
                'impact' => $scenario['impact'],
                'urgency' => $scenario['urgency'],
            ]);

            $ticket->fill([
                'title' => $scenario['title'],
                'description' => $scenario['description'],
                'requester_id' => $requester->id,
                'requester_department_id' => $requester->department_id,
                'ticket_category_id' => $detail->category?->category?->id,
                'ticket_subcategory_id' => $detail->category?->id,
                'ticket_detail_subcategory_id' => $detail->id,
                'ticket_priority_id' => $priorities[$scenario['priority_code']] ?? null,
                'service_id' => $services[$scenario['service_code']] ?? null,
                'asset_id' => $scenario['asset_code'] ? ($assets[$scenario['asset_code']]->id ?? null) : null,
                'asset_location_id' => $locations[$scenario['location_code']] ?? ($scenario['asset_code'] ? ($assets[$scenario['asset_code']]->asset_location_id ?? null) : null),
                'inspection_id' => $scenario['inspection_number'] ?? null ? ($inspections[$scenario['inspection_number']] ?? null) : null,
                'ticket_status_id' => $statuses[$scenario['status_code']] ?? null,
                'assigned_team_name' => $scenario['assigned_team_name'] ?? null,
                'assigned_engineer_id' => isset($scenario['assigned_engineer_email']) ? ($users[$scenario['assigned_engineer_email']] ?? null) : null,
                'requires_approval' => $flow['requires_approval'],
                'allow_direct_assignment' => $flow['allow_direct_assignment'],
                'approval_status' => $scenario['approval_status'],
                'approval_requested_at' => $scenario['approval_requested_at'] ?? ($flow['requires_approval'] ? $createdAt : null),
                'expected_approver_id' => $flow['approver_user_id'],
                'expected_approver_name_snapshot' => $flow['approver_name'],
                'expected_approver_strategy' => $flow['approver_strategy'],
                'expected_approver_role_code' => $flow['approver_role_code'],
                'approved_at' => $scenario['approved_at'] ?? null,
                'approved_by_id' => isset($scenario['approved_by_email']) ? ($users[$scenario['approved_by_email']] ?? null) : null,
                'rejected_at' => $scenario['rejected_at'] ?? null,
                'rejected_by_id' => isset($scenario['rejected_by_email']) ? ($users[$scenario['rejected_by_email']] ?? null) : null,
                'approval_notes' => $scenario['approval_notes'] ?? null,
                'assignment_ready_at' => ($scenario['assignment_ready'] ?? false) ? ($scenario['assignment_ready_at'] ?? $createdAt) : null,
                'assignment_ready_by_id' => isset($scenario['assignment_ready_by_email']) ? ($users[$scenario['assignment_ready_by_email']] ?? null) : null,
                'flow_policy_source' => $flow['source'],
                'sla_policy_id' => $sla->policyId,
                'sla_policy_name' => $sla->name,
                'sla_name_snapshot' => $sla->name,
                'response_due_at' => $sla->responseDueAt($createdAt),
                'responded_at' => $scenario['responded_at'] ?? ($scenario['started_at'] ?? null),
                'breached_response_at' => $scenario['breached_response_at'] ?? null,
                'resolution_due_at' => $sla->resolutionDueAt($createdAt),
                'source' => $scenario['source'],
                'impact' => $scenario['impact'],
                'urgency' => $scenario['urgency'],
                'started_at' => $scenario['started_at'] ?? null,
                'paused_at' => $scenario['paused_at'] ?? null,
                'resolved_at' => $scenario['resolved_at'] ?? null,
                'sla_status' => $scenario['sla_status'] ?? ($sla->hasTargets() ? Ticket::SLA_STATUS_ON_TIME : null),
                'breached_resolution_at' => $scenario['breached_resolution_at'] ?? null,
                'completed_at' => $scenario['completed_at'] ?? null,
                'closed_at' => $scenario['closed_at'] ?? null,
                'last_status_changed_at' => $scenario['closed_at'] ?? $scenario['completed_at'] ?? $scenario['started_at'] ?? $createdAt,
                'created_by_id' => $requester->id,
                'updated_by_id' => isset($scenario['updated_by_email']) ? ($users[$scenario['updated_by_email']] ?? $requester->id) : $requester->id,
            ]);

            $ticket->created_at = $createdAt;
            $ticket->updated_at = $scenario['closed_at'] ?? $scenario['completed_at'] ?? $scenario['started_at'] ?? $createdAt;
            $ticket->save();

            foreach ($scenario['activities'] ?? [] as $activity) {
                TicketActivity::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'activity_type' => $activity['type'],
                        'actor_user_id' => $users[$activity['actor']] ?? null,
                    ],
                    [
                        'old_status_id' => $activity['old'] ? ($statuses[$activity['old']] ?? null) : null,
                        'new_status_id' => $activity['new'] ? ($statuses[$activity['new']] ?? null) : null,
                        'metadata' => $activity['metadata'] ?? [],
                    ]
                );
            }

            if (isset($scenario['assignment'])) {
                TicketAssignment::query()->updateOrCreate(
                    ['ticket_id' => $ticket->id],
                    [
                        'previous_engineer_id' => null,
                        'assigned_engineer_id' => $ticket->assigned_engineer_id,
                        'assigned_by_id' => $users[$scenario['assignment']['assigned_by']] ?? null,
                        'assigned_at' => $scenario['assignment']['assigned_at'],
                        'notes' => $scenario['assignment']['notes'],
                    ]
                );
            }

            foreach ($scenario['worklogs'] ?? [] as $worklog) {
                TicketWorklog::query()->updateOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'user_id' => $users[$worklog['user']] ?? null,
                        'description' => $worklog['description'],
                    ],
                    [
                        'log_type' => $worklog['type'],
                        'started_at' => $worklog['started_at'],
                        'ended_at' => $worklog['ended_at'],
                        'duration_minutes' => $worklog['duration'],
                    ]
                );
            }
        }
    }

    private function upsertInspection(string $number, array $payload): Inspection
    {
        $inspection = Inspection::query()->firstOrNew(['inspection_number' => $number]);
        $inspection->fill(collect($payload)->except(['created_at', 'updated_at'])->all());
        $inspection->created_at = $payload['created_at'] ?? now();
        $inspection->updated_at = $payload['updated_at'] ?? $inspection->created_at;
        $inspection->save();

        return $inspection;
    }

    private function syncInspectionItems(Inspection $inspection, InspectionTemplate $template, array $results): void
    {
        $existing = $inspection->items()->get()->keyBy('inspection_template_item_id');

        foreach ($template->items as $index => $templateItem) {
            $result = $results[$index] ?? [];
            $item = $existing->get($templateItem->id) ?? $inspection->items()->make([
                'inspection_template_item_id' => $templateItem->id,
            ]);

            $item->fill([
                'sequence' => $templateItem->sequence,
                'item_label' => $templateItem->item_label,
                'item_type' => $templateItem->item_type,
                'expected_value' => $templateItem->expected_value,
                'result_status' => $result['status'] ?? null,
                'result_value' => $result['value'] ?? null,
                'notes' => $result['notes'] ?? null,
            ]);

            $inspection->items()->save($item);
        }
    }
}
