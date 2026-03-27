<?php

namespace App\Modules\MasterData\Departments;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Department::query()
            ->with(['parentDepartment:id,name', 'head:id,name'])
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

    public function create(array $data): Department
    {
        return Department::create($this->preparePayload($data));
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($this->preparePayload($data));

        return $department->fresh(['parentDepartment:id,name', 'head:id,name']);
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
