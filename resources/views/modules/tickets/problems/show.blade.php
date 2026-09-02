@extends('layouts.vertical', ['subtitle' => 'Problem Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Problem Management', 'subtitle' => $problem->problem_number])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">{{ $problem->problem_number }}</div>
                <h4 class="mb-2">{{ $problem->title }}</h4>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $statusOptions[$problem->status] ?? $problem->status }}</span>
                    <span class="badge bg-light text-dark border">{{ $problem->priority?->name ?? 'No Priority' }}</span>
                    @if ($problem->is_known_error)
                        <span class="badge bg-warning-subtle text-warning">Known Error</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('knowledge-articles.create', ['problem_id' => $problem->id]) }}" class="btn btn-outline-primary">Create Article</a>
                <a href="{{ route('problems.edit', $problem) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('problems.index') }}" class="btn btn-outline-light">Back</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">RCA</h5>
                <div class="vstack gap-3">
                    <div>
                        <div class="small text-muted mb-1">Symptom</div>
                        <div class="fw-semibold">{{ $problem->symptom ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Root Cause</div>
                        <div class="fw-semibold">{{ $problem->root_cause ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Workaround</div>
                        <div class="fw-semibold">{{ $problem->workaround ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Permanent Fix</div>
                        <div class="fw-semibold">{{ $problem->permanent_fix ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Action Item</div>
                        <div class="fw-semibold">{{ $problem->action_item ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Ownership</h5>
                <div class="vstack gap-2">
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Owner</span>
                        <span class="fw-semibold text-end">{{ $problem->owner?->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Target Resolution</span>
                        <span class="fw-semibold text-end">{{ optional($problem->target_resolution_at)->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Resolved At</span>
                        <span class="fw-semibold text-end">{{ optional($problem->resolved_at)->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Linked Tickets</span>
                        <span class="fw-semibold text-end">{{ number_format((int) ($problem->tickets_count ?? $problem->tickets->count())) }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Knowledge Articles</span>
                        <span class="fw-semibold text-end">{{ number_format((int) ($problem->knowledge_articles_count ?? $problem->knowledgeArticles->count())) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
                    <h5 class="card-title mb-0">Knowledge Articles</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('knowledge-articles.index', ['search' => $problem->title]) }}" class="btn btn-sm btn-outline-secondary">Search Knowledge</a>
                        <a href="{{ route('knowledge-articles.create', ['problem_id' => $problem->id]) }}" class="btn btn-sm btn-outline-primary">Create Article</a>
                    </div>
                </div>
                <div class="row g-2">
                    @forelse ($problem->knowledgeArticles as $article)
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $article->article_number }}</div>
                                        <div class="small text-muted">{{ $article->title }}</div>
                                    </div>
                                    <span class="badge {{ $article->status === \App\Models\KnowledgeArticle::STATUS_PUBLISHED ? 'bg-success-subtle text-success' : 'bg-light text-dark border' }}">
                                        {{ \App\Models\KnowledgeArticle::statusOptions()[$article->status] ?? $article->status }}
                                    </span>
                                </div>
                                @if ($article->summary)
                                    <div class="small text-muted mt-2">{{ $article->summary }}</div>
                                @endif
                                <a href="{{ route('knowledge-articles.show', $article) }}" class="btn btn-sm btn-outline-primary mt-3">Open Article</a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">No knowledge article linked to this problem.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Linked Tickets</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Process</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($problem->tickets as $ticket)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $ticket->ticket_number }}</div>
                                        <div class="small text-muted">{{ $ticket->title }}</div>
                                    </td>
                                    <td>{{ $ticket->processTypeLabel() }}</td>
                                    <td>{{ $ticket->status?->name ?? '-' }}</td>
                                    <td>{{ $ticket->priority?->name ?? '-' }}</td>
                                    <td>{{ optional($ticket->created_at)->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Open Ticket</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No ticket linked to this problem.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
