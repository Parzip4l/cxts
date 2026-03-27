<?php

namespace App\Modules\Tickets\Tickets\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketPriority;
use App\Models\User;
use App\Modules\Tickets\Tickets\Requests\AssignTicketRequest;
use App\Modules\Tickets\Tickets\Requests\StoreTicketRequest;
use App\Modules\Tickets\Tickets\Requests\TicketDecisionRequest;
use App\Modules\Tickets\Tickets\TicketService;
use App\Services\Tickets\EngineerRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly EngineerRecommendationService $engineerRecommendationService,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->ticketService->paginate([
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
        ], (int) $request->input('per_page', 15), $request->user());

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $payload = $request->validated();

        if (! isset($payload['requester_id']) || $payload['requester_id'] === null) {
            $payload['requester_id'] = $request->user()?->id;
        }

        if (! isset($payload['requester_department_id']) || $payload['requester_department_id'] === null) {
            $payload['requester_department_id'] = $request->user()?->department_id;
        }

        $payload['ticket_priority_id'] = $payload['ticket_priority_id'] ?? $this->resolveDefaultPriorityId();
        $payload['source'] = $payload['source'] ?? 'api';
        $payload['impact'] = $payload['impact'] ?? 'medium';
        $payload['urgency'] = $payload['urgency'] ?? 'medium';

        $ticket = $this->ticketService->create($payload, $request->user());

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'requester:id,name',
            'requesterDepartment:id,name',
            'category:id,name',
            'subcategory:id,name',
            'detailSubcategory:id,name',
            'priority:id,name',
            'status:id,name,code',
            'service:id,name',
            'asset:id,name',
            'asset.category:id,name',
            'assetLocation:id,name',
            'inspection:id,inspection_number',
            'assignedEngineer:id,name',
            'expectedApprover:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'assignmentReadyBy:id,name',
            'attachments',
            'attachments.uploadedBy:id,name',
        ]);

        $ticket->setAttribute(
            'engineer_recommendation',
            $this->engineerRecommendationService->serializeRecommendation(
                $this->engineerRecommendationService->recommendForTicket($ticket)
            )
        );

        return new TicketResource($ticket);
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket): TicketResource
    {
        $this->authorize('assign', $ticket);

        $engineer = User::query()->whereKey($request->validated('assigned_engineer_id'))->firstOrFail();

        $updated = $this->ticketService->assign(
            ticket: $ticket,
            assignedEngineer: $engineer,
            actor: $request->user(),
            teamName: $request->validated('assigned_team_name'),
            notes: $request->validated('notes'),
        );

        return new TicketResource($updated);
    }

    public function approve(TicketDecisionRequest $request, Ticket $ticket): TicketResource
    {
        $this->authorize('approve', $ticket);

        return new TicketResource($this->ticketService->approve($ticket, $request->user(), $request->validated('notes')));
    }

    public function reject(TicketDecisionRequest $request, Ticket $ticket): TicketResource
    {
        $this->authorize('reject', $ticket);

        return new TicketResource($this->ticketService->reject($ticket, $request->user(), $request->validated('notes')));
    }

    public function markReady(TicketDecisionRequest $request, Ticket $ticket): TicketResource
    {
        $this->authorize('markReady', $ticket);

        return new TicketResource($this->ticketService->markReadyForAssignment($ticket, $request->user(), $request->validated('notes')));
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted.']);
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

    private function resolveDefaultPriorityId(): ?int
    {
        return TicketPriority::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->value('id');
    }
}
