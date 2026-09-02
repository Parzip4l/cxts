@extends('layouts.vertical', ['subtitle' => $pageTitle])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Knowledge Base', 'subtitle' => $pageTitle])

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $article->title) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Owner</label>
                    <select name="owner_user_id" class="form-select" data-searchable-select data-search-placeholder="Search owner">
                        <option value="">No owner</option>
                        @foreach ($ownerOptions as $owner)
                            <option value="{{ $owner->id }}" @selected((string) old('owner_user_id', $article->owner_user_id) === (string) $owner->id)>
                                {{ $owner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="article_type" class="form-select" required>
                        @foreach ($typeOptions as $typeCode => $typeLabel)
                            <option value="{{ $typeCode }}" @selected(old('article_type', $article->article_type) === $typeCode)>
                                {{ $typeLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach ($statusOptions as $statusCode => $statusLabel)
                            <option value="{{ $statusCode }}" @selected(old('status', $article->status ?? \App\Models\KnowledgeArticle::STATUS_DRAFT) === $statusCode)>
                                {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $article->category) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Published At</label>
                    <input type="datetime-local" name="published_at" class="form-control"
                        value="{{ old('published_at', optional($article->published_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Summary</label>
                    <textarea name="summary" rows="2" class="form-control">{{ old('summary', $article->summary) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Content</label>
                    <textarea name="content" rows="8" class="form-control" required>{{ old('content', $article->content) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Linked Tickets</label>
                    @php $ticketSelection = collect(old('ticket_ids', $selectedTicketIds ?? []))->map(fn ($id) => (string) $id)->all(); @endphp
                    <select name="ticket_ids[]" class="form-select" multiple data-searchable-select data-search-placeholder="Search ticket">
                        @foreach ($ticketOptions as $ticket)
                            <option value="{{ $ticket->id }}" @selected(in_array((string) $ticket->id, $ticketSelection, true))>
                                {{ $ticket->ticket_number }} - {{ $ticket->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Linked Problems</label>
                    @php $problemSelection = collect(old('problem_ids', $selectedProblemIds ?? []))->map(fn ($id) => (string) $id)->all(); @endphp
                    <select name="problem_ids[]" class="form-select" multiple data-searchable-select data-search-placeholder="Search problem">
                        @foreach ($problemOptions as $problem)
                            <option value="{{ $problem->id }}" @selected(in_array((string) $problem->id, $problemSelection, true))>
                                {{ $problem->problem_number }} - {{ $problem->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('knowledge-articles.index') }}" class="btn btn-outline-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Article</button>
        </div>
    </div>
</form>
@endsection
