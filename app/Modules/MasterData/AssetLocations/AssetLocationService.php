<?php

namespace App\Modules\MasterData\AssetLocations;

use App\Models\AssetLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AssetLocationService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return AssetLocation::query()
            ->with('department:id,name')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): AssetLocation
    {
        return AssetLocation::create($this->preparePayload($data));
    }

    public function update(AssetLocation $assetLocation, array $data): AssetLocation
    {
        $assetLocation->update($this->preparePayload($data));

        return $assetLocation->fresh('department:id,name');
    }

    public function delete(AssetLocation $assetLocation): void
    {
        $assetLocation->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
