<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Executive Report'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $current = $data['current'];
        $comparisons = collect($data['comparisons']);
        $summary = $data['executive_summary'];
        $actionPlan = collect($data['action_plan'] ?? []);
        $topRisks = collect($data['top_risks'] ?? []);
        $topImprovementAreas = collect($data['top_improvement_areas'] ?? []);
        $primaryComparison = $comparisons->firstWhere('key', $data['primary_comparison_key']) ?? $comparisons->first();

        $toneBadgeClass = fn ($tone) => match ($tone) {
            'improved', 'success' => 'bg-success-subtle text-success',
            'declined', 'danger', 'immediate' => 'bg-danger-subtle text-danger',
            'mixed', 'warning', 'high' => 'bg-warning-subtle text-warning',
            'medium' => 'bg-info-subtle text-info',
            'maintain', 'stable' => 'bg-secondary-subtle text-secondary',
            'critical' => 'bg-danger-subtle text-danger',
            'low' => 'bg-secondary-subtle text-secondary',
            default => 'bg-secondary-subtle text-secondary',
        };

        $deltaClass = fn ($direction, $higherIsBetter = true) => match ($direction) {
            'up' => $higherIsBetter ? 'text-success' : 'text-danger',
            'down' => $higherIsBetter ? 'text-danger' : 'text-success',
            default => 'text-muted',
        };

        $deltaIcon = fn ($direction) => match ($direction) {
            'up' => 'solar:arrow-up-outline',
            'down' => 'solar:arrow-down-outline',
            default => 'solar:minus-outline',
        };

        $metrics = [
            [
                'label' => 'Total Tickets',
                'current' => $current['summary']['total_tickets'],
                'key' => 'ticket_volume',
                'format' => fn ($value) => number_format($value),
                'higher_is_better' => false,
            ],
            [
                'label' => 'Completion Rate',
                'current' => $current['derived']['completion_rate'],
                'key' => 'completion_rate',
                'format' => fn ($value) => number_format($value, 2) . '%',
                'higher_is_better' => true,
            ],
            [
                'label' => 'Response SLA',
                'current' => $current['sla']['response']['compliance_rate'],
                'key' => 'response_compliance',
                'format' => fn ($value) => number_format($value, 2) . '%',
                'higher_is_better' => true,
            ],
            [
                'label' => 'Resolution SLA',
                'current' => $current['sla']['resolution']['compliance_rate'],
                'key' => 'resolution_compliance',
                'format' => fn ($value) => number_format($value, 2) . '%',
                'higher_is_better' => true,
            ],
            [
                'label' => 'Engineer Effectiveness',
                'current' => $current['engineer']['avg_effectiveness_score'],
                'key' => 'avg_effectiveness_score',
                'format' => fn ($value) => number_format($value, 2),
                'higher_is_better' => true,
            ],
            [
                'label' => 'Abnormal Inspections',
                'current' => $current['inspection']['abnormal_inspections'],
                'key' => 'abnormal_inspections',
                'format' => fn ($value) => number_format($value),
                'higher_is_better' => false,
            ],
        ];
    ?>

    <div class="report-page">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h5 class="mb-1">Executive Report Filters</h5>
                </div>
                <span class="badge bg-primary-subtle text-primary">Board View</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <?php echo $__env->make('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard.report'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 report-hero-card">
        <div class="card-body p-4">
            <div class="row g-4 align-items-start">
                <div class="col-xl-7">
                    <div class="small text-uppercase fw-semibold text-muted mb-2">Executive Summary Otomatis</div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <h3 class="mb-0"><?php echo e($summary['headline']); ?></h3>
                        <span class="badge <?php echo e($toneBadgeClass($summary['tone'])); ?>"><?php echo e(str($summary['tone'])->title()); ?></span>
                    </div>
                    <p class="text-muted mb-3">
                        Periode aktif: <?php echo e(\Carbon\Carbon::parse($current['period']['date_from'])->format('d M Y')); ?>

                        to <?php echo e(\Carbon\Carbon::parse($current['period']['date_to'])->format('d M Y')); ?>.
                        Benchmark utama:
                        <?php echo e(\Carbon\Carbon::parse($primaryComparison['period']['date_from'])->format('d M Y')); ?>

                        to <?php echo e(\Carbon\Carbon::parse($primaryComparison['period']['date_to'])->format('d M Y')); ?>.
                    </p>
                    <div class="row g-3">
                        <?php $__currentLoopData = $summary['highlights']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $highlight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6">
                                <div class="rounded-3 border bg-white p-3 h-100 report-mini-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="fw-semibold"><?php echo e($highlight['title']); ?></div>
                                        <span class="badge <?php echo e($toneBadgeClass($highlight['tone'])); ?>"><?php echo e(str($highlight['tone'])->title()); ?></span>
                                    </div>
                                    <div class="small text-muted"><?php echo e($highlight['message']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="col-xl-5">
                    <div class="rounded-4 border bg-white p-4 h-100 report-mini-card">
                        <div class="small text-uppercase fw-semibold text-muted mb-3">Snapshot Kualitas Saat Ini</div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Ticket Volume</span>
                                <span class="fw-semibold"><?php echo e(number_format($current['summary']['total_tickets'])); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Completion Rate</span>
                                <span class="fw-semibold"><?php echo e(number_format($current['derived']['completion_rate'], 2)); ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Response SLA</span>
                                <span class="fw-semibold"><?php echo e(number_format($current['sla']['response']['compliance_rate'], 2)); ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Resolution SLA</span>
                                <span class="fw-semibold"><?php echo e(number_format($current['sla']['resolution']['compliance_rate'], 2)); ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Engineer Effectiveness</span>
                                <span class="fw-semibold"><?php echo e(number_format($current['engineer']['avg_effectiveness_score'], 2)); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Abnormal Inspections</span>
                                <span class="fw-semibold"><?php echo e(number_format($current['inspection']['abnormal_inspections'])); ?></span>
                            </div>
                        </div>
                        <div class="small text-muted mt-3 mb-0"><?php echo e($summary['note']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Recommended Action Plan</h4>
                    <small class="text-muted">Prioritas tindakan otomatis yang disusun dari perubahan SLA, demand, inspection, dan efektivitas engineer.</small>
                </div>
                <span class="badge bg-light text-dark border"><?php echo e($actionPlan->count()); ?> aksi</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="row g-3">
                <?php $__currentLoopData = $actionPlan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-xl-6">
                        <div class="rounded-3 border bg-white p-3 h-100 report-mini-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="fw-semibold"><?php echo e($action['title']); ?></div>
                                <span class="badge <?php echo e($toneBadgeClass(strtolower($action['priority']))); ?>"><?php echo e($action['priority']); ?></span>
                            </div>
                            <div class="small text-muted mb-3"><?php echo e($action['message']); ?></div>
                            <div class="d-flex flex-wrap gap-2 small">
                                <span class="badge bg-light text-dark border">Owner: <?php echo e($action['owner']); ?></span>
                                <span class="badge bg-light text-dark border">Target: <?php echo e($action['timeframe']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 dashboard-section-card">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1">Top 5 Risks</h4>
                            <small class="text-muted">Area risiko paling penting yang perlu dimonitor berdasarkan kondisi periode aktif dan baseline utama.</small>
                        </div>
                        <span class="badge bg-light text-dark border"><?php echo e($topRisks->count()); ?> risiko</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        <?php $__currentLoopData = $topRisks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-3 border bg-white p-3 report-mini-card">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="fw-semibold"><?php echo e($risk['title']); ?></div>
                                    <span class="badge <?php echo e($toneBadgeClass(strtolower($risk['severity']))); ?>"><?php echo e($risk['severity']); ?></span>
                                </div>
                                <div class="small text-muted"><?php echo e($risk['message']); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100 dashboard-section-card">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="card-title mb-1">Top 5 Improvement Areas</h4>
                            <small class="text-muted">Area dengan dampak peningkatan paling besar jika ditangani lebih dulu pada siklus operasional berikutnya.</small>
                        </div>
                        <span class="badge bg-light text-dark border"><?php echo e($topImprovementAreas->count()); ?> area</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex flex-column gap-3">
                        <?php $__currentLoopData = $topImprovementAreas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-3 border bg-white p-3 report-mini-card">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="fw-semibold"><?php echo e($area['title']); ?></div>
                                    <span class="badge <?php echo e($toneBadgeClass(strtolower($area['priority']))); ?>"><?php echo e($area['priority']); ?></span>
                                </div>
                                <div class="small text-muted"><?php echo e($area['message']); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm dashboard-section-card h-100">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4 class="card-title mb-1">Perbandingan Benchmark Kualitas</h4>
                            <small class="text-muted">Membandingkan KPI kualitas utama terhadap baseline 7 hari, 1 bulan, dan 1 tahun sebelumnya.</small>
                        </div>
                        <span class="badge bg-light text-dark border">Comparative KPI</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div id="executive-report-quality-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm dashboard-section-card h-100">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4 class="card-title mb-1">Perbandingan Tekanan Operasional</h4>
                            <small class="text-muted">Melihat tekanan operasional lintas baseline dari volume, overdue, unassigned, dan abnormal findings.</small>
                        </div>
                        <span class="badge bg-light text-dark border">Pressure View</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div id="executive-report-pressure-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php $__currentLoopData = $comparisons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comparison): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $ticketDelta = $comparison['delta']['ticket_volume'];
                $responseDelta = $comparison['delta']['response_compliance'];
                $resolutionDelta = $comparison['delta']['resolution_compliance'];
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 comparison-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <div class="small text-uppercase fw-semibold text-muted mb-2"><?php echo e($comparison['label']); ?></div>
                                <div class="fw-semibold">
                                    <?php echo e(\Carbon\Carbon::parse($comparison['period']['date_from'])->format('d M Y')); ?>

                                    - <?php echo e(\Carbon\Carbon::parse($comparison['period']['date_to'])->format('d M Y')); ?>

                                </div>
                            </div>
                            <span class="badge <?php echo e($toneBadgeClass($comparison['status'])); ?>"><?php echo e(str($comparison['status'])->title()); ?></span>
                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Ticket Volume</span>
                                <span class="<?php echo e($deltaClass($ticketDelta['direction'], false)); ?>">
                                    <iconify-icon icon="<?php echo e($deltaIcon($ticketDelta['direction'])); ?>" class="me-1"></iconify-icon>
                                    <?php echo e($ticketDelta['change'] > 0 ? '+' : ''); ?><?php echo e(number_format($ticketDelta['change'])); ?>

                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Response SLA</span>
                                <span class="<?php echo e($deltaClass($responseDelta['direction'])); ?>">
                                    <iconify-icon icon="<?php echo e($deltaIcon($responseDelta['direction'])); ?>" class="me-1"></iconify-icon>
                                    <?php echo e($responseDelta['change'] > 0 ? '+' : ''); ?><?php echo e(number_format($responseDelta['change'], 2)); ?> pt
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Resolution SLA</span>
                                <span class="<?php echo e($deltaClass($resolutionDelta['direction'])); ?>">
                                    <iconify-icon icon="<?php echo e($deltaIcon($resolutionDelta['direction'])); ?>" class="me-1"></iconify-icon>
                                    <?php echo e($resolutionDelta['change'] > 0 ? '+' : ''); ?><?php echo e(number_format($resolutionDelta['change'], 2)); ?> pt
                                </span>
                            </div>
                        </div>
                        <?php if(!empty($comparison['drivers'])): ?>
                            <div class="mt-3 pt-3 border-top">
                                <div class="small text-muted mb-2">Sorotan utama</div>
                                <div class="small"><?php echo e($comparison['drivers'][0]['message']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h4 class="card-title mb-1">Comparative Metric Table</h4>
                    <small class="text-muted">Membandingkan kualitas operasional dan efektivitas terhadap baseline 7 hari, 1 bulan, dan 1 tahun sebelumnya.</small>
                </div>
                <span class="badge bg-light text-dark border"><?php echo e(count($metrics)); ?> metrik</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <div class="table-responsive dashboard-table-shell">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Periode Aktif</th>
                            <?php $__currentLoopData = $comparisons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comparison): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th><?php echo e($comparison['label']); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="fw-semibold"><?php echo e($metric['label']); ?></td>
                                <td><?php echo e($metric['format']($metric['current'])); ?></td>
                                <?php $__currentLoopData = $comparisons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comparison): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $delta = $comparison['delta'][$metric['key']];
                                    ?>
                                    <td>
                                        <div><?php echo e($metric['format']($delta['previous'])); ?></div>
                                        <div class="small <?php echo e($deltaClass($delta['direction'], $metric['higher_is_better'])); ?>">
                                            <iconify-icon icon="<?php echo e($deltaIcon($delta['direction'])); ?>" class="me-1"></iconify-icon>
                                            <?php echo e($delta['change'] > 0 ? '+' : ''); ?><?php echo e($metric['format']($delta['change'])); ?>

                                        </div>
                                    </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php $__currentLoopData = $comparisons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comparison): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100 dashboard-section-card">
                    <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h5 class="mb-1"><?php echo e($comparison['label']); ?></h5>
                                <small class="text-muted">Penjelasan otomatis berdasarkan perubahan metrik aktual pada baseline ini.</small>
                            </div>
                            <span class="badge <?php echo e($toneBadgeClass($comparison['status'])); ?>"><?php echo e(str($comparison['status'])->title()); ?></span>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-flex flex-column gap-3">
                            <?php $__currentLoopData = $comparison['drivers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-3 border bg-light-subtle p-3 report-mini-card">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div class="fw-semibold"><?php echo e($driver['title']); ?></div>
                                        <span class="badge <?php echo e($toneBadgeClass($driver['tone'])); ?>"><?php echo e(str($driver['tone'])->title()); ?></span>
                                    </div>
                                    <div class="small text-muted"><?php echo e($driver['message']); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .report-page .card,
        .report-page .report-hero-card,
        .report-page .comparison-card,
        .report-page .dashboard-section-card {
            background: #fff !important;
        }

        .report-mini-card {
            border-color: rgba(148, 163, 184, 0.18) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
        }

        .report-hero-card .card-body,
        .comparison-card .card-body {
            padding: 1.5rem !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.operationsDashboardPayload = {
            page: 'executive-report',
            executiveReport: <?php echo json_encode($data, 15, 512) ?>,
        };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/operations-charts.js']); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Executive Report'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/dashboard/operations/report.blade.php ENDPATH**/ ?>