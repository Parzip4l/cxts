<?php

namespace App\Modules\Tickets\Problems\Web;

use App\Http\Controllers\Controller;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\User;
use App\Modules\Tickets\Problems\ProblemService;
use App\Modules\Tickets\Problems\Requests\ProblemRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProblemController extends Controller
{
    public function __construct(private readonly ProblemService $problemService)
    {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'owner_user_id' => $request->input('owner_user_id'),
        ];

        if ($request->has('is_known_error') && $request->input('is_known_error') !== '') {
            $filters['is_known_error'] = (bool) $request->input('is_known_error');
        }

        return view('modules.tickets.problems.index', [
            'problems' => $this->problemService->paginate($filters),
            'filters' => $filters,
            'statusOptions' => Problem::statusOptions(),
            'ownerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        $prefillTicket = $request->filled('ticket_id')
            ? Ticket::query()->with(['priority:id,name'])->find($request->integer('ticket_id'))
            : null;

        return view('modules.tickets.problems.form', [
            'problem' => new Problem([
                'title' => $prefillTicket ? 'Problem from '.$prefillTicket->ticket_number : null,
                'description' => $prefillTicket?->description,
                'ticket_priority_id' => $prefillTicket?->ticket_priority_id,
                'symptom' => $prefillTicket?->title,
            ]),
            'selectedTicketIds' => $prefillTicket ? [(string) $prefillTicket->id] : [],
            ...$this->formOptions(),
            'action' => route('problems.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Problem',
        ]);
    }

    public function store(ProblemRequest $request): RedirectResponse
    {
        $problem = $this->problemService->create($request->validated(), $request->user());

        return redirect()
            ->route('problems.show', $problem)
            ->with('success', 'Problem has been created.');
    }

    public function show(Problem $problem): View
    {
        return view('modules.tickets.problems.show', [
            'problem' => $problem
                ->load(['owner:id,name', 'priority:id,name', 'tickets.status:id,name,code', 'tickets.priority:id,name', 'knowledgeArticles'])
                ->loadCount(['tickets', 'knowledgeArticles']),
            'statusOptions' => Problem::statusOptions(),
        ]);
    }

    public function edit(Problem $problem): View
    {
        return view('modules.tickets.problems.form', [
            'problem' => $problem->load('tickets:id'),
            'selectedTicketIds' => $problem->tickets->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ...$this->formOptions(),
            'action' => route('problems.update', $problem),
            'method' => 'PUT',
            'pageTitle' => 'Edit Problem',
        ]);
    }

    public function update(ProblemRequest $request, Problem $problem): RedirectResponse
    {
        $problem = $this->problemService->update($problem, $request->validated(), $request->user());

        return redirect()
            ->route('problems.show', $problem)
            ->with('success', 'Problem has been updated.');
    }

    public function destroy(Problem $problem): RedirectResponse
    {
        $this->problemService->delete($problem);

        return redirect()
            ->route('problems.index')
            ->with('success', 'Problem has been deleted.');
    }

    private function formOptions(): array
    {
        return [
            'statusOptions' => Problem::statusOptions(),
            'ownerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'priorityOptions' => TicketPriority::query()->where('is_active', true)->orderBy('level')->get(['id', 'name']),
            'ticketOptions' => Ticket::query()->orderByDesc('created_at')->limit(100)->get(['id', 'ticket_number', 'title']),
        ];
    }
}
