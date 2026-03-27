<?php

namespace App\Modules\Tickets\TicketSubcategories\Web;

use App\Http\Controllers\Controller;
use App\Models\EngineerSkill;
use App\Models\TicketCategory;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Tickets\TicketSubcategories\Requests\StoreTicketSubcategoryRequest;
use App\Modules\Tickets\TicketSubcategories\Requests\UpdateTicketSubcategoryRequest;
use App\Modules\Tickets\TicketSubcategories\TicketSubcategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketSubcategoryController extends Controller
{
    public function __construct(private readonly TicketSubcategoryService $ticketSubcategoryService)
    {
        $this->authorizeResource(TicketSubcategory::class, 'ticket_subcategory');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'ticket_category_id' => $request->input('ticket_category_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $ticketSubcategories = $this->ticketSubcategoryService->paginate($filters);

        return view('modules.tickets.subcategories.index', [
            'ticketSubcategories' => $ticketSubcategories,
            'filters' => $filters,
            'categoryOptions' => TicketCategory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.subcategories.form', [
            'ticketSubcategory' => new TicketSubcategory(),
            'categoryOptions' => TicketCategory::query()->orderBy('name')->get(['id', 'name']),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name', 'role']),
            'approverStrategyOptions' => TicketCategory::approverStrategies(),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'action' => route('master-data.ticket-subcategories.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Ticket Category',
        ]);
    }

    public function store(StoreTicketSubcategoryRequest $request): RedirectResponse
    {
        $this->ticketSubcategoryService->create($request->validated());

        return redirect()
            ->route('master-data.ticket-subcategories.index')
            ->with('success', 'Ticket category has been created.');
    }

    public function edit(TicketSubcategory $ticketSubcategory): View
    {
        return view('modules.tickets.subcategories.form', [
            'ticketSubcategory' => $ticketSubcategory,
            'categoryOptions' => TicketCategory::query()->orderBy('name')->get(['id', 'name']),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name', 'role']),
            'approverStrategyOptions' => TicketCategory::approverStrategies(),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'action' => route('master-data.ticket-subcategories.update', $ticketSubcategory),
            'method' => 'PUT',
            'pageTitle' => 'Edit Ticket Category',
        ]);
    }

    public function update(UpdateTicketSubcategoryRequest $request, TicketSubcategory $ticketSubcategory): RedirectResponse
    {
        $this->ticketSubcategoryService->update($ticketSubcategory, $request->validated());

        return redirect()
            ->route('master-data.ticket-subcategories.index')
            ->with('success', 'Ticket category has been updated.');
    }

    public function destroy(TicketSubcategory $ticketSubcategory): RedirectResponse
    {
        $this->ticketSubcategoryService->delete($ticketSubcategory);

        return redirect()
            ->route('master-data.ticket-subcategories.index')
            ->with('success', 'Ticket category has been deleted.');
    }
}
