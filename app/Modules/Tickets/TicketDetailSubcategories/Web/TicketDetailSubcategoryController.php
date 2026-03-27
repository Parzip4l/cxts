<?php

namespace App\Modules\Tickets\TicketDetailSubcategories\Web;

use App\Http\Controllers\Controller;
use App\Models\EngineerSkill;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Modules\Tickets\TicketDetailSubcategories\Requests\StoreTicketDetailSubcategoryRequest;
use App\Modules\Tickets\TicketDetailSubcategories\Requests\UpdateTicketDetailSubcategoryRequest;
use App\Modules\Tickets\TicketDetailSubcategories\TicketDetailSubcategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketDetailSubcategoryController extends Controller
{
    public function __construct(private readonly TicketDetailSubcategoryService $ticketDetailSubcategoryService)
    {
        $this->authorizeResource(TicketDetailSubcategory::class, 'ticket_detail_subcategory');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'ticket_subcategory_id' => $request->input('ticket_subcategory_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return view('modules.tickets.detail-subcategories.index', [
            'ticketDetailSubcategories' => $this->ticketDetailSubcategoryService->paginate($filters),
            'filters' => $filters,
            'categoryOptions' => TicketSubcategory::query()->with('category:id,name')->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.detail-subcategories.form', [
            'ticketDetailSubcategory' => new TicketDetailSubcategory(),
            'categoryOptions' => TicketSubcategory::query()->with('category:id,name')->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'skillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name', 'role']),
            'approverStrategyOptions' => TicketCategory::approverStrategies(),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'action' => route('master-data.ticket-detail-subcategories.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Ticket Sub Category',
        ]);
    }

    public function store(StoreTicketDetailSubcategoryRequest $request): RedirectResponse
    {
        $this->ticketDetailSubcategoryService->create($request->validated());

        return redirect()
            ->route('master-data.ticket-detail-subcategories.index')
            ->with('success', 'Ticket sub category has been created.');
    }

    public function edit(TicketDetailSubcategory $ticketDetailSubcategory): View
    {
        return view('modules.tickets.detail-subcategories.form', [
            'ticketDetailSubcategory' => $ticketDetailSubcategory->load('engineerSkills:id,name'),
            'categoryOptions' => TicketSubcategory::query()->with('category:id,name')->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'skillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name', 'role']),
            'approverStrategyOptions' => TicketCategory::approverStrategies(),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'action' => route('master-data.ticket-detail-subcategories.update', $ticketDetailSubcategory),
            'method' => 'PUT',
            'pageTitle' => 'Edit Ticket Sub Category',
        ]);
    }

    public function update(UpdateTicketDetailSubcategoryRequest $request, TicketDetailSubcategory $ticketDetailSubcategory): RedirectResponse
    {
        $this->ticketDetailSubcategoryService->update($ticketDetailSubcategory, $request->validated());

        return redirect()
            ->route('master-data.ticket-detail-subcategories.index')
            ->with('success', 'Ticket sub category has been updated.');
    }

    public function destroy(TicketDetailSubcategory $ticketDetailSubcategory): RedirectResponse
    {
        $this->ticketDetailSubcategoryService->delete($ticketDetailSubcategory);

        return redirect()
            ->route('master-data.ticket-detail-subcategories.index')
            ->with('success', 'Ticket sub category has been deleted.');
    }
}
