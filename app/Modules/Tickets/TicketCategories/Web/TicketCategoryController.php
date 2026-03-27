<?php

namespace App\Modules\Tickets\TicketCategories\Web;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use App\Models\User;
use App\Modules\Tickets\TicketCategories\Requests\StoreTicketCategoryRequest;
use App\Modules\Tickets\TicketCategories\Requests\UpdateTicketCategoryRequest;
use App\Modules\Tickets\TicketCategories\TicketCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketCategoryController extends Controller
{
    public function __construct(private readonly TicketCategoryService $ticketCategoryService)
    {
        $this->authorizeResource(TicketCategory::class, 'ticket_category');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $ticketCategories = $this->ticketCategoryService->paginate($filters);

        return view('modules.tickets.categories.index', [
            'ticketCategories' => $ticketCategories,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.categories.form', [
            'ticketCategory' => new TicketCategory(),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name', 'role']),
            'approverStrategyOptions' => TicketCategory::approverStrategies(),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'action' => route('master-data.ticket-categories.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Ticket Type',
        ]);
    }

    public function store(StoreTicketCategoryRequest $request): RedirectResponse
    {
        $this->ticketCategoryService->create($request->validated());

        return redirect()
            ->route('master-data.ticket-categories.index')
            ->with('success', 'Ticket type has been created.');
    }

    public function edit(TicketCategory $ticketCategory): View
    {
        return view('modules.tickets.categories.form', [
            'ticketCategory' => $ticketCategory,
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name', 'role']),
            'approverStrategyOptions' => TicketCategory::approverStrategies(),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'action' => route('master-data.ticket-categories.update', $ticketCategory),
            'method' => 'PUT',
            'pageTitle' => 'Edit Ticket Type',
        ]);
    }

    public function update(UpdateTicketCategoryRequest $request, TicketCategory $ticketCategory): RedirectResponse
    {
        $this->ticketCategoryService->update($ticketCategory, $request->validated());

        return redirect()
            ->route('master-data.ticket-categories.index')
            ->with('success', 'Ticket type has been updated.');
    }

    public function destroy(TicketCategory $ticketCategory): RedirectResponse
    {
        $this->ticketCategoryService->delete($ticketCategory);

        return redirect()
            ->route('master-data.ticket-categories.index')
            ->with('success', 'Ticket type has been deleted.');
    }
}
