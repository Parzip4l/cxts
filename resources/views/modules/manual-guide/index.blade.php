@extends('layouts.vertical', ['subtitle' => 'Manual Guide'])

@section('content')
@include('layouts.partials.page-title', ['title' => 'Help Center', 'subtitle' => 'Manual Guide'])

@push('styles')
<style>
    .manual-guide-page {
        scroll-behavior: smooth;
    }

    .manual-hero-icon {
        width: 56px;
        height: 56px;
        flex: 0 0 56px;
    }

    .manual-toc-card {
        position: sticky;
        top: 92px;
    }

    .manual-toc-list {
        max-height: calc(100vh - 240px);
        overflow-y: auto;
    }

    .manual-toc-link {
        color: #475569;
        border-radius: 0.75rem;
        padding: 0.55rem 0.7rem;
        transition: background-color 0.16s ease, color 0.16s ease;
    }

    .manual-toc-link:hover,
    .manual-toc-link.active {
        color: #1d4ed8;
        background: rgba(37, 99, 235, 0.08);
    }

    .manual-toc-link.manual-toc-level-2 {
        padding-left: 1rem;
    }

    .manual-guide-section {
        scroll-margin-top: 96px;
    }

    .manual-content h3,
    .manual-content h4,
    .manual-content h5 {
        margin-top: 1.4rem;
        margin-bottom: 0.7rem;
    }

    .manual-content p,
    .manual-content li {
        color: #475569;
        line-height: 1.72;
    }

    .manual-content ul,
    .manual-content ol {
        padding-left: 1.25rem;
        margin-bottom: 1rem;
    }

    .manual-content code {
        color: #0f172a;
        background: #f1f5f9;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 0.45rem;
        padding: 0.1rem 0.35rem;
    }

    .manual-content pre {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 0.8rem;
        padding: 1rem;
        overflow-x: auto;
    }

    .manual-content pre code {
        color: inherit;
        background: transparent;
        border: 0;
        padding: 0;
    }

    .manual-content blockquote {
        border-left: 4px solid rgba(37, 99, 235, 0.25);
        padding-left: 1rem;
        color: #475569;
    }

    @media (max-width: 1199.98px) {
        .manual-toc-card {
            position: static;
        }

        .manual-toc-list {
            max-height: none;
        }
    }
</style>
@endpush

