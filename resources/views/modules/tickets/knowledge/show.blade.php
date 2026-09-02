@extends('layouts.vertical', ['subtitle' => 'Knowledge Detail'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Knowledge Base', 'subtitle' => $article->article_number])

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small text-muted fw-semibold mb-1">{{ $article->article_number }}</div>
                <h4 class="mb-2">{{ $article->title }}</h4>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-primary-subtle text-primary">{{ $typeOptions[$article->article_type] ?? $article->article_type }}</span>
                    <span class="badge {{ $article->status === \App\Models\KnowledgeArticle::STATUS_PUBLISHED ? 'bg-success-subtle text-success' : 'bg-light text-dark border' }}">
                        {{ $statusOptions[$article->status] ?? $article->status }}
                    </span>
                    @if ($article->category)
                        <span class="badge bg-light text-dark border">{{ $article->category }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('knowledge-articles.edit', $article) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('knowledge-articles.index') }}" class="btn btn-outline-light">Back</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                @if ($article->summary)
                    <div class="rounded-3 border bg-light-subtle p-3 mb-3">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Summary</div>
                        <div class="fw-semibold">{{ $article->summary }}</div>
                    </div>
                @endif
                <h5 class="card-title mb-3">Content</h5>
                <div class="text-body" style="white-space: pre-line;">{{ $article->content }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Article Info</h5>
                <div class="vstack gap-2">
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Owner</span>
                        <span class="fw-semibold text-end">{{ $article->owner?->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Published</span>
                        <span class="fw-semibold text-end">{{ optional($article->published_at)->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Linked Tickets</span>
                        <span class="fw-semibold text-end">{{ number_format((int) ($article->tickets_count ?? $article->tickets->count())) }}</span>
                    </div>
                    <div class="d-flex justify-content-between gap-3">
                        <span class="text-muted">Linked Problems</span>
                        <span class="fw-semibold text-end">{{ number_format((int) ($article->problems_count ?? $article->problems->count())) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Linked Tickets</h5>
                <div class="vstack gap-2">
                    @forelse ($article->tickets as $ticket)
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold">{{ $ticket->ticket_number }}</div>
                            <div class="small text-muted mb-2">{{ $ticket->title }}</div>
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">Open Ticket</a>
                        </div>
                    @empty
                        <div class="text-muted">No linked ticket.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Linked Problems</h5>
                <div class="vstack gap-2">
                    @forelse ($article->problems as $problem)
                        <div class="border rounded-3 p-3">
                            <div class="fw-semibold">{{ $problem->problem_number }}</div>
                            <div class="small text-muted mb-2">{{ $problem->title }}</div>
                            <a href="{{ route('problems.show', $problem) }}" class="btn btn-sm btn-outline-primary">Open Problem</a>
                        </div>
                    @empty
                        <div class="text-muted">No linked problem.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
