<?php

namespace App\Modules\Tickets\EngineerTasks\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Modules\MasterData\EngineerSchedules\EngineerScheduleService;
use App\Modules\Tickets\EngineerTasks\EngineerTaskService;
use App\Modules\Tickets\EngineerTasks\Requests\StoreTaskWorklogRequest;
use App\Modules\Tickets\EngineerTasks\Requests\TransitionTaskRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngineerTaskController extends Controller
{
    public function __construct(
        private readonly EngineerTaskService $engineerTaskService,
        private readonly EngineerScheduleService $engineerScheduleService,
    ) {
    }

    public function schedule(Request $request): View
    {
        $filters = [
            'work_date_from' => $request->input('work_date_from'),
            'work_date_to' => $request->input('work_date_to'),
            'status' => $request->input('status'),
        ];

        return view('modules.tickets.engineer-tasks.schedule', [
            'schedules' => $this->engineerScheduleService->paginateForEngineer($request->user(), $filters),
            'filters' => $filters,
        ]);
    }

    public function index(Request $request): View
    {
        $tasks = $this->engineerTaskService->paginateMyTasks(
            engineer: $request->user(),
            filters: [
                'search' => $request->input('search'),
                'ticket_status_id' => $request->input('ticket_status_id'),
            ],
        );

        return view('modules.tickets.engineer-tasks.index', [
            'tasks' => $tasks,
            'statusOptions' => TicketStatus::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $request->input('search'),
                'ticket_status_id' => $request->input('ticket_status_id'),
            ],
        ]);
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('work', $ticket);
        $this->engineerTaskService->ensureOwnedByEngineer($ticket, $request->user());

        $ticket->load([
            'category:id,name',
            'subcategory:id,name',
            'detailSubcategory:id,name',
            'priority:id,name',
            'status:id,name,code',
            'service:id,name',
            'asset:id,name',
            'assetLocation:id,name',
            'worklogs.user:id,name',
            'activities.actor:id,name',
            'activities.oldStatus:id,name',
            'activities.newStatus:id,name',
        ]);

        return view('modules.tickets.engineer-tasks.show', ['ticket' => $ticket]);
    }

    public function start(TransitionTaskRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);
        $this->engineerTaskService->start($ticket, $request->user(), $request->validated('notes'));

        return back()->with('success', 'Task started.');
    }

    public function pause(TransitionTaskRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);
        $this->engineerTaskService->pause($ticket, $request->user(), $request->validated('notes'));

        return back()->with('success', 'Task paused.');
    }

    public function resume(TransitionTaskRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);
        $this->engineerTaskService->resume($ticket, $request->user(), $request->validated('notes'));

        return back()->with('success', 'Task resumed.');
    }

    public function complete(TransitionTaskRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);
        $this->engineerTaskService->complete($ticket, $request->user(), $request->validated('notes'));

        return back()->with('success', 'Task completed.');
    }

    public function storeWorklog(StoreTaskWorklogRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('addWorklog', $ticket);
        $this->engineerTaskService->addWorklog($ticket, $request->user(), $request->validated());

        return back()->with('success', 'Worklog added.');
    }

    public function history(Request $request): View
    {
        $tasks = $this->engineerTaskService->paginateHistory($request->user());

        return view('modules.tickets.engineer-tasks.history', [
            'tasks' => $tasks,
        ]);
    }
}
