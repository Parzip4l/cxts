<?php

namespace App\Modules\MasterData\Services;

use App\Models\ServiceCatalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ServiceCatalogService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ServiceCatalog::query()
            ->with(['ownerDepartment:id,name', 'vendor:id,name', 'manager:id,name', 'defaultRequestSlaPolicy:id,name'])
            ->withCount(['assets', 'tickets'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('service_category', 'like', "%{$search}%");
                });
            })
            ->when($filters['ownership_model'] ?? null, fn ($query, $ownershipModel) => $query->where('ownership_model', $ownershipModel))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->when(array_key_exists('is_requestable', $filters), fn ($query) => $query->where('is_requestable', (bool) $filters['is_requestable']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): ServiceCatalog
    {
        $skillIds = $this->extractSkillIds($data);
        $service = ServiceCatalog::create($this->preparePayload($data));
        $service->engineerSkills()->sync($skillIds);

        return $service->fresh(['ownerDepartment:id,name', 'vendor:id,name', 'manager:id,name', 'defaultRequestSlaPolicy:id,name', 'engineerSkills:id,name']);
    }

    public function update(ServiceCatalog $service, array $data): ServiceCatalog
    {
        $skillIds = $this->extractSkillIds($data);
        $service->update($this->preparePayload($data));
        $service->engineerSkills()->sync($skillIds);

        return $service->fresh(['ownerDepartment:id,name', 'vendor:id,name', 'manager:id,name', 'defaultRequestSlaPolicy:id,name', 'engineerSkills:id,name']);
    }

    public function delete(ServiceCatalog $service): void
    {
        $service->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        if (array_key_exists('is_requestable', $data)) {
            $data['is_requestable'] = (bool) $data['is_requestable'];
        }

        if (($data['default_request_approval_required'] ?? null) === '') {
            $data['default_request_approval_required'] = null;
        } elseif (array_key_exists('default_request_approval_required', $data)) {
            $data['default_request_approval_required'] = $data['default_request_approval_required'] === null
                ? null
                : (bool) $data['default_request_approval_required'];
        }

        if (($data['request_form_schema'] ?? null) === '') {
            $data['request_form_schema'] = null;
        } elseif (isset($data['request_form_schema']) && is_string($data['request_form_schema'])) {
            $data['request_form_schema'] = json_decode($data['request_form_schema'], true);
        }

        unset($data['engineer_skill_ids']);

        return $data;
    }

    private function extractSkillIds(array $data): array
    {
        return collect($data['engineer_skill_ids'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }
}
