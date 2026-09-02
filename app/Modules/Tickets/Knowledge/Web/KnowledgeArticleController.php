<?php

namespace App\Modules\Tickets\Knowledge\Web;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\Problem;
use App\Models\Ticket;
use App\Models\User;
use App\Modules\Tickets\Knowledge\KnowledgeArticleService;
use App\Modules\Tickets\Knowledge\Requests\KnowledgeArticleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeArticleController extends Controller
{
    public function __construct(private readonly KnowledgeArticleService $knowledgeArticleService)
    {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'article_type' => $request->input('article_type'),
            'status' => $request->input('status'),
            'category' => $request->input('category'),
        ];

        return view('modules.tickets.knowledge.index', [
            'articles' => $this->knowledgeArticleService->paginate($filters),
            'filters' => $filters,
            'typeOptions' => KnowledgeArticle::typeOptions(),
            'statusOptions' => KnowledgeArticle::statusOptions(),
            'categoryOptions' => KnowledgeArticle::query()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ]);
    }

    public function create(Request $request): View
    {
        $prefillTicket = $request->filled('ticket_id')
            ? Ticket::query()->find($request->integer('ticket_id'))
            : null;
        $prefillProblem = $request->filled('problem_id')
            ? Problem::query()->find($request->integer('problem_id'))
            : null;

        return view('modules.tickets.knowledge.form', [
            'article' => new KnowledgeArticle($this->prefillArticle($prefillTicket, $prefillProblem)),
            'selectedTicketIds' => $prefillTicket ? [(string) $prefillTicket->id] : [],
            'selectedProblemIds' => $prefillProblem ? [(string) $prefillProblem->id] : [],
            ...$this->formOptions(),
            'action' => route('knowledge-articles.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Knowledge Article',
        ]);
    }

    public function store(KnowledgeArticleRequest $request): RedirectResponse
    {
        $article = $this->knowledgeArticleService->create($request->validated(), $request->user());

        return redirect()
            ->route('knowledge-articles.show', $article)
            ->with('success', 'Knowledge article has been created.');
    }

    public function show(KnowledgeArticle $knowledgeArticle): View
    {
        return view('modules.tickets.knowledge.show', [
            'article' => $knowledgeArticle
                ->load(['owner:id,name', 'tickets.status:id,name,code', 'tickets.priority:id,name', 'problems'])
                ->loadCount(['tickets', 'problems']),
            'typeOptions' => KnowledgeArticle::typeOptions(),
            'statusOptions' => KnowledgeArticle::statusOptions(),
        ]);
    }

    public function edit(KnowledgeArticle $knowledgeArticle): View
    {
        $knowledgeArticle->load(['tickets:id', 'problems:id']);

        return view('modules.tickets.knowledge.form', [
            'article' => $knowledgeArticle,
            'selectedTicketIds' => $knowledgeArticle->tickets->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'selectedProblemIds' => $knowledgeArticle->problems->pluck('id')->map(fn ($id) => (string) $id)->all(),
            ...$this->formOptions(),
            'action' => route('knowledge-articles.update', $knowledgeArticle),
            'method' => 'PUT',
            'pageTitle' => 'Edit Knowledge Article',
        ]);
    }

    public function update(KnowledgeArticleRequest $request, KnowledgeArticle $knowledgeArticle): RedirectResponse
    {
        $article = $this->knowledgeArticleService->update($knowledgeArticle, $request->validated(), $request->user());

        return redirect()
            ->route('knowledge-articles.show', $article)
            ->with('success', 'Knowledge article has been updated.');
    }

    public function destroy(KnowledgeArticle $knowledgeArticle): RedirectResponse
    {
        $this->knowledgeArticleService->delete($knowledgeArticle);

        return redirect()
            ->route('knowledge-articles.index')
            ->with('success', 'Knowledge article has been deleted.');
    }

    private function formOptions(): array
    {
        return [
            'typeOptions' => KnowledgeArticle::typeOptions(),
            'statusOptions' => KnowledgeArticle::statusOptions(),
            'ownerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'ticketOptions' => Ticket::query()->orderByDesc('created_at')->limit(100)->get(['id', 'ticket_number', 'title']),
            'problemOptions' => Problem::query()->orderByDesc('created_at')->limit(100)->get(['id', 'problem_number', 'title']),
        ];
    }

    private function prefillArticle(?Ticket $ticket, ?Problem $problem): array
    {
        if ($problem !== null) {
            return [
                'title' => 'Known error from '.$problem->problem_number,
                'article_type' => KnowledgeArticle::TYPE_KNOWN_ERROR,
                'category' => 'Problem Management',
                'summary' => $problem->symptom,
                'content' => trim(collect([
                    'Root Cause: '.$problem->root_cause,
                    'Workaround: '.$problem->workaround,
                    'Permanent Fix: '.$problem->permanent_fix,
                ])->filter(fn ($line) => ! str_ends_with($line, ': '))->implode("\n\n")),
            ];
        }

        if ($ticket !== null) {
            return [
                'title' => 'Knowledge from '.$ticket->ticket_number,
                'article_type' => KnowledgeArticle::TYPE_TROUBLESHOOTING,
                'category' => $ticket->processTypeLabel(),
                'summary' => $ticket->title,
                'content' => $ticket->description,
            ];
        }

        return [
            'article_type' => KnowledgeArticle::TYPE_TROUBLESHOOTING,
            'status' => KnowledgeArticle::STATUS_DRAFT,
        ];
    }
}
