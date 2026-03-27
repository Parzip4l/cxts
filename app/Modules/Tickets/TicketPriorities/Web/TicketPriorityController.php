<?php

namespace App\Modules\Tickets\TicketPriorities\Web;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use App\Modules\Tickets\TicketPriorities\Requests\StoreTicketPriorityRequest;
use App\Modules\Tickets\TicketPriorities\Requests\UpdateTicketPriorityRequest;
use App\Modules\Tickets\TicketPriorities\TicketPriorityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketPriorityController extends Controller
{
    public function __construct(private readonly TicketPriorityService $ticketPriorityService)
    {
        $this->authorizeResource(TicketPriority::class, 'ticket_priority');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $ticketPriorities = $this->ticketPriorityService->paginate($filters);

        return view('modules.tickets.priorities.index', [
            'ticketPriorities' => $ticketPriorities,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.priorities.form', [
            'ticketPriority' => new TicketPriority(),
            'action' => route('master-data.ticket-priorities.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Ticket Priority',
        ]);
    }

    public function store(StoreTicketPriorityRequest $request): RedirectResponse
    {
        $this->ticketPriorityService->create($request->validated());

        return redirect()
            ->route('master-data.ticket-priorities.index')
            ->with('success', 'Ticket priority has been created.');
    }

    public function edit(TicketPriority $ticketPriority): View
    {
        return view('modules.tickets.priorities.form', [
            'ticketPriority' => $ticketPriority,
            'action' => route('master-data.ticket-priorities.update', $ticketPriority),
            'method' => 'PUT',
            'pageTitle' => 'Edit Ticket Priority',
        ]);
    }

    public function update(UpdateTicketPriorityRequest $request, TicketPriority $ticketPriority): RedirectResponse
    {
        $this->ticketPriorityService->update($ticketPriority, $request->validated());

        return redirect()
            ->route('master-data.ticket-priorities.index')
            ->with('success', 'Ticket priority has been updated.');
    }

    public function destroy(TicketPriority $ticketPriority): RedirectResponse
    {
        $this->ticketPriorityService->delete($ticketPriority);

        return redirect()
            ->route('master-data.ticket-priorities.index')
            ->with('success', 'Ticket priority has been deleted.');
    }
}
