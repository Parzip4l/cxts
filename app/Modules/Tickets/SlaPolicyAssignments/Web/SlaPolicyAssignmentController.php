<?php

namespace App\Modules\Tickets\SlaPolicyAssignments\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceCatalog;
use App\Models\SlaPolicy;
use App\Models\SlaPolicyAssignment;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketSubcategory;
use App\Modules\Tickets\SlaPolicyAssignments\Requests\StoreSlaPolicyAssignmentRequest;
use App\Modules\Tickets\SlaPolicyAssignments\Requests\UpdateSlaPolicyAssignmentRequest;
use App\Modules\Tickets\SlaPolicyAssignments\SlaPolicyAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SlaPolicyAssignmentController extends Controller
{
    private const TICKET_TYPE_OPTIONS = [
        'incident',
        'service_request',
        'change_request',
    ];

    public function __construct(private readonly SlaPolicyAssignmentService $slaPolicyAssignmentService)
    {
        $this->authorizeResource(SlaPolicyAssignment::class, 'sla_policy_assignment');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'sla_policy_id' => $request->input('sla_policy_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return view('modules.tickets.sla-policy-assignments.index', [
            'slaPolicyAssignments' => $this->slaPolicyAssignmentService->paginate($filters),
            'filters' => $filters,
            'policyOptions' => SlaPolicy::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('modules.tickets.sla-policy-assignments.form', [
            'slaPolicyAssignment' => new SlaPolicyAssignment(),
            'action' => route('master-data.sla-policy-assignments.store'),
            'method' => 'POST',
            'pageTitle' => 'Create SLA Rule',
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreSlaPolicyAssignmentRequest $request): RedirectResponse
    {
        $this->slaPolicyAssignmentService->create($request->validated());

        return redirect()
            ->route('master-data.sla-policy-assignments.index')
            ->with('success', 'SLA rule has been created.');
    }

    public function edit(SlaPolicyAssignment $slaPolicyAssignment): View
    {
        return view('modules.tickets.sla-policy-assignments.form', [
            'slaPolicyAssignment' => $slaPolicyAssignment,
            'action' => route('master-data.sla-policy-assignments.update', $slaPolicyAssignment),
            'method' => 'PUT',
            'pageTitle' => 'Edit SLA Rule',
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdateSlaPolicyAssignmentRequest $request, SlaPolicyAssignment $slaPolicyAssignment): RedirectResponse
    {
        $this->slaPolicyAssignmentService->update($slaPolicyAssignment, $request->validated());

        return redirect()
            ->route('master-data.sla-policy-assignments.index')
            ->with('success', 'SLA rule has been updated.');
    }

    public function destroy(SlaPolicyAssignment $slaPolicyAssignment): RedirectResponse
    {
        $this->slaPolicyAssignmentService->delete($slaPolicyAssignment);

        return redirect()
            ->route('master-data.sla-policy-assignments.index')
            ->with('success', 'SLA rule has been deleted.');
    }

    private function formOptions(): array
    {
        return [
            'policyOptions' => SlaPolicy::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categoryOptions' => TicketCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subcategoryOptions' => TicketSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'detailSubcategoryOptions' => TicketDetailSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_subcategory_id']),
            'serviceOptions' => ServiceCatalog::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'priorityOptions' => TicketPriority::query()->where('is_active', true)->orderBy('level')->get(['id', 'name', 'code']),
            'ticketTypeOptions' => self::TICKET_TYPE_OPTIONS,
            'impactOptions' => ['low', 'medium', 'high'],
            'urgencyOptions' => ['low', 'medium', 'high'],
        ];
    }
}
