<?php

namespace App\Modules\Tickets\TicketCategories;

use App\Models\TicketCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketCategoryService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TicketCategory::query()
            ->with('approver:id,name')
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

    public function create(array $data): TicketCategory
    {
        return TicketCategory::query()->create($this->preparePayload($data))->fresh('approver:id,name');
    }

    public function update(TicketCategory $ticketCategory, array $data): TicketCategory
    {
        $ticketCategory->update($this->preparePayload($data));

        return $ticketCategory->fresh('approver:id,name');
    }

    public function delete(TicketCategory $ticketCategory): void
    {
        $ticketCategory->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('requires_approval', $data)) {
            $data['requires_approval'] = (bool) $data['requires_approval'];
        }

        if (array_key_exists('allow_direct_assignment', $data)) {
            $data['allow_direct_assignment'] = (bool) $data['allow_direct_assignment'];
        }

        if (array_key_exists('approver_user_id', $data) && ($data['approver_user_id'] === '' || $data['approver_user_id'] === null)) {
            $data['approver_user_id'] = null;
        }

        if (array_key_exists('approver_strategy', $data) && ($data['approver_strategy'] === '' || $data['approver_strategy'] === null)) {
            $data['approver_strategy'] = null;
        }

        if (array_key_exists('approver_role_code', $data) && ($data['approver_role_code'] === '' || $data['approver_role_code'] === null)) {
            $data['approver_role_code'] = null;
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
