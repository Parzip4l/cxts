@extends('layouts.vertical', ['subtitle' => 'Knowledge Base'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Knowledge Base'])

<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search article, content, category"
                    value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <select name="article_type" class="form-select">
                    <option value="">All type</option>
                    @foreach ($typeOptions as $typeCode => $typeLabel)
                        <option value="{{ $typeCode }}" @selected(($filters['article_type'] ?? null) === $typeCode)>
                            {{ $typeLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All status</option>
                    @foreach ($statusOptions as $statusCode => $statusLabel)
                        <option value="{{ $statusCode }}" @selected(($filters['status'] ?? null) === $statusCode)>
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">All category</option>
                    @foreach ($categoryOptions as $category)
                        <option value="{{ $category }}" @selected(($filters['category'] ?? null) === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-md-end">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
                <a href="{{ route('knowledge-articles.index') }}" class="btn btn-outline-light">Reset</a>
            </div>
            <div class="col-12 text-end">
                <a href="{{ route('knowledge-articles.create') }}" class="btn btn-primary">Create Article</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Owner</th>
                        <th>Links</th>
                        <th>Published</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $article->article_number }}</div>
                                <div class="small text-muted">{{ $article->title }}</div>
                            </td>
                            <td>{{ $typeOptions[$article->article_type] ?? $article->article_type }}</td>
                            <td>
                                <span class="badge {{ $article->status === \App\Models\KnowledgeArticle::STATUS_PUBLISHED ? 'bg-success-subtle text-success' : 'bg-light text-dark border' }}">
                                    {{ $statusOptions[$article->status] ?? $article->status }}
                                </span>
                            </td>
                            <td>{{ $article->category ?: '-' }}</td>
                            <td>{{ $article->owner?->name ?? '-' }}</td>
                            <td>
                                {{ number_format((int) ($article->tickets_count ?? 0)) }} ticket(s),
                                {{ number_format((int) ($article->problems_count ?? 0)) }} problem(s)
                            </td>
                            <td>{{ optional($article->published_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('knowledge-articles.show', $article) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="{{ route('knowledge-articles.edit', $article) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('knowledge-articles.destroy', $article) }}" class="d-inline"
                                    onsubmit="return confirm('Delete this knowledge article?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No knowledge article found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
