<?php

namespace App\Modules\ManualGuide\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ManualGuideController extends Controller
{
    public function index(): View
    {
        $manualPath = $this->manualPath();
        $manualExists = is_file($manualPath) && is_readable($manualPath);
        $markdown = $manualExists ? (string) file_get_contents($manualPath) : $this->fallbackMarkdown();

        if (trim($markdown) === '') {
            $manualExists = false;
            $markdown = $this->fallbackMarkdown();
        }

        $sections = $this->buildSections($markdown);

        $statusSummary = $this->buildStatusSummary($sections);

        return view('modules.manual-guide.index', [
            'manualExists' => $manualExists,
            'sections' => $sections,
            'tableOfContents' => $this->buildTableOfContents($sections),
            'statusSummary' => $statusSummary,
            'hasStatusSummary' => array_sum($statusSummary) > 0,
            'lastUpdated' => $manualExists ? Carbon::createFromTimestamp((int) filemtime($manualPath)) : null,
            'sourceName' => 'MANUAL_GUIDE_CXTS.md',
        ]);
    }

    protected function manualPath(): string
    {
        return base_path('MANUAL_GUIDE_CXTS.md');
    }

    /**
     * Source manual: MANUAL_GUIDE_CXTS.md. Update that Markdown file to refresh this page.
     */
    private function buildSections(string $markdown): array
    {
        $lines = preg_split('/\R/', str_replace(["\r\n", "\r"], "\n", $markdown)) ?: [];
        $sections = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,2})\s+(.+?)\s*#*\s*$/u', $line, $matches) === 1) {
                if ($current !== null) {
                    $sections[] = $this->prepareSection($current, count($sections));
                }

                $current = [
                    'level' => strlen($matches[1]),
                    'title' => $this->cleanHeadingTitle($matches[2]),
                    'body' => [],
                ];

                continue;
            }

            if ($current === null) {
                $current = [
                    'level' => 1,
                    'title' => 'Manual Guide',
                    'body' => [],
                ];
            }

            $current['body'][] = $line;
        }

        if ($current !== null) {
            $sections[] = $this->prepareSection($current, count($sections));
        }

        return $sections;
    }

    private function prepareSection(array $section, int $index): array
    {
        $bodyMarkdown = trim(implode("\n", $section['body']));
        $status = $this->detectStatus($section['title'], $bodyMarkdown);

        return [
            'level' => $section['level'],
            'title' => $section['title'],
            'slug' => $this->uniqueSlug((string) $section['title'], $index),
            'body_markdown' => $bodyMarkdown,
            'html' => $this->markdownToSafeHtml($bodyMarkdown),
            'status' => $status,
            'search_text' => Str::lower($section['title'] . ' ' . strip_tags($bodyMarkdown)),
        ];
    }

    private function buildTableOfContents(array $sections): array
    {
        return array_map(fn (array $section): array => [
            'title' => $section['title'],
            'slug' => $section['slug'],
            'level' => $section['level'],
            'status' => $section['status'],
        ], $sections);
    }

    private function buildStatusSummary(array $sections): array
    {
        $summary = [
            'Implemented' => 0,
            'Partial' => 0,
            'Ongoing' => 0,
        ];

        foreach ($sections as $section) {
            $label = $section['status']['label'] ?? null;

            if (isset($summary[$label])) {
                $summary[$label]++;
            }
        }

        return $summary;
    }

    private function detectStatus(string $title, string $markdown): ?array
    {
        $statusText = '';

        if (preg_match('/^###\s+Status.*?(?:\R\s*)+[-*]\s+`?([^`\r\n]+)`?/ium', $markdown, $matches) === 1) {
            $statusText = Str::lower($matches[1]);
        } elseif (Str::contains(Str::lower($title), ['ongoing', 'belum lengkap'])) {
            $statusText = 'ongoing';
        }

        if ($statusText === '') {
            return null;
        }

        if (Str::contains($statusText, ['parsial', 'partial'])) {
            return ['label' => 'Partial', 'class' => 'warning'];
        }

        if (Str::contains($statusText, ['ongoing', 'belum'])) {
            return ['label' => 'Ongoing', 'class' => 'info'];
        }

        if (Str::contains($statusText, 'implemented')) {
            return ['label' => 'Implemented', 'class' => 'success'];
        }

        return ['label' => Str::headline($statusText), 'class' => 'secondary'];
    }

    private function markdownToSafeHtml(string $markdown): string
    {
        if (trim($markdown) === '') {
            return '<p class="text-muted mb-0">Belum ada detail untuk bagian ini.</p>';
        }

        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 50,
        ]);
    }

    private function cleanHeadingTitle(string $title): string
    {
        $title = preg_replace('/[`*_]+/u', '', trim($title)) ?: trim($title);

        return trim($title);
    }

    private function uniqueSlug(string $title, int $index): string
    {
        $slug = Str::slug($title);

        if ($slug === '') {
            $slug = 'manual-section';
        }

        return $slug . '-' . ($index + 1);
    }

    private function fallbackMarkdown(): string
    {
        return <<<'MARKDOWN'
# Manual Guide

Manual aplikasi belum tersedia saat ini.

Dokumen sumber manual belum bisa dibaca oleh aplikasi. Silakan hubungi administrator atau tim product untuk memperbarui dokumentasi internal.

## Cara Update Manual

- Update file Markdown manual penggunaan CXTS.
- Refresh halaman Manual Guide setelah file tersedia.
- Konten manual akan ditampilkan otomatis dari dokumen tersebut.
MARKDOWN;
    }
}
