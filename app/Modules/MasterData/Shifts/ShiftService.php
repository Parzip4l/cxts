<?php

namespace App\Modules\MasterData\Shifts;

use App\Models\Shift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShiftService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Shift::query()
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

    public function create(array $data): Shift
    {
        return Shift::query()->create($this->preparePayload($data));
    }

    public function update(Shift $shift, array $data): Shift
    {
        $shift->update($this->preparePayload($data));

        return $shift->fresh();
    }

    public function delete(Shift $shift): void
    {
        $shift->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        if (array_key_exists('is_overnight', $data)) {
            $data['is_overnight'] = (bool) $data['is_overnight'];
        }

        return $data;
    }
}