<div class="manual-guide-page">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                <div class="d-flex gap-3">
                    <div class="manual-hero-icon rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                        <iconify-icon icon="solar:book-bookmark-outline" class="fs-32"></iconify-icon>
                    </div>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-primary-subtle text-primary">Internal Guide</span>
                            <span class="badge bg-light text-dark border">{{ count($sections) }} sections</span>
                            @if ($lastUpdated)
                                <span class="badge bg-light text-dark border">Updated {{ $lastUpdated->format('d M Y H:i') }}</span>
                            @endif
                        </div>
                        <h4 class="mb-2">Manual Guide CXTS</h4>
                        <p class="text-muted mb-0">
                            Panduan langkah demi langkah untuk memakai aplikasi CXTS dalam operasional harian.
                        </p>
                    </div>
                </div>

                <div class="align-self-lg-end">
                    <span class="badge bg-light text-dark border">Source: {{ $sourceName }}</span>
                </div>
            </div>
        </div>
    </div>

    @unless ($manualExists)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex gap-3">
                <iconify-icon icon="solar:danger-triangle-outline" class="fs-24"></iconify-icon>
                <div>
                    <h6 class="mb-1">Manual source belum tersedia</h6>
                    <p class="mb-0">Halaman ini sedang menampilkan fallback sementara. Konten manual akan muncul otomatis setelah dokumen sumber tersedia.</p>
                </div>
            </div>
        </div>
    @endunless

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-7">
                    <label for="manualGuideSearch" class="form-label small text-muted mb-1">Search Manual</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <iconify-icon icon="solar:magnifer-outline"></iconify-icon>
                        </span>
                        <input
                            type="search"
                            id="manualGuideSearch"
                            class="form-control"
                            placeholder="Cari cara membuat ticket, approve, assign engineer, inspection, notifikasi, atau profile">
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                        @if ($hasStatusSummary)
                            <span class="badge bg-success-subtle text-success">Implemented: {{ $statusSummary['Implemented'] }}</span>
                            <span class="badge bg-warning-subtle text-warning">Partial: {{ $statusSummary['Partial'] }}</span>
                            <span class="badge bg-info-subtle text-info">Ongoing: {{ $statusSummary['Ongoing'] }}</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary">Step-by-step</span>
                            <span class="badge bg-info-subtle text-info">Role-based guide</span>
                            <span class="badge bg-light text-dark border">Daily operation</span>
                        @endif
                        <button type="button" class="btn btn-outline-light btn-sm" id="manualGuideClearSearch">Reset</button>
                    </div>
                </div>
            </div>
            <p class="text-muted small mb-0 mt-3" id="manualGuideSearchCount">{{ count($sections) }} sections shown</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm manual-toc-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-1">Table of Contents</h5>
                    <p class="text-muted small mb-0">Klik bagian untuk lompat ke panduan.</p>
                </div>
                <div class="card-body">
                    <div class="manual-toc-list d-flex flex-column gap-1">
                        @foreach ($tableOfContents as $item)
                            <a
                                href="#{{ $item['slug'] }}"
                                class="manual-toc-link manual-toc-level-{{ $item['level'] }} text-decoration-none d-flex justify-content-between align-items-center gap-2"
                                data-manual-toc="{{ $item['slug'] }}">
                                <span>{{ $item['title'] }}</span>
                                @if ($item['status'])
                                    <span class="badge bg-{{ $item['status']['class'] }}-subtle text-{{ $item['status']['class'] }}">{{ $item['status']['label'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9">
            <div class="alert alert-light border shadow-sm d-none" id="manualGuideNoResults">
                <div class="d-flex gap-3">
                    <iconify-icon icon="solar:document-medicine-outline" class="fs-24 text-muted"></iconify-icon>
                    <div>
                        <h6 class="mb-1">Tidak ada hasil</h6>
                        <p class="text-muted mb-0">Coba kata kunci lain atau reset pencarian.</p>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column gap-4">
                @foreach ($sections as $section)
                    <section
                        id="{{ $section['slug'] }}"
                        class="card border-0 shadow-sm manual-guide-section"
                        data-manual-section="{{ $section['slug'] }}">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="text-muted small text-uppercase fw-semibold mb-1">Section {{ $loop->iteration }}</div>
                                    <h5 class="mb-0">{{ $section['title'] }}</h5>
                                </div>
                                @if ($section['status'])
                                    <span class="badge bg-{{ $section['status']['class'] }}-subtle text-{{ $section['status']['class'] }}">
                                        {{ $section['status']['label'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body manual-content">
                            {!! $section['html'] !!}
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="text-end mt-4">
                <button type="button" class="btn btn-outline-light" id="manualGuideBackTop">
                    Back to top
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('manualGuideSearch');
        var clearButton = document.getElementById('manualGuideClearSearch');
        var countLabel = document.getElementById('manualGuideSearchCount');
        var noResults = document.getElementById('manualGuideNoResults');
        var backTop = document.getElementById('manualGuideBackTop');
        var sections = Array.prototype.slice.call(document.querySelectorAll('[data-manual-section]'));
        var tocLinks = Array.prototype.slice.call(document.querySelectorAll('[data-manual-toc]'));

        function applySearch() {
            var query = (searchInput.value || '').trim().toLowerCase();
            var visibleCount = 0;

            sections.forEach(function (section) {
                var matches = query === '' || section.innerText.toLowerCase().indexOf(query) !== -1;

                section.classList.toggle('d-none', !matches);

                if (matches) {
                    visibleCount += 1;
                }
            });

            tocLinks.forEach(function (link) {
                var target = document.querySelector('[data-manual-section="' + link.dataset.manualToc + '"]');
                var showLink = !target || !target.classList.contains('d-none');

                link.classList.toggle('d-none', !showLink);
            });

            countLabel.textContent = visibleCount + (visibleCount === 1 ? ' section shown' : ' sections shown');
            noResults.classList.toggle('d-none', visibleCount !== 0);
        }

        tocLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                var target = document.querySelector(link.getAttribute('href'));

                if (!target) {
                    return;
                }

                event.preventDefault();
                tocLinks.forEach(function (item) {
                    item.classList.remove('active');
                });
                link.classList.add('active');
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        searchInput.addEventListener('input', applySearch);

        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            applySearch();
            searchInput.focus();
        });

        backTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
@endpush
@endsection
