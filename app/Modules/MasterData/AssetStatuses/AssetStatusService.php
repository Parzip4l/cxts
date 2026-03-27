<?php

namespace App\Modules\MasterData\AssetStatuses;

use App\Models\AssetStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AssetStatusService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return AssetStatus::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_operational', $filters), fn ($query) => $query->where('is_operational', (bool) $filters['is_operational']))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): AssetStatus
    {
        return AssetStatus::create($this->preparePayload($data));
    }

    public function update(AssetStatus $assetStatus, array $data): AssetStatus
    {
        $assetStatus->update($this->preparePayload($data));

        return $assetStatus->fresh();
    }

    public function delete(AssetStatus $assetStatus): void
    {
        $assetStatus->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_operational', $data)) {
            $data['is_operational'] = (bool) $data['is_operational'];
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
