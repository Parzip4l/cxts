<?php

namespace App\Modules\Tickets\TicketStatuses\Web;

use App\Http\Controllers\Controller;
use App\Models\TicketStatus;
use App\Modules\Tickets\TicketStatuses\Requests\StoreTicketStatusRequest;
use App\Modules\Tickets\TicketStatuses\Requests\UpdateTicketStatusRequest;
use App\Modules\Tickets\TicketStatuses\TicketStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketStatusController extends Controller
{
    public function __construct(private readonly TicketStatusService $ticketStatusService)
    {
        $this->authorizeResource(TicketStatus::class, 'ticket_status');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $ticketStatuses = $this->ticketStatusService->paginate($filters);

        return view('modules.tickets.statuses.index', [
            'ticketStatuses' => $ticketStatuses,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.statuses.form', [
            'ticketStatus' => new TicketStatus(),
            'action' => route('master-data.ticket-statuses.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Workflow Status',
        ]);
    }

    public function store(StoreTicketStatusRequest $request): RedirectResponse
    {
        $this->ticketStatusService->create($request->validated());

        return redirect()
            ->route('master-data.ticket-statuses.index')
            ->with('success', 'Workflow status has been created.');
    }

    public function edit(TicketStatus $ticketStatus): View
    {
        return view('modules.tickets.statuses.form', [
            'ticketStatus' => $ticketStatus,
            'action' => route('master-data.ticket-statuses.update', $ticketStatus),
            'method' => 'PUT',
            'pageTitle' => 'Edit Workflow Status',
        ]);
    }

    public function update(UpdateTicketStatusRequest $request, TicketStatus $ticketStatus): RedirectResponse
    {
        $this->ticketStatusService->update($ticketStatus, $request->validated());

        return redirect()
            ->route('master-data.ticket-statuses.index')
            ->with('success', 'Workflow status has been updated.');
    }

    public function destroy(TicketStatus $ticketStatus): RedirectResponse
    {
        $this->ticketStatusService->delete($ticketStatus);

        return redirect()
            ->route('master-data.ticket-statuses.index')
            ->with('success', 'Workflow status has been deleted.');
    }
}
