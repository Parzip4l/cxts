<?php

namespace App\Modules\Tickets\Tickets\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLocation;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketSubcategory;
use App\Models\User;
use App\Services\Tickets\EngineerRecommendationService;
use App\Modules\Tickets\Tickets\Requests\AssignTicketRequest;
use App\Modules\Tickets\Tickets\Requests\StoreTicketRequest;
use App\Modules\Tickets\Tickets\Requests\TicketDecisionRequest;
use App\Modules\Tickets\Tickets\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly EngineerRecommendationService $engineerRecommendationService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ticket::class);

        $filters = [
            'search' => $request->input('search'),
            'ticket_status_id' => $request->input('ticket_status_id'),
            'ticket_priority_id' => $request->input('ticket_priority_id'),
            'ticket_category_id' => $request->input('ticket_category_id'),
            'ticket_subcategory_id' => $request->input('ticket_subcategory_id'),
            'ticket_detail_subcategory_id' => $request->input('ticket_detail_subcategory_id'),
            'assigned_engineer_id' => $request->input('assigned_engineer_id'),
            'expected_approver_id' => $request->input('expected_approver_id'),
            'expected_approver_role_code' => $request->input('expected_approver_role_code'),
            'approval_status' => $request->input('approval_status'),
        ];

        $tickets = $this->ticketService->paginate($filters, actor: $request->user());

        return view('modules.tickets.tickets.index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'statusOptions' => TicketStatus::query()->orderBy('name')->get(['id', 'name']),
            'priorityOptions' => TicketPriority::query()->orderBy('level')->get(['id', 'name']),
            'categoryOptions' => TicketCategory::query()->orderBy('name')->get(['id', 'name']),
            'subcategoryOptions' => TicketSubcategory::query()->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'detailSubcategoryOptions' => TicketDetailSubcategory::query()->orderBy('name')->get(['id', 'name', 'ticket_subcategory_id']),
            'engineerOptions' => User::query()->where('role', 'engineer')->orderBy('name')->get(['id', 'name']),
            'approverOptions' => User::query()->whereIn('role', ['super_admin', 'operational_admin', 'supervisor'])->orderBy('name')->get(['id', 'name']),
            'approverRoleOptions' => TicketCategory::approverRoleOptions(),
            'approvalStatusOptions' => [
                Ticket::APPROVAL_STATUS_NOT_REQUIRED => 'Not Required',
                Ticket::APPROVAL_STATUS_PENDING => 'Pending',
                Ticket::APPROVAL_STATUS_APPROVED => 'Approved',
                Ticket::APPROVAL_STATUS_REJECTED => 'Rejected',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Ticket::class);

        $authUser = $request->user();
        $priorityOptions = TicketPriority::query()->where('is_active', true)->orderBy('level')->get(['id', 'code', 'name']);

        return view('modules.tickets.tickets.form', [
            'action' => route('tickets.store'),
            'ticket' => new Ticket(),
            'requesterOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'requesterDepartmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'categoryOptions' => TicketCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subcategoryOptions' => TicketSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_category_id']),
            'detailSubcategoryOptions' => TicketDetailSubcategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ticket_subcategory_id']),
            'priorityOptions' => $priorityOptions,
            'serviceOptions' => ServiceCatalog::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'assetOptions' => Asset::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'service_id', 'asset_location_id', 'asset_category_id']),
            'locationOptions' => AssetLocation::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'defaultRequesterId' => $authUser?->id,
            'defaultRequesterDepartmentId' => $authUser?->department_id,
            'defaultPriorityId' => $this->resolveDefaultPriorityId($priorityOptions),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        if (! isset($payload['requester_id']) || $payload['requester_id'] === null) {
            $payload['requester_id'] = $request->user()?->id;
        }

        if (! isset($payload['requester_department_id']) || $payload['requester_department_id'] === null) {
            $payload['requester_department_id'] = $request->user()?->department_id;
        }

        $payload['ticket_priority_id'] = $payload['ticket_priority_id'] ?? $this->resolveDefaultPriorityId();
        $payload['source'] = $payload['source'] ?? 'web';
        $payload['impact'] = $payload['impact'] ?? 'medium';
        $payload['urgency'] = $payload['urgency'] ?? 'medium';

        $ticket = $this->ticketService->create($payload, $request->user());

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket has been created.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'requester:id,name',
            'requesterDepartment:id,name',
            'category:id,name,requires_approval,allow_direct_assignment,approver_strategy,approver_user_id,approver_role_code',
            'category.approver:id,name',
            'subcategory:id,name,requires_approval,allow_direct_assignment,approver_strategy,approver_user_id,approver_role_code',
            'subcategory.approver:id,name',
            'detailSubcategory:id,name,requires_approval,allow_direct_assignment,approver_strategy,approver_user_id,approver_role_code',
            'detailSubcategory.approver:id,name',
            'priority:id,name',
            'status:id,name,code',
            'service:id,name',
            'asset:id,name',
            'asset.category:id,name',
            'assetLocation:id,name',
            'inspection:id,inspection_number',
            'assignedEngineer:id,name',
            'assignedEngineer.engineerSkills:id,name',
            'expectedApprover:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'assignmentReadyBy:id,name',
            'assignments.assignedEngineer:id,name',
            'assignments.previousEngineer:id,name',
            'assignments.assignedBy:id,name',
            'worklogs.user:id,name',
            'activities.actor:id,name',
            'activities.oldStatus:id,name',
            'activities.newStatus:id,name',
            'attachments',
            'attachments.uploadedBy:id,name',
        ]);

        $engineerRecommendation = $this->engineerRecommendationService->recommendForTicket($ticket);
        $allRecommendationOptions = collect()
            ->merge($engineerRecommendation['recommended_engineers'])
            ->merge($engineerRecommendation['fallback_engineers'])
            ->unique('id')
            ->values();
        $assignmentFilters = [
            'department_id' => $request->filled('assignment_department_id')
                ? (int) $request->integer('assignment_department_id')
                : null,
            'team_label' => $request->filled('assignment_team_label')
                ? trim((string) $request->string('assignment_team_label'))
                : null,
        ];
        $engineerRecommendation = $this->filterRecommendation($engineerRecommendation, $assignmentFilters);

        return view('modules.tickets.tickets.show', [
            'ticket' => $ticket,
            'engineerOptions' => $engineerRecommendation['has_recommendation']
                ? $engineerRecommendation['recommended_engineers']
                : $engineerRecommendation['fallback_engineers'],
            'fallbackEngineerOptions' => $engineerRecommendation['fallback_engineers'],
            'engineerRecommendation' => $engineerRecommendation,
            'assignmentFilters' => $assignmentFilters,
            'assignmentDepartmentOptions' => $allRecommendationOptions
                ->filter(fn ($engineer) => $engineer->department_id && $engineer->department_name)
                ->map(fn ($engineer) => [
                    'id' => (int) $engineer->department_id,
                    'name' => $engineer->department_name,
                ])
                ->unique('id')
                ->sortBy('name')
                ->values(),
            'assignmentTeamOptions' => $allRecommendationOptions
                ->pluck('team_label')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
        ]);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('assign', $ticket);

        $engineer = User::query()->whereKey($request->validated('assigned_engineer_id'))->firstOrFail();

        $this->ticketService->assign(
            ticket: $ticket,
            assignedEngineer: $engineer,
            actor: $request->user(),
            teamName: $request->validated('assigned_team_name'),
            notes: $request->validated('notes'),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket has been assigned.');
    }

    public function approve(TicketDecisionRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('approve', $ticket);

        $this->ticketService->approve($ticket, $request->user(), $request->validated('notes'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket has been approved.');
    }

    public function reject(TicketDecisionRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('reject', $ticket);

        $this->ticketService->reject($ticket, $request->user(), $request->validated('notes'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket has been rejected.');
    }

    public function markReady(TicketDecisionRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('markReady', $ticket);

        $this->ticketService->markReadyForAssignment($ticket, $request->user(), $request->validated('notes'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Ticket is ready for assignment.');
    }

    public function showAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $ticket);
        abort_unless($attachment->ticket_id === $ticket->id, 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->file_path), 404);

        return Storage::disk($attachment->disk)->response(
            $attachment->file_path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
            'inline'
        );
    }

    private function resolveDefaultPriorityId($priorityOptions = null): ?int
    {
        $priorityOptions ??= TicketPriority::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->get(['id', 'code', 'name']);

        $defaultPriority = $priorityOptions->firstWhere('code', 'P3')
            ?? $priorityOptions->first(fn ($priority) => strcasecmp((string) $priority->name, 'Medium') === 0)
            ?? $priorityOptions->first();

        return $defaultPriority?->id;
    }

    private function filterRecommendation(array $recommendation, array $filters): array
    {
        $departmentId = $filters['department_id'] ?? null;
        $teamLabel = $filters['team_label'] ?? null;

        $applyFilters = function ($engineers) use ($departmentId, $teamLabel) {
            return collect($engineers)
                ->when($departmentId, fn ($collection) => $collection->filter(
                    fn ($engineer) => (int) ($engineer->department_id ?? 0) === (int) $departmentId
                ))
                ->when($teamLabel, fn ($collection) => $collection->filter(
                    fn ($engineer) => strcasecmp((string) ($engineer->team_label ?? ''), (string) $teamLabel) === 0
                ))
                ->values();
        };

        $recommendation['recommended_engineers'] = $applyFilters($recommendation['recommended_engineers'] ?? collect());
        $recommendation['fallback_engineers'] = $applyFilters($recommendation['fallback_engineers'] ?? collect());
        $recommendation['has_recommendation'] = $recommendation['recommended_engineers']->isNotEmpty();

        return $recommendation;
    }
}
