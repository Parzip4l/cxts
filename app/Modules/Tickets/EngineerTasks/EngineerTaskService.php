<?php

namespace App\Modules\Tickets\EngineerTasks;

use App\Models\Ticket;
use App\Models\User;
use App\Modules\Tickets\Tickets\TicketService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EngineerTaskService
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function paginateMyTasks(User $engineer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Ticket::query()
            ->with([
                'category:id,name',
                'priority:id,name',
                'status:id,name,code',
                'asset:id,name',
                'assetLocation:id,name',
            ])
            ->where('assigned_engineer_id', $engineer->id)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('ticket_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($filters['ticket_status_id'] ?? null, fn ($query, $statusId) => $query->where('ticket_status_id', $statusId))
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function ensureOwnedByEngineer(Ticket $ticket, User $engineer): void
    {
        if ((int) $ticket->assigned_engineer_id !== (int) $engineer->id) {
            throw new AccessDeniedHttpException('Task is not assigned to this engineer.');
        }
    }

    public function start(Ticket $ticket, User $engineer, ?string $notes = null): Ticket
    {
        $this->ensureOwnedByEngineer($ticket, $engineer);

        return $this->ticketService->startWork($ticket, $engineer, $notes);
    }

    public function pause(Ticket $ticket, User $engineer, ?string $notes = null): Ticket
    {
        $this->ensureOwnedByEngineer($ticket, $engineer);

        return $this->ticketService->pauseWork($ticket, $engineer, $notes);
    }

    public function resume(Ticket $ticket, User $engineer, ?string $notes = null): Ticket
    {
        $this->ensureOwnedByEngineer($ticket, $engineer);

        return $this->ticketService->resumeWork($ticket, $engineer, $notes);
    }

    public function complete(Ticket $ticket, User $engineer, ?string $notes = null): Ticket
    {
        $this->ensureOwnedByEngineer($ticket, $engineer);

        return $this->ticketService->completeWork($ticket, $engineer, $notes);
    }

    public function addWorklog(Ticket $ticket, User $engineer, array $data)
    {
        $this->ensureOwnedByEngineer($ticket, $engineer);

        return $this->ticketService->addWorklog($ticket, $engineer, $data);
    }

    public function paginateHistory(User $engineer, int $perPage = 15): LengthAwarePaginator
    {
        $closedStatusCodes = [TicketService::STATUS_COMPLETED, TicketService::STATUS_CLOSED];

        return Ticket::query()
            ->with(['status:id,name,code', 'category:id,name', 'priority:id,name'])
            ->where('assigned_engineer_id', $engineer->id)
            ->whereHas('status', fn ($query) => $query->whereIn('code', $closedStatusCodes))
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
