<?php

namespace App\Modules\MasterData\Vendors;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VendorService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Vendor::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Vendor
    {
        return Vendor::create($this->preparePayload($data));
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($this->preparePayload($data));

        return $vendor->fresh();
    }

    public function delete(Vendor $vendor): void
    {
        $vendor->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
