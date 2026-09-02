<?php

namespace App\Modules\MasterData\Assets;

use App\Models\Asset;
use App\Models\AssetRelationship;
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
        $dependencyIds = $this->extractRelationshipIds($data, 'depends_on_asset_ids');
        $supportedIds = $this->extractRelationshipIds($data, 'supports_asset_ids');
        $asset = Asset::create($this->preparePayload($data));
        $this->syncRelationships($asset, AssetRelationship::TYPE_DEPENDS_ON, $dependencyIds);
        $this->syncRelationships($asset, AssetRelationship::TYPE_SUPPORTS, $supportedIds);

        return $asset->fresh($this->relations());
    }

    public function update(Asset $asset, array $data): Asset
    {
        $dependencyIds = $this->extractRelationshipIds($data, 'depends_on_asset_ids');
        $supportedIds = $this->extractRelationshipIds($data, 'supports_asset_ids');
        $asset->update($this->preparePayload($data));
        $this->syncRelationships($asset, AssetRelationship::TYPE_DEPENDS_ON, $dependencyIds);
        $this->syncRelationships($asset, AssetRelationship::TYPE_SUPPORTS, $supportedIds);

        return $asset->fresh($this->relations());
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

        if (array_key_exists('is_configuration_item', $data)) {
            $data['is_configuration_item'] = (bool) $data['is_configuration_item'];
        }

        if (! ($data['is_configuration_item'] ?? false)) {
            $data['ci_type'] = null;
            $data['ci_lifecycle_state'] = null;
            $data['ci_governance_note'] = null;
        }

        unset($data['depends_on_asset_ids'], $data['supports_asset_ids']);

        return $data;
    }

    private function query(array $filters = []): Builder
    {
        return Asset::query()
            ->with([
                ...$this->relations(),
            ])
            ->withCount('tickets')
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
            ->when($filters['ci_type'] ?? null, fn ($query, $ciType) => $query->where('ci_type', $ciType))
            ->when(array_key_exists('is_configuration_item', $filters), fn ($query) => $query->where('is_configuration_item', (bool) $filters['is_configuration_item']))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']));
    }

    private function relations(): array
    {
        return [
            'category:id,name',
            'service:id,name',
            'ownerDepartment:id,name',
            'vendor:id,name',
            'location:id,name',
            'status:id,name',
            'relationships.relatedAsset:id,code,name,service_id,criticality',
            'impactedByRelationships.asset:id,code,name,service_id,criticality',
        ];
    }

    private function extractRelationshipIds(array $data, string $key): array
    {
        return collect($data[$key] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function syncRelationships(Asset $asset, string $type, array $relatedAssetIds): void
    {
        $relatedAssetIds = collect($relatedAssetIds)
            ->reject(fn (int $assetId): bool => $assetId === (int) $asset->id)
            ->values();

        AssetRelationship::query()
            ->where('asset_id', $asset->id)
            ->where('relationship_type', $type)
            ->whereNotIn('related_asset_id', $relatedAssetIds->all())
            ->delete();

        foreach ($relatedAssetIds as $relatedAssetId) {
            AssetRelationship::query()->updateOrCreate(
                [
                    'asset_id' => $asset->id,
                    'related_asset_id' => $relatedAssetId,
                    'relationship_type' => $type,
                ],
                []
            );
        }
    }
}
