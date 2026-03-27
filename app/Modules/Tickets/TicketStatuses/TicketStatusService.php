<?php

namespace App\Modules\Tickets\TicketStatuses;

use App\Models\TicketStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketStatusService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TicketStatus::query()
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

    public function create(array $data): TicketStatus
    {
        return TicketStatus::query()->create($this->preparePayload($data));
    }

    public function update(TicketStatus $ticketStatus, array $data): TicketStatus
    {
        $ticketStatus->update($this->preparePayload($data));

        return $ticketStatus->fresh();
    }

    public function delete(TicketStatus $ticketStatus): void
    {
        $ticketStatus->delete();
    }

    private function preparePayload(array $data): array
    {
        foreach (['is_open', 'is_in_progress', 'is_closed', 'is_active'] as $booleanField) {
            if (array_key_exists($booleanField, $data)) {
                $data[$booleanField] = (bool) $data[$booleanField];
            }
        }

        return $data;
    }
}
