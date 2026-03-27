<?php

namespace App\Modules\Tickets\TicketPriorities;

use App\Models\TicketPriority;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketPriorityService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TicketPriority::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('level')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): TicketPriority
    {
        return TicketPriority::query()->create($this->preparePayload($data));
    }

    public function update(TicketPriority $ticketPriority, array $data): TicketPriority
    {
        $ticketPriority->update($this->preparePayload($data));

        return $ticketPriority->fresh();
    }

    public function delete(TicketPriority $ticketPriority): void
    {
        $ticketPriority->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
