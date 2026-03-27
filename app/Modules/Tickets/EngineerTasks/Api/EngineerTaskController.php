<?php

namespace App\Modules\Tickets\EngineerTasks\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EngineerScheduleResource;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketWorklogResource;
use App\Models\Ticket;
use App\Modules\MasterData\EngineerSchedules\EngineerScheduleService;
use App\Modules\Tickets\EngineerTasks\EngineerTaskService;
use App\Modules\Tickets\EngineerTasks\Requests\StoreTaskWorklogRequest;
use App\Modules\Tickets\EngineerTasks\Requests\TransitionTaskRequest;
use App\Services\Tickets\EngineerRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngineerTaskController extends Controller
{
    public function __construct(
        private readonly EngineerTaskService $engineerTaskService,
        private readonly EngineerScheduleService $engineerScheduleService,
        private readonly EngineerRecommendationService $engineerRecommendationService,
    ) {
    }

    public function schedules(Request $request)
    {
        $schedules = $this->engineerScheduleService->paginateForEngineer(
            engineer: $request->user(),
            filters: [
                'status' => $request->input('status'),
                'work_date_from' => $request->input('work_date_from'),
                'work_date_to' => $request->input('work_date_to'),
            ],
            perPage: (int) $request->input('per_page', 15),
        );

        return EngineerScheduleResource::collection($schedules);
    }

    public function index(Request $request)
    {
        $tasks = $this->engineerTaskService->paginateMyTasks(
            engineer: $request->user(),
            filters: [
                'search' => $request->input('search'),
                'ticket_status_id' => $request->input('ticket_status_id'),
            ],
            perPage: (int) $request->input('per_page', 15),
        );

        return TicketResource::collection($tasks);
    }

    public function show(Request $request, Ticket $ticket): TicketResource
    {
        $this->engineerTaskService->ensureOwnedByEngineer($ticket, $request->user());

        $ticket->load([
            'category:id,name',
            'subcategory:id,name',
            'detailSubcategory:id,name',
            'priority:id,name',
            'status:id,name,code',
            'service:id,name',
            'asset:id,name',
            'asset.category:id,name',
            'assetLocation:id,name',
            'assignedEngineer:id,name',
            'worklogs.user:id,name',
            'activities.actor:id,name',
            'activities.oldStatus:id,name',
            'activities.newStatus:id,name',
        ]);

        $ticket->setAttribute(
            'engineer_recommendation',
            $this->engineerRecommendationService->serializeRecommendation(
                $this->engineerRecommendationService->recommendForTicket($ticket)
            )
        );

        return new TicketResource($ticket);
    }

    public function start(TransitionTaskRequest $request, Ticket $ticket): TicketResource
    {
        return new TicketResource($this->engineerTaskService->start($ticket, $request->user(), $request->validated('notes')));
    }

    public function pause(TransitionTaskRequest $request, Ticket $ticket): TicketResource
    {
        return new TicketResource($this->engineerTaskService->pause($ticket, $request->user(), $request->validated('notes')));
    }

    public function resume(TransitionTaskRequest $request, Ticket $ticket): TicketResource
    {
        return new TicketResource($this->engineerTaskService->resume($ticket, $request->user(), $request->validated('notes')));
    }

    public function complete(TransitionTaskRequest $request, Ticket $ticket): TicketResource
    {
        return new TicketResource($this->engineerTaskService->complete($ticket, $request->user(), $request->validated('notes')));
    }

    public function storeWorklog(StoreTaskWorklogRequest $request, Ticket $ticket): JsonResponse
    {
        return (new TicketWorklogResource(
            $this->engineerTaskService->addWorklog($ticket, $request->user(), $request->validated())
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function history(Request $request)
    {
        $tasks = $this->engineerTaskService->paginateHistory($request->user(), (int) $request->input('per_page', 15));

        return TicketResource::collection($tasks);
    }
}
