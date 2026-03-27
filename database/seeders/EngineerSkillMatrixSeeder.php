<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\EngineerSkill;
use App\Models\ServiceCatalog;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketSubcategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class EngineerSkillMatrixSeeder extends Seeder
{
    public function run(): void
    {
        $skills = collect([
            ['code' => 'NETWORKING', 'name' => 'Networking'],
            ['code' => 'WIRELESS', 'name' => 'Wireless'],
            ['code' => 'SECURITY', 'name' => 'Security'],
            ['code' => 'CCTV', 'name' => 'CCTV'],
            ['code' => 'FIELD_SUPPORT', 'name' => 'Field Support'],
            ['code' => 'INFRASTRUCTURE', 'name' => 'Infrastructure'],
            ['code' => 'POWER_UPS', 'name' => 'Power & UPS'],
            ['code' => 'ACCESS_CONTROL', 'name' => 'Access Control'],
            ['code' => 'CABLING', 'name' => 'Structured Cabling'],
        ])->mapWithKeys(function (array $skill) {
            $model = EngineerSkill::query()->updateOrCreate(
                ['code' => $skill['code']],
                [
                    'name' => $skill['name'],
                    'description' => 'Default engineer skill mapping for ticket recommendation.',
                    'is_active' => true,
                ]
            );

            return [$skill['code'] => $model];
        });

        $this->mapServices($skills);
        $this->mapTicketSubcategories($skills);
        $this->mapTicketDetailSubcategories($skills);
        $this->mapAssetCategories($skills);
        $this->mapEngineers($skills);
    }

    private function mapServices($skills): void
    {
        $serviceSkillMap = [
            'SRV-WIFI-CORE' => ['NETWORKING', 'WIRELESS'],
            'SRV-WIFI-AREA' => ['NETWORKING', 'WIRELESS', 'FIELD_SUPPORT'],
            'SRV-CCTV-MON' => ['CCTV', 'SECURITY'],
            'SRV-GATE-SCAN' => ['ACCESS_CONTROL', 'FIELD_SUPPORT'],
            'SRV-LAN-EDGE' => ['NETWORKING', 'CABLING'],
            'SRV-WAN-UPLINK' => ['NETWORKING'],
            'SRV-FW-PERIM' => ['SECURITY', 'NETWORKING'],
            'SRV-UPS-POWER' => ['POWER_UPS', 'INFRASTRUCTURE'],
            'SRV-DC-NET' => ['INFRASTRUCTURE', 'NETWORKING'],
            'SRV-IOT-EDGE' => ['FIELD_SUPPORT', 'NETWORKING'],
            'SRV-ACS-CONTROL' => ['ACCESS_CONTROL', 'SECURITY'],
            'SRV-NVR-STORE' => ['CCTV', 'INFRASTRUCTURE'],
            'SRV-RADIO-BH' => ['NETWORKING'],
            'SRV-SAT-BACKUP' => ['NETWORKING'],
            'SRV-CLOUD-GW' => ['INFRASTRUCTURE', 'NETWORKING'],
            'SRV-FIELD-MAINT' => ['FIELD_SUPPORT'],
            'SRV-ASSET-DEPLOY' => ['FIELD_SUPPORT', 'CABLING'],
            'SRV-CABLE-MGMT' => ['CABLING', 'NETWORKING'],
        ];

        foreach ($serviceSkillMap as $serviceCode => $skillCodes) {
            $service = ServiceCatalog::query()->where('code', $serviceCode)->first();
            if ($service === null) {
                continue;
            }

            $service->engineerSkills()->sync($this->resolveSkillIds($skills, $skillCodes));
        }
    }

    private function mapTicketSubcategories($skills): void
    {
        $subcategorySkillMap = [
            'NETWORK_DOWN' => ['NETWORKING'],
            'PERFORMANCE' => ['NETWORKING', 'WIRELESS'],
            'NEW_INSTALL' => ['FIELD_SUPPORT', 'CABLING'],
            'ACCESS_REQUEST' => ['ACCESS_CONTROL', 'SECURITY'],
            'PREVENTIVE' => ['FIELD_SUPPORT'],
            'CORRECTIVE' => ['FIELD_SUPPORT', 'INFRASTRUCTURE'],
        ];

        foreach ($subcategorySkillMap as $subcategoryCode => $skillCodes) {
            $subcategory = TicketSubcategory::query()->where('code', $subcategoryCode)->first();
            if ($subcategory === null) {
                continue;
            }

            $subcategory->engineerSkills()->sync($this->resolveSkillIds($skills, $skillCodes));
        }
    }

    private function mapTicketDetailSubcategories($skills): void
    {
        $detailSkillMap = [
            'LAN_OUTAGE' => ['NETWORKING', 'CABLING'],
            'WAN_OUTAGE' => ['NETWORKING'],
            'WIRELESS_OUTAGE' => ['WIRELESS', 'NETWORKING'],
            'BACKBONE_LINK_DOWN' => ['NETWORKING', 'CABLING'],
            'HIGH_LATENCY' => ['NETWORKING', 'WIRELESS'],
            'PACKET_LOSS' => ['NETWORKING'],
            'INTERMITTENT_ACCESS' => ['WIRELESS', 'NETWORKING'],
            'BANDWIDTH_CONGESTION' => ['NETWORKING'],
            'UNAUTHORIZED_ACCESS' => ['SECURITY', 'ACCESS_CONTROL'],
            'FIREWALL_ANOMALY' => ['SECURITY', 'NETWORKING'],
            'CCTV_BLIND_SPOT' => ['CCTV', 'SECURITY'],
            'HARDWARE_FAILURE' => ['INFRASTRUCTURE', 'FIELD_SUPPORT'],
            'MODULE_FAILURE' => ['INFRASTRUCTURE'],
            'STORAGE_FAILURE' => ['INFRASTRUCTURE', 'CCTV'],
            'ACCOUNT_ACCESS' => ['ACCESS_CONTROL', 'SECURITY'],
            'VPN_ACCESS' => ['NETWORKING', 'SECURITY'],
            'PERMISSION_CHANGE' => ['ACCESS_CONTROL', 'SECURITY'],
            'CARD_READER_REJECT' => ['ACCESS_CONTROL', 'FIELD_SUPPORT'],
            'UPS_ALARM' => ['POWER_UPS', 'INFRASTRUCTURE'],
            'POWER_DROP' => ['POWER_UPS', 'INFRASTRUCTURE'],
            'BATTERY_DEGRADATION' => ['POWER_UPS'],
            'NEW_DEVICE_INSTALL' => ['FIELD_SUPPORT'],
            'NEW_LINK_INSTALL' => ['CABLING', 'NETWORKING'],
            'SITE_ACTIVATION' => ['FIELD_SUPPORT', 'NETWORKING'],
            'NEW_RACK_SETUP' => ['INFRASTRUCTURE', 'CABLING'],
            'DEVICE_RELOCATION' => ['FIELD_SUPPORT'],
            'PORT_ADDITION' => ['CABLING', 'NETWORKING'],
            'TOPOLOGY_CHANGE' => ['NETWORKING'],
            'NEW_JOINER_BUNDLE' => ['ACCESS_CONTROL'],
            'FIELD_USER_ACTIVATION' => ['ACCESS_CONTROL', 'FIELD_SUPPORT'],
            'MONITORING_ENABLEMENT' => ['INFRASTRUCTURE', 'NETWORKING'],
            'SERVICE_GO_LIVE' => ['FIELD_SUPPORT', 'NETWORKING'],
            'HEALTH_CHECK' => ['FIELD_SUPPORT'],
            'CLEANING' => ['FIELD_SUPPORT'],
            'SCHEDULED_TEST' => ['FIELD_SUPPORT', 'NETWORKING'],
            'FIRMWARE_REVIEW' => ['INFRASTRUCTURE', 'SECURITY'],
            'COMPONENT_REPLACEMENT' => ['FIELD_SUPPORT', 'INFRASTRUCTURE'],
            'REPAIR_VISIT' => ['FIELD_SUPPORT'],
            'EMERGENCY_FIX' => ['FIELD_SUPPORT', 'NETWORKING'],
            'RECONFIGURATION' => ['NETWORKING', 'SECURITY'],
            'ABNORMAL_INSPECTION' => ['FIELD_SUPPORT'],
            'SITE_RECTIFICATION' => ['FIELD_SUPPORT', 'CABLING'],
            'BATTERY_REPLACEMENT' => ['POWER_UPS'],
            'CAMERA_REPLACEMENT' => ['CCTV', 'FIELD_SUPPORT'],
            'SFP_REPLACEMENT' => ['NETWORKING', 'CABLING'],
        ];

        foreach ($detailSkillMap as $detailCode => $skillCodes) {
            $detailSubcategory = TicketDetailSubcategory::query()->where('code', $detailCode)->first();
            if ($detailSubcategory === null) {
                continue;
            }

            $detailSubcategory->engineerSkills()->sync($this->resolveSkillIds($skills, $skillCodes));
        }
    }

    private function mapAssetCategories($skills): void
    {
        $assetCategorySkillMap = [
            'CAT-AP' => ['WIRELESS', 'NETWORKING'],
            'CAT-SW' => ['NETWORKING', 'CABLING'],
            'CAT-RT' => ['NETWORKING'],
            'CAT-FW' => ['SECURITY', 'NETWORKING'],
            'CAT-CCTV' => ['CCTV', 'SECURITY'],
            'CAT-GATE' => ['ACCESS_CONTROL', 'FIELD_SUPPORT'],
            'CAT-UPS' => ['POWER_UPS', 'INFRASTRUCTURE'],
            'CAT-SRV' => ['INFRASTRUCTURE'],
            'CAT-STO' => ['INFRASTRUCTURE'],
            'CAT-NVR' => ['CCTV', 'INFRASTRUCTURE'],
            'CAT-IOT' => ['FIELD_SUPPORT', 'NETWORKING'],
            'CAT-ACS' => ['ACCESS_CONTROL', 'SECURITY'],
            'CAT-RADIO' => ['NETWORKING'],
            'CAT-MODEM' => ['NETWORKING'],
            'CAT-RACK' => ['INFRASTRUCTURE'],
            'CAT-CABLE' => ['CABLING', 'NETWORKING'],
        ];

        foreach ($assetCategorySkillMap as $categoryCode => $skillCodes) {
            $assetCategory = AssetCategory::query()->where('code', $categoryCode)->first();
            if ($assetCategory === null) {
                continue;
            }

            $assetCategory->engineerSkills()->sync($this->resolveSkillIds($skills, $skillCodes));
        }
    }

    private function mapEngineers($skills): void
    {
        $profiles = [
            ['NETWORKING', 'WIRELESS', 'CABLING'],
            ['FIELD_SUPPORT', 'ACCESS_CONTROL', 'CABLING'],
            ['SECURITY', 'CCTV', 'ACCESS_CONTROL'],
            ['INFRASTRUCTURE', 'POWER_UPS', 'NETWORKING'],
        ];

        $engineers = User::query()
            ->where('role', 'engineer')
            ->orderBy('name')
            ->get();

        foreach ($engineers as $index => $engineer) {
            $profileSkillCodes = $profiles[$index % count($profiles)];
            $engineer->engineerSkills()->sync($this->resolveSkillIds($skills, $profileSkillCodes));
        }
    }

    private function resolveSkillIds($skills, array $skillCodes): array
    {
        return collect($skillCodes)
            ->map(fn (string $code) => $skills[$code]->id ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
