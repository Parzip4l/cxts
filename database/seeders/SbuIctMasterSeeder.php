<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\SlaPolicy;
use App\Models\SlaPolicyAssignment;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SbuIctMasterSeeder extends Seeder
{
    private const SOURCE_FILE = 'data/List_Perangkat_SBU_ICT.xlsx';

    private const SHEET_REFERENCE = 'Referensi';
    private const SHEET_ASSETS = 'Master Perangkat';
    private const SHEET_LOCATIONS = 'Mapping Lokasi';
    private const SHEET_SLA = 'SLA Layanan';
    private const SHEET_PERSONNEL = 'Personel';

    /**
     * Seed SBU ICT master data from the bundled Excel workbook.
     */
    public function run(): void
    {
        $workbook = $this->readWorkbook(database_path('seeders/' . self::SOURCE_FILE));

        $this->resetRelatedTables();
        $this->seedDepartments($workbook);
        $this->seedAssetStatuses($workbook);
        $this->seedAssetCategories($workbook);
        $this->seedAssetLocations($workbook);
        $this->seedServices($workbook);
        $this->seedUsers($workbook);
        $this->seedAssets($workbook);
        $this->seedSlaPolicies($workbook);
        $this->syncDepartmentHeadsAndServiceManagers();

        $this->command?->info('SBU ICT master data seeded from ' . self::SOURCE_FILE . '.');
    }

    private function resetRelatedTables(): void
    {
        DB::transaction(function (): void {
            $this->deleteTables([
                'audit_logs',
                'api_tokens',
                'user_push_tokens',
                'ticket_attachments',
                'ticket_activities',
                'ticket_worklogs',
                'ticket_assignments',
                'tickets',
                'inspection_evidences',
                'inspection_items',
                'inspections',
                'engineer_schedules',
                'engineer_skill_user',
                'engineer_skill_service',
                'asset_category_engineer_skill',
                'sla_policy_assignments',
                'sla_policies',
                'assets',
                'asset_locations',
                'services',
                'asset_statuses',
                'asset_categories',
            ]);

            if (Schema::hasTable('departments')) {
                Department::query()->update(['head_user_id' => null]);
            }

            if (Schema::hasTable('users')) {
                User::query()->delete();
            }

            if (Schema::hasTable('departments')) {
                Department::query()->delete();
            }
        });
    }

    private function deleteTables(array $tables): void
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    private function seedDepartments(array $workbook): void
    {
        $definitions = [
            ['code' => 'SBU-ICT', 'name' => 'SBU ICT', 'parent_code' => null, 'description' => 'Root organization for ICT services imported from the SBU ICT device workbook.'],
        ];

        foreach ($this->referenceValues($workbook, 6) as $unit) {
            $definitions[] = [
                'code' => $this->departmentCode($unit),
                'name' => $unit,
                'parent_code' => 'SBU-ICT',
                'description' => 'Unit bisnis dari sheet Referensi.',
            ];
        }

        foreach ($this->columnValues($workbook[self::SHEET_ASSETS] ?? [], 5, true) as $unit) {
            $definitions[] = [
                'code' => $this->departmentCode($unit),
                'name' => $unit,
                'parent_code' => 'SBU-ICT',
                'description' => 'Unit bisnis pemilik perangkat dari sheet Master Perangkat.',
            ];
        }

        foreach ($this->columnValues($workbook[self::SHEET_PERSONNEL] ?? [], 2, true) as $unit) {
            $definitions[] = [
                'code' => $this->departmentCode($unit),
                'name' => $unit,
                'parent_code' => $this->guessParentDepartmentCode($unit),
                'description' => 'Unit personel dari sheet Personel.',
            ];
        }

        $departmentsByCode = collect();

        foreach (collect($definitions)->unique('code')->values() as $definition) {
            $department = Department::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'parent_department_id' => null,
                    'head_user_id' => null,
                    'is_active' => true,
                ]
            );

            $departmentsByCode->put($definition['code'], $department);
        }

        foreach (collect($definitions)->unique('code')->values() as $definition) {
            if ($definition['parent_code'] === null) {
                continue;
            }

            $department = $departmentsByCode->get($definition['code']);
            $parent = $departmentsByCode->get($definition['parent_code']);

            if ($department !== null && $parent !== null) {
                $department->update(['parent_department_id' => $parent->id]);
            }
        }
    }

    private function seedAssetStatuses(array $workbook): void
    {
        $statuses = [
            ['code' => 'ACTIVE', 'name' => 'Aktif', 'description' => 'Perangkat aktif dan digunakan.', 'is_operational' => true],
            ['code' => 'INACTIVE', 'name' => 'Tidak Aktif', 'description' => 'Perangkat tidak aktif berdasarkan sumber data.', 'is_operational' => false],
            ['code' => 'STANDBY', 'name' => 'Backup', 'description' => 'Perangkat cadangan atau standby.', 'is_operational' => true],
            ['code' => 'FAULTY', 'name' => 'Rusak', 'description' => 'Perangkat berkondisi rusak berdasarkan sumber data.', 'is_operational' => false],
        ];

        foreach ($this->referenceValues($workbook, 3) as $status) {
            $code = $this->assetStatusCode($status, null);
            $statuses[] = [
                'code' => $code,
                'name' => $status,
                'description' => 'Status perangkat dari sheet Referensi.',
                'is_operational' => $code !== 'INACTIVE',
            ];
        }

        foreach ($this->referenceValues($workbook, 2) as $condition) {
            if ($this->assetStatusCode(null, $condition) !== 'FAULTY') {
                continue;
            }

            $statuses[] = [
                'code' => 'FAULTY',
                'name' => 'Rusak',
                'description' => 'Kondisi perangkat rusak dari sheet Referensi.',
                'is_operational' => false,
            ];
        }

        foreach (collect($statuses)->unique('code')->values() as $status) {
            AssetStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'description' => $status['description'],
                    'is_operational' => $status['is_operational'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAssetCategories(array $workbook): void
    {
        $categoryNames = collect($this->referenceValues($workbook, 1));

        foreach ($this->dataRows($workbook[self::SHEET_ASSETS] ?? []) as $row) {
            $categoryNames->push($this->inferCategoryName(
                $this->cell($row, 6),
                $this->cell($row, 2),
                $this->cell($row, 8)
            ));
        }

        foreach ($categoryNames->filter()->unique()->values() as $categoryName) {
            AssetCategory::query()->updateOrCreate(
                ['code' => $this->assetCategoryCode($categoryName)],
                [
                    'name' => $categoryName,
                    'description' => 'Kategori perangkat dari workbook SBU ICT.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAssetLocations(array $workbook): void
    {
        $departments = Department::query()->pluck('id', 'code');
        $locations = [];

        foreach ($this->referenceValues($workbook, 5) as $site) {
            $locations[$site] = [
                'name' => $site,
                'address' => $site,
                'department_id' => $departments['SBU-ICT'] ?? null,
                'description' => 'Lokasi dari sheet Referensi.',
            ];
        }

        foreach ($this->dataRows($workbook[self::SHEET_LOCATIONS] ?? []) as $row) {
            $label = $this->locationLabel($row);
            if ($label === '') {
                continue;
            }

            $locations[$label] = [
                'name' => $this->limitText($label, 150),
                'address' => $this->locationAddress($row),
                'department_id' => $departments['SBU-ICT'] ?? null,
                'description' => $this->locationDescription($row),
            ];
        }

        foreach ($locations as $label => $location) {
            AssetLocation::query()->updateOrCreate(
                ['code' => $this->locationCode($label)],
                [
                    'name' => $location['name'],
                    'address' => $location['address'],
                    'latitude' => null,
                    'longitude' => null,
                    'department_id' => $location['department_id'],
                    'description' => $location['description'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedServices(array $workbook): void
    {
        $departments = Department::query()->pluck('id', 'code');
        $services = [];

        foreach ($this->referenceValues($workbook, 0) as $serviceName) {
            $services[$serviceName] = [
                'name' => $serviceName,
                'unit' => null,
                'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL,
            ];
        }

        foreach ($this->dataRows($workbook[self::SHEET_ASSETS] ?? []) as $row) {
            $serviceName = $this->normalize($this->cell($row, 4));
            if ($serviceName === '') {
                continue;
            }

            $ownership = $this->normalize($this->cell($row, 13));
            $services[$serviceName] = [
                'name' => $serviceName,
                'unit' => $this->normalize($this->cell($row, 5)),
                'ownership_model' => $this->ownershipModel($ownership),
            ];
        }

        foreach ($this->dataRows($workbook[self::SHEET_SLA] ?? []) as $row) {
            $serviceName = $this->normalize($this->cell($row, 1));
            if ($serviceName === '') {
                continue;
            }

            $services[$serviceName] ??= [
                'name' => $serviceName,
                'unit' => null,
                'ownership_model' => ServiceCatalog::OWNERSHIP_INTERNAL,
            ];
        }

        foreach ($services as $service) {
            $departmentCode = $service['unit'] !== null && $service['unit'] !== ''
                ? $this->departmentCode($service['unit'])
                : 'SBU-ICT';

            ServiceCatalog::query()->updateOrCreate(
                ['code' => $this->serviceCode($service['name'])],
                [
                    'name' => $service['name'],
                    'service_category' => $service['unit'] ?: 'SBU ICT',
                    'description' => 'Layanan dari workbook SBU ICT.',
                    'ownership_model' => $service['ownership_model'],
                    'department_owner_id' => $departments[$departmentCode] ?? $departments['SBU-ICT'] ?? null,
                    'vendor_id' => null,
                    'service_manager_user_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedUsers(array $workbook): void
    {
        $departments = Department::query()->pluck('id', 'code');
        $password = Hash::make('password');

        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@demo.com', 'role' => 'super_admin', 'department_code' => 'SBU-ICT'],
            ['name' => 'Operational Admin', 'email' => 'opsadmin@demo.com', 'role' => 'operational_admin', 'department_code' => 'SBU-ICT'],
            ['name' => 'Supervisor SBU ICT', 'email' => 'supervisor@demo.com', 'role' => 'supervisor', 'department_code' => 'SBU-ICT'],
            ['name' => 'Inspection Officer SBU ICT', 'email' => 'inspector@demo.com', 'role' => 'inspection_officer', 'department_code' => 'SBU-ICT'],
            ['name' => 'Requester SBU ICT', 'email' => 'requester@demo.com', 'role' => 'requester', 'department_code' => 'SBU-ICT'],
        ];

        foreach ($this->dataRows($workbook[self::SHEET_PERSONNEL] ?? []) as $row) {
            $name = $this->normalize($this->cell($row, 1));
            if ($name === '') {
                continue;
            }

            $unit = $this->normalize($this->cell($row, 2));
            $users[] = [
                'name' => $name,
                'email' => $this->emailForName($name),
                'role' => 'engineer',
                'department_code' => $this->departmentCode($unit),
            ];
        }

        $seenEmails = [];
        foreach ($users as $user) {
            $email = $this->uniqueEmail($user['email'], $seenEmails);
            $seenEmails[$email] = true;

            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $user['name'],
                    'phone_number' => null,
                    'profile_photo_path' => null,
                    'email_verified_at' => now(),
                    'password' => $password,
                    'role' => $user['role'],
                    'department_id' => $departments[$user['department_code']] ?? $departments['SBU-ICT'] ?? null,
                    'remember_token' => Str::random(10),
                ]
            );
        }
    }

    private function seedAssets(array $workbook): void
    {
        $categories = AssetCategory::query()->pluck('id', 'code');
        $statuses = AssetStatus::query()->pluck('id', 'code');
        $locations = AssetLocation::query()->pluck('id', 'code');
        $departments = Department::query()->pluck('id', 'code');
        $services = ServiceCatalog::query()->pluck('id', 'code');
        $mappingRows = $this->mappingRowsByNumber($workbook[self::SHEET_LOCATIONS] ?? []);
        $usedCodes = [];

        foreach ($this->dataRows($workbook[self::SHEET_ASSETS] ?? []) as $row) {
            $sequence = $this->normalize($this->cell($row, 0));
            $sourceCode = $this->normalize($this->cell($row, 1));
            $name = $this->normalize($this->cell($row, 2))
                ?: $this->normalize($this->cell($row, 8))
                ?: $sourceCode
                ?: 'Perangkat ' . $sequence;

            if ($sequence === '') {
                continue;
            }

            $assetCode = $this->assetCode($sourceCode, $sequence, $usedCodes);
            $usedCodes[$assetCode] = true;

            $categoryName = $this->inferCategoryName(
                $this->cell($row, 6),
                $name,
                $this->cell($row, 8)
            );
            $serviceName = $this->normalize($this->cell($row, 4));
            $unitName = $this->normalize($this->cell($row, 5));
            $statusCode = $this->assetStatusCode($this->cell($row, 12), $this->cell($row, 11));
            $mappingRow = $mappingRows[$sequence] ?? [];
            $locationCode = $this->locationCode($this->locationLabel($mappingRow));

            Asset::query()->updateOrCreate(
                ['code' => $assetCode],
                [
                    'name' => $this->limitText($name, 150),
                    'asset_category_id' => $categories[$this->assetCategoryCode($categoryName)] ?? null,
                    'service_id' => $services[$this->serviceCode($serviceName)] ?? null,
                    'department_owner_id' => $departments[$this->departmentCode($unitName)] ?? $departments['SBU-ICT'] ?? null,
                    'vendor_id' => null,
                    'asset_location_id' => $locations[$locationCode] ?? null,
                    'serial_number' => $this->nullable($this->cell($row, 9)),
                    'brand' => $this->nullable($this->cell($row, 7)),
                    'model' => $this->nullable($this->cell($row, 8)),
                    'install_date' => $this->installDate($this->cell($row, 10)),
                    'warranty_end_date' => null,
                    'criticality' => $this->criticality($serviceName, $categoryName),
                    'asset_status_id' => $statuses[$statusCode] ?? null,
                    'notes' => $this->assetNotes($row, $mappingRow, $sourceCode, $assetCode),
                    'is_active' => $statusCode !== 'INACTIVE',
                ]
            );
        }
    }

    private function seedSlaPolicies(array $workbook): void
    {
        $services = ServiceCatalog::query()->pluck('id', 'code');
        $priorities = TicketPriority::query()->pluck('id', 'code');
        $incidentCategoryId = TicketCategory::query()->where('code', 'INCIDENT')->value('id');
        $currentService = null;
        $currentAvailability = null;
        $sortOrder = 10;

        foreach ($this->dataRows($workbook[self::SHEET_SLA] ?? []) as $row) {
            $service = $this->normalize($this->cell($row, 1));
            if ($service !== '') {
                $currentService = $service;
                $currentAvailability = $this->normalize($this->cell($row, 2));
            }

            $incidentLevel = $this->normalize($this->cell($row, 3));
            if ($currentService === null || $incidentLevel === '') {
                continue;
            }

            $priorityCode = $this->priorityCode($incidentLevel);
            $responseMinutes = $this->durationToMinutes($this->cell($row, 5));
            $resolutionMinutes = $this->durationToMinutes($this->cell($row, 7));
            $policyName = $this->slaPolicyName($currentService, $priorityCode);

            $policy = SlaPolicy::query()->updateOrCreate(
                ['name' => $policyName],
                [
                    'description' => $this->slaDescription($currentService, $currentAvailability, $row),
                    'response_time_minutes' => $responseMinutes,
                    'resolution_time_minutes' => $resolutionMinutes,
                    'working_hours_id' => null,
                    'is_active' => true,
                ]
            );

            SlaPolicyAssignment::query()->updateOrCreate(
                [
                    'sla_policy_id' => $policy->id,
                    'ticket_type' => 'incident',
                    'category_id' => $incidentCategoryId,
                    'subcategory_id' => null,
                    'detail_subcategory_id' => null,
                    'service_item_id' => $services[$this->serviceCode($currentService)] ?? null,
                    'priority_id' => $priorities[$priorityCode] ?? null,
                    'impact' => $this->impactForPriority($priorityCode),
                    'urgency' => $this->impactForPriority($priorityCode),
                ],
                [
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );
        }
    }

    private function syncDepartmentHeadsAndServiceManagers(): void
    {
        $supervisorId = User::query()->where('email', 'supervisor@demo.com')->value('id');
        Department::query()->where('code', 'SBU-ICT')->update(['head_user_id' => $supervisorId]);

        $usersByDepartment = User::query()
            ->whereIn('role', ['engineer', 'supervisor', 'operational_admin'])
            ->orderByRaw("case when role = 'supervisor' then 0 when role = 'operational_admin' then 1 else 2 end")
            ->orderBy('name')
            ->get()
            ->groupBy('department_id');

        foreach (Department::query()->where('code', '!=', 'SBU-ICT')->get(['id']) as $department) {
            $headId = $usersByDepartment->get($department->id)?->first()?->id;
            if ($headId !== null) {
                $department->update(['head_user_id' => $headId]);
            }
        }

        $defaultManagerId = $supervisorId ?: User::query()->where('role', 'operational_admin')->value('id');
        ServiceCatalog::query()->update(['service_manager_user_id' => $defaultManagerId]);
    }

    private function readWorkbook(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Workbook not found: {$path}");
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open workbook: {$path}");
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $relationships = $this->readWorkbookRelationships($zip);
        $workbook = $this->xml($zip, 'xl/workbook.xml');
        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $sheets = $workbook->xpath('//main:sheets/main:sheet') ?: [];
        $data = [];

        foreach ($sheets as $sheet) {
            $name = (string) $sheet['name'];
            $relationAttributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $relationId = (string) $relationAttributes['id'];
            $target = $relationships[$relationId] ?? null;

            if ($target === null) {
                continue;
            }

            $sheetPath = str_starts_with($target, 'xl/') ? $target : 'xl/' . ltrim($target, '/');
            $data[$name] = $this->readSheet($zip, $sheetPath, $sharedStrings);
        }

        $zip->close();

        return $data;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }

        $xml = $this->xml($zip, 'xl/sharedStrings.xml');
        $xml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $strings = [];

        foreach ($xml->xpath('//main:si') ?: [] as $item) {
            $item->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $parts = [];
            foreach ($item->xpath('.//main:t') ?: [] as $text) {
                $parts[] = (string) $text;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function readWorkbookRelationships(ZipArchive $zip): array
    {
        $xml = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
        $relationships = [];

        foreach ($xml->Relationship as $relationship) {
            $relationships[(string) $relationship['Id']] = (string) $relationship['Target'];
        }

        return $relationships;
    }

    private function readSheet(ZipArchive $zip, string $path, array $sharedStrings): array
    {
        $xml = $this->xml($zip, $path);
        $xml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];

        foreach ($xml->xpath('//main:sheetData/main:row') ?: [] as $row) {
            $values = [];
            $row->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            foreach ($row->xpath('main:c') ?: [] as $cell) {
                $reference = (string) $cell['r'];
                $index = $this->columnIndex($reference);

                while (count($values) <= $index) {
                    $values[] = null;
                }

                $values[$index] = $this->cellValue($cell, $sharedStrings);
            }

            $rows[] = $values;
        }

        return $rows;
    }

    private function xml(ZipArchive $zip, string $path): SimpleXMLElement
    {
        $contents = $zip->getFromName($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read XLSX part: {$path}");
        }

        $xml = simplexml_load_string($contents);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException("Invalid XML in XLSX part: {$path}");
        }

        return $xml;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) $cell['t'];

        if ($type === 'inlineStr') {
            $cell->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            return implode('', array_map('strval', $cell->xpath('.//main:t') ?: []));
        }

        $value = $cell->v;
        if (! isset($value)) {
            return null;
        }

        $raw = (string) $value;

        if ($type === 's') {
            return $sharedStrings[(int) $raw] ?? $raw;
        }

        return $raw;
    }

    private function columnIndex(string $cellReference): int
    {
        preg_match('/^[A-Z]+/', $cellReference, $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function referenceValues(array $workbook, int $column): array
    {
        return $this->columnValues($workbook[self::SHEET_REFERENCE] ?? [], $column, true);
    }

    private function columnValues(array $rows, int $column, bool $skipHeader = false): array
    {
        $values = [];

        foreach ($skipHeader ? array_slice($rows, 1) : $rows as $row) {
            $value = $this->normalize($this->cell($row, $column));
            if ($value !== '') {
                $values[] = $value;
            }
        }

        return collect($values)->unique()->values()->all();
    }

    private function dataRows(array $rows): array
    {
        return array_slice($rows, 1);
    }

    private function cell(array $row, int $index): ?string
    {
        return isset($row[$index]) ? (string) $row[$index] : null;
    }

    private function nullable(?string $value): ?string
    {
        $normalized = $this->normalize($value);

        return $normalized === '' ? null : $this->limitText($normalized, 100);
    }

    private function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = str_replace("\xc2\xa0", ' ', (string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);

        if (preg_match('/^\d+\.0$/', $value)) {
            return substr($value, 0, -2);
        }

        return $value === '-' ? '' : $value;
    }

    private function codeFromName(string $prefix, string $name, int $maxLength = 50): string
    {
        $slug = Str::slug($this->normalize($name), '-');
        $code = strtoupper($prefix . '-' . ($slug !== '' ? $slug : 'NA'));

        return $this->limitCode($code, $maxLength);
    }

    private function limitCode(string $code, int $maxLength = 50): string
    {
        return strlen($code) <= $maxLength ? $code : substr($code, 0, $maxLength);
    }

    private function limitText(?string $text, int $maxLength): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = $this->normalize($text);

        return strlen($text) <= $maxLength ? $text : substr($text, 0, $maxLength);
    }

    private function departmentCode(string $name): string
    {
        return $this->codeFromName('DEPT', $name ?: 'SBU ICT');
    }

    private function assetCategoryCode(string $name): string
    {
        return $this->codeFromName('CAT', $name ?: 'Uncategorized');
    }

    private function serviceCode(string $name): string
    {
        return $this->codeFromName('SRV', $name ?: 'Unassigned');
    }

    private function locationCode(string $label): string
    {
        $label = $this->normalize($label) ?: 'Tidak Tercatat';
        $slug = strtoupper(Str::slug($label, '-')) ?: 'NA';
        $hash = strtoupper(substr(md5($label), 0, 8));

        return $this->limitCode('LOC-' . substr($slug, 0, 37) . '-' . $hash);
    }

    private function assetCode(string $sourceCode, string $sequence, array $usedCodes): string
    {
        $baseCode = $sourceCode !== ''
            ? $this->normalize($sourceCode)
            : 'SBU-ICT-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        $baseCode = $this->limitCode($baseCode);
        $candidate = $baseCode;
        $suffix = 2;

        while (isset($usedCodes[$candidate]) || Asset::query()->where('code', $candidate)->exists()) {
            $tail = '-' . $suffix++;
            $candidate = $this->limitCode(substr($baseCode, 0, 50 - strlen($tail)) . $tail);
        }

        return $candidate;
    }

    private function assetStatusCode(?string $status, ?string $condition): string
    {
        $status = Str::lower($this->normalize($status));
        $condition = Str::lower($this->normalize($condition));

        if (str_contains($condition, 'rusak')) {
            return 'FAULTY';
        }

        if (str_contains($status, 'backup')) {
            return 'STANDBY';
        }

        if (str_contains($status, 'tidak aktif')) {
            return 'INACTIVE';
        }

        return 'ACTIVE';
    }

    private function inferCategoryName(?string $category, ?string $name, ?string $model): string
    {
        $category = $this->normalize($category);
        if ($category !== '') {
            return $category;
        }

        $haystack = Str::lower($this->normalize($name) . ' ' . $this->normalize($model));

        return match (true) {
            str_contains($haystack, 'alcatel') || str_contains($haystack, 'os6450') => 'Switch',
            str_contains($haystack, 'access point') || str_contains($haystack, 'aruba') => 'Access Point',
            str_contains($haystack, 'thermal printer') => 'POS Thermal Printer',
            str_contains($haystack, 'tablet') => 'POS Tablet',
            str_contains($haystack, 'kiosk') => 'Kiosk',
            str_contains($haystack, 'rfid') => 'RFID Reader',
            default => 'Uncategorized',
        };
    }

    private function guessParentDepartmentCode(string $unit): string
    {
        $unit = Str::lower($unit);

        if (str_contains($unit, 'digital') || str_contains($unit, 'bms')) {
            return $this->departmentCode('Digital Solution');
        }

        if (str_contains($unit, 'network') || str_contains($unit, 'telco') || str_contains($unit, 'csoc')) {
            return $this->departmentCode('Telco & Network');
        }

        return 'SBU-ICT';
    }

    private function ownershipModel(string $ownership): string
    {
        $ownership = Str::lower($ownership);

        if (str_contains($ownership, 'sewa') || str_contains($ownership, 'pihak ketiga')) {
            return ServiceCatalog::OWNERSHIP_VENDOR;
        }

        return ServiceCatalog::OWNERSHIP_INTERNAL;
    }

    private function mappingRowsByNumber(array $rows): array
    {
        $mapping = [];

        foreach ($this->dataRows($rows) as $row) {
            $sequence = $this->normalize($this->cell($row, 0));
            if ($sequence !== '') {
                $mapping[$sequence] = $row;
            }
        }

        return $mapping;
    }

    private function locationLabel(array $row): string
    {
        $site = $this->normalize($this->cell($row, 3));
        $site = $site !== '' ? $site : 'Tidak Tercatat';
        $parts = [$site];

        foreach ([4, 5, 6, 7] as $index) {
            $part = $this->normalize($this->cell($row, $index));
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        return implode(' - ', $parts);
    }

    private function locationAddress(array $row): ?string
    {
        $site = $this->normalize($this->cell($row, 3));

        return $site !== '' ? $site : null;
    }

    private function locationDescription(array $row): string
    {
        $parts = [];
        foreach ([4 => 'Area', 5 => 'Gedung', 6 => 'Lantai', 7 => 'Ruang', 8 => 'Keterangan'] as $index => $label) {
            $value = $this->normalize($this->cell($row, $index));
            if ($value !== '') {
                $parts[] = "{$label}: {$value}";
            }
        }

        return $parts !== [] ? implode('; ', $parts) : 'Lokasi dari sheet Mapping Lokasi.';
    }

    private function installDate(?string $year): ?string
    {
        $year = $this->normalize($year);

        return preg_match('/^\d{4}$/', $year) ? "{$year}-01-01" : null;
    }

    private function criticality(string $serviceName, string $categoryName): string
    {
        $haystack = Str::lower($serviceName . ' ' . $categoryName);

        return match (true) {
            str_contains($haystack, 'data internet') => Asset::CRITICALITY_CRITICAL,
            str_contains($haystack, 'bus management') => Asset::CRITICALITY_HIGH,
            str_contains($haystack, 'autogate') => Asset::CRITICALITY_HIGH,
            str_contains($haystack, 'pos') => Asset::CRITICALITY_HIGH,
            str_contains($haystack, 'wifi') => Asset::CRITICALITY_HIGH,
            default => Asset::CRITICALITY_MEDIUM,
        };
    }

    private function assetNotes(array $assetRow, array $mappingRow, string $sourceCode, string $assetCode): string
    {
        $notes = [
            'Project: ' . ($this->normalize($this->cell($assetRow, 3)) ?: 'Tidak tercatat'),
            'Kepemilikan: ' . ($this->normalize($this->cell($assetRow, 13)) ?: 'Tidak tercatat'),
            'Kondisi sumber: ' . ($this->normalize($this->cell($assetRow, 11)) ?: 'Tidak tercatat'),
            'Status sumber: ' . ($this->normalize($this->cell($assetRow, 12)) ?: 'Tidak tercatat'),
        ];

        if ($sourceCode === '') {
            $notes[] = "Kode perangkat dibuat dari nomor baris Excel: {$assetCode}";
        } elseif ($sourceCode !== $assetCode) {
            $notes[] = "Kode perangkat sumber duplikat: {$sourceCode}";
        }

        foreach ([4 => 'Area', 5 => 'Gedung', 6 => 'Lantai', 7 => 'Ruang', 8 => 'Keterangan lokasi'] as $index => $label) {
            $value = $this->normalize($this->cell($mappingRow, $index));
            if ($value !== '') {
                $notes[] = "{$label}: {$value}";
            }
        }

        $assetRemark = $this->normalize($this->cell($assetRow, 14));
        if ($assetRemark !== '') {
            $notes[] = "Keterangan perangkat: {$assetRemark}";
        }

        return implode(' | ', $notes);
    }

    private function emailForName(string $name): string
    {
        $slug = Str::slug($name, '.');

        return ($slug !== '' ? $slug : 'personel') . '@sbu-ict.local';
    }

    private function uniqueEmail(string $email, array $seenEmails): string
    {
        if (! isset($seenEmails[$email])) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $index = 2;
        $candidate = "{$local}.{$index}@{$domain}";

        while (isset($seenEmails[$candidate])) {
            $candidate = "{$local}." . (++$index) . "@{$domain}";
        }

        return $candidate;
    }

    private function priorityCode(string $incidentLevel): string
    {
        if (preg_match('/P([1-4])/', strtoupper($incidentLevel), $matches)) {
            return 'P' . $matches[1];
        }

        return 'P3';
    }

    private function durationToMinutes(?string $duration): ?int
    {
        $duration = Str::lower($this->normalize($duration));

        if ($duration === '') {
            return null;
        }

        preg_match('/(\d+)/', $duration, $matches);
        $value = isset($matches[1]) ? (int) $matches[1] : null;

        if ($value === null) {
            return null;
        }

        return match (true) {
            str_contains($duration, 'hari') => $value * 1440,
            str_contains($duration, 'jam') => $value * 60,
            default => $value,
        };
    }

    private function slaPolicyName(string $serviceName, string $priorityCode): string
    {
        return $this->limitCode('SLA_' . strtoupper(Str::slug($serviceName, '_')) . '_' . $priorityCode, 150);
    }

    private function slaDescription(string $serviceName, ?string $availability, array $row): string
    {
        $parts = [
            'Layanan: ' . $serviceName,
            'SLA Availability: ' . ($availability ?: 'Tidak tercatat'),
            'Level incident: ' . ($this->normalize($this->cell($row, 3)) ?: 'Tidak tercatat'),
            'Kategori: ' . ($this->normalize($this->cell($row, 4)) ?: 'Tidak tercatat'),
            'Help Desk: ' . ($this->normalize($this->cell($row, 6)) ?: 'Tidak tercatat'),
            'Supervisor: ' . ($this->normalize($this->cell($row, 8)) ?: 'Tidak tercatat'),
            'Division Head: ' . ($this->normalize($this->cell($row, 9)) ?: 'Tidak tercatat'),
            'GM: ' . ($this->normalize($this->cell($row, 10)) ?: 'Tidak tercatat'),
            'EGM: ' . ($this->normalize($this->cell($row, 11)) ?: 'Tidak tercatat'),
        ];

        return implode(' | ', $parts);
    }

    private function impactForPriority(string $priorityCode): string
    {
        return match ($priorityCode) {
            'P1', 'P2' => 'high',
            'P4' => 'low',
            default => 'medium',
        };
    }
}
