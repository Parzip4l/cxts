<?php

namespace App\Modules\MasterData\Assets;

use App\Models\Asset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AssetService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function count(array $filters = []): int
    {
        return $this->query($filters)->count();
    }

    public function create(array $data): Asset
    {
        return Asset::create($this->preparePayload($data));
    }

    public function update(Asset $asset, array $data): Asset
    {
        $asset->update($this->preparePayload($data));

        return $asset->fresh([
            'category:id,name',
            'service:id,name',
            'ownerDepartment:id,name',
            'vendor:id,name',
            'location:id,name',
            'status:id,name',
        ]);
    }

    public function delete(Asset $asset): void
    {
        $asset->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }

    private function query(array $filters = []): Builder
    {
        return Asset::query()
            ->with([
                'category:id,name',
                'service:id,name',
                'ownerDepartment:id,name',
                'vendor:id,name',
                'location:id,name',
                'status:id,name',
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($filters['asset_category_id'] ?? null, fn ($query, $assetCategoryId) => $query->where('asset_category_id', $assetCategoryId))
            ->when($filters['asset_status_id'] ?? null, fn ($query, $assetStatusId) => $query->where('asset_status_id', $assetStatusId))
            ->when($filters['service_id'] ?? null, fn ($query, $serviceId) => $query->where('service_id', $serviceId))
            ->when($filters['department_owner_id'] ?? null, fn ($query, $departmentOwnerId) => $query->where('department_owner_id', $departmentOwnerId))
            ->when($filters['vendor_id'] ?? null, fn ($query, $vendorId) => $query->where('vendor_id', $vendorId))
            ->when($filters['asset_location_id'] ?? null, fn ($query, $assetLocationId) => $query->where('asset_location_id', $assetLocationId))
            ->when($filters['criticality'] ?? null, fn ($query, $criticality) => $query->where('criticality', $criticality))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']));
    }
}
