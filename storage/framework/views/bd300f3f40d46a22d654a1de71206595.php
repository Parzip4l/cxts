<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $ticketSummary = $overview['summary'];
        $slaSummary = $overview['sla'];
        $inspectionSummary = $overview['inspection_summary'];
        $reportStructure = $overview['report_structure'];
        $dailyTrend = collect($overview['daily_trend'] ?? []);
        $topEngineers = collect($overview['top_engineers'] ?? []);
        $topEngineer = $topEngineers->first();
        $taxonomyHotspots = collect($reportStructure['taxonomy_breakdown'] ?? [])->take(5);
        $statusDistribution = collect($reportStructure['status_distribution'] ?? [])->take(5);
        $priorityDistribution = collect($reportStructure['priority_distribution'] ?? [])->take(4);
        $criticalAlerts = collect($slaPerformance['breach_tickets'] ?? [])->take(5);
        $responseCompliance = (float) ($slaSummary['response']['compliance_rate'] ?? 0);
        $resolutionCompliance = (float) ($slaSummary['resolution']['compliance_rate'] ?? 0);
        $inspectionNormalityRate = ($inspectionSummary['submitted_inspections'] ?? 0) > 0
            ? (($inspectionSummary['normal_inspections'] ?? 0) / max(1, $inspectionSummary['submitted_inspections'])) * 100
            : 0;
        $totalCreatedInTrend = (int) $dailyTrend->sum('created');
        $totalCompletedInTrend = (int) $dailyTrend->sum('completed');
        $closureCoverage = $totalCreatedInTrend > 0 ? round(($totalCompletedInTrend / max(1, $totalCreatedInTrend)) * 100, 1) : 0;
        $peakDemand = $dailyTrend->sortByDesc('created')->first();
        $avgDemandPerDay = $dailyTrend->count() > 0 ? round($dailyTrend->avg('created') ?? 0, 1) : 0;
        $backlogPressure = max(0, $totalCreatedInTrend - $totalCompletedInTrend);
        $servicePosture = match (true) {
            $responseCompliance >= 88 && $resolutionCompliance >= 82 && $backlogPressure <= 5 && ($inspectionSummary['abnormal_inspections'] ?? 0) <= 2 => 'Stable',
            $responseCompliance >= 72 && $resolutionCompliance >= 68 && $backlogPressure <= 10 => 'Watchlist',
            default => 'Critical Attention',
        };
        $primaryPressure = match (true) {
            ($ticketSummary['overdue_resolution_tickets'] ?? 0) >= max(4, $ticketSummary['unassigned_tickets'] ?? 0) => 'Resolution backlog mulai menekan performa layanan.',
            ($ticketSummary['unassigned_tickets'] ?? 0) >= 4 => 'Queue tanpa owner masih tinggi dan butuh penugasan lebih cepat.',
            ($inspectionSummary['abnormal_inspections'] ?? 0) >= 4 => 'Temuan abnormal inspection meningkat dan berpotensi jadi incident baru.',
            default => 'Demand harian meningkat dan ritme closure perlu dijaga agar backlog tidak melebar.',
        };
        $immediateFocus = match (true) {
            $responseCompliance < 70 => 'Perkuat triage awal dan first response coverage pada 24 jam ke depan.',
            $resolutionCompliance < 70 => 'Prioritaskan recovery untuk ticket overdue dan penyelesaian yang tertahan.',
            $backlogPressure > 8 => 'Dorong closure wave pada ticket yang sudah assigned dan in progress.',
            default => 'Jaga kestabilan throughput sambil menutup temuan inspection yang masih abnormal.',
        };
        $complianceTone = function ($value) {
            if ($value >= 90) {
                return ['badge' => 'bg-success-subtle text-success', 'bar' => 'bg-success', 'label' => 'Healthy'];
            }

            if ($value >= 70) {
                return ['badge' => 'bg-warning-subtle text-warning', 'bar' => 'bg-warning', 'label' => 'Watchlist'];
            }

            return ['badge' => 'bg-danger-subtle text-danger', 'bar' => 'bg-danger', 'label' => 'Needs Action'];
        };
        $responseTone = $complianceTone($responseCompliance);
        $resolutionTone = $complianceTone($resolutionCompliance);
    ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 pb-0">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h5 class="mb-1">Operational Overview</h5>
                    <p class="text-muted mb-0 small">Gunakan filter ini untuk menyelaraskan dashboard, SLA, approval, dan trend chart dalam satu rentang analisis.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary">Executive View</span>
            </div>
        </div>
        <div class="card-body pt-3">
            <?php echo $__env->make('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-premium-card dashboard-premium-card-primary">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-2">Total Tickets</div>
                            <h3 class="mb-1"><?php echo e(number_format($ticketSummary['total_tickets'])); ?></h3>
                            <div class="small text-muted">Open queue: <?php echo e(number_format($ticketSummary['open_tickets'])); ?></div>
                        </div>
                        <span class="avatar-md bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-shell">
                            <iconify-icon icon="solar:ticket-outline" class="fs-28 text-primary"></iconify-icon>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary-subtle text-primary">Operational Demand</span>
                        <span class="small text-muted"><?php echo e($ticketSummary['total_tickets'] > 0 ? number_format(($ticketSummary['open_tickets'] / max(1, $ticketSummary['total_tickets'])) * 100, 0) : 0); ?>% open</span>
                    </div>
                    <div class="progress bg-light" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo e($ticketSummary['total_tickets'] > 0 ? min(100, ($ticketSummary['open_tickets'] / max(1, $ticketSummary['total_tickets'])) * 100) : 0); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-premium-card dashboard-premium-card-warning">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-2">SLA Response</div>
                            <h3 class="mb-1"><?php echo e(number_format($responseCompliance, 2)); ?>%</h3>
                            <div class="small text-muted">Breached: <?php echo e(number_format($slaSummary['response']['breached'])); ?></div>
                        </div>
                        <span class="avatar-md bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-shell">
                            <iconify-icon icon="solar:clock-circle-outline" class="fs-28 text-warning"></iconify-icon>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge <?php echo e($responseTone['badge']); ?>"><?php echo e($responseTone['label']); ?></span>
                        <span class="small text-muted">Target monitor</span>
                    </div>
                    <div class="progress bg-light" style="height: 10px;">
                        <div class="progress-bar <?php echo e($responseTone['bar']); ?>" style="width: <?php echo e(max(0, min(100, $responseCompliance))); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-premium-card dashboard-premium-card-danger">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-2">SLA Resolution</div>
                            <h3 class="mb-1"><?php echo e(number_format($resolutionCompliance, 2)); ?>%</h3>
                            <div class="small text-muted">Breached: <?php echo e(number_format($slaSummary['resolution']['breached'])); ?></div>
                        </div>
                        <span class="avatar-md bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-shell">
                            <iconify-icon icon="solar:shield-warning-outline" class="fs-28 text-danger"></iconify-icon>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge <?php echo e($resolutionTone['badge']); ?>"><?php echo e($resolutionTone['label']); ?></span>
                        <span class="small text-muted">Execution health</span>
                    </div>
                    <div class="progress bg-light" style="height: 10px;">
                        <div class="progress-bar <?php echo e($resolutionTone['bar']); ?>" style="width: <?php echo e(max(0, min(100, $resolutionCompliance))); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-premium-card dashboard-premium-card-info">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <div class="text-muted small text-uppercase fw-semibold mb-2">Inspections Submitted</div>
                            <h3 class="mb-1"><?php echo e(number_format($inspectionSummary['submitted_inspections'])); ?></h3>
                            <div class="small text-muted">Normal findings: <?php echo e(number_format($inspectionSummary['normal_inspections'])); ?></div>
                        </div>
                        <span class="avatar-md bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-shell">
                            <iconify-icon icon="solar:clipboard-check-outline" class="fs-28 text-info"></iconify-icon>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary-subtle text-primary">Normality Rate</span>
                        <span class="small text-muted"><?php echo e(number_format($inspectionNormalityRate, 0)); ?>%</span>
                    </div>
                    <div class="progress bg-light" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo e(max(0, min(100, $inspectionNormalityRate))); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 align-items-start">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm dashboard-section-card">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4 class="card-title mb-1">Ticket Daily Trend</h4>
                            <small class="text-muted">Ringkasan demand harian, closure cadence, dan tekanan backlog dalam satu panel yang lebih mudah dibaca manajemen.</small>
                        </div>
                        <span class="badge bg-white text-dark border">Executive Trend</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-lg-3">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-2">Created In Period</div>
                                <div class="d-flex align-items-end justify-content-between gap-2">
                                    <h4 class="mb-0"><?php echo e(number_format($totalCreatedInTrend)); ?></h4>
                                    <span class="badge bg-primary-subtle text-primary">Demand</span>
                                </div>
                                <div class="small text-muted mt-2">Rata-rata <?php echo e(number_format($avgDemandPerDay, 1)); ?> ticket per hari.</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-2">Completed In Period</div>
                                <div class="d-flex align-items-end justify-content-between gap-2">
                                    <h4 class="mb-0"><?php echo e(number_format($totalCompletedInTrend)); ?></h4>
                                    <span class="badge bg-success-subtle text-success">Closure</span>
                                </div>
                                <div class="small text-muted mt-2">Coverage <?php echo e(number_format($closureCoverage, 1)); ?>% dari demand masuk.</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-2">Peak Demand Day</div>
                                <div class="d-flex align-items-end justify-content-between gap-2">
                                    <h4 class="mb-0"><?php echo e(number_format((int) ($peakDemand['created'] ?? 0))); ?></h4>
                                    <span class="badge bg-warning-subtle text-warning">Peak</span>
                                </div>
                                <div class="small text-muted mt-2">
                                    <?php echo e(isset($peakDemand['date']) ? \Illuminate\Support\Carbon::parse($peakDemand['date'])->translatedFormat('d M Y') : 'Belum ada data'); ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-2">Backlog Pressure</div>
                                <div class="d-flex align-items-end justify-content-between gap-2">
                                    <h4 class="mb-0"><?php echo e(number_format($backlogPressure)); ?></h4>
                                    <span class="badge <?php echo e($backlogPressure > 8 ? 'bg-danger-subtle text-danger' : ($backlogPressure > 3 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success')); ?>">
                                        <?php echo e($backlogPressure > 8 ? 'High' : ($backlogPressure > 3 ? 'Watch' : 'Stable')); ?>

                                    </span>
                                </div>
                                <div class="small text-muted mt-2">Selisih created versus completed pada periode aktif.</div>
                            </div>
                        </div>
                    </div>
                    <div id="ops-daily-trend-chart" class="apex-charts dashboard-chart-compact"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm dashboard-section-card">
                <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <h4 class="card-title mb-1">Service Health Snapshot</h4>
                            <small class="text-muted">Ringkasan kualitas layanan, inspection, dan execution pressure tanpa membuka panel lain.</small>
                        </div>
                        <span class="badge bg-white text-dark border">5-Second Read</span>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-12">
                            <div id="ops-inspection-result-chart" class="apex-charts dashboard-chart-tight"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted text-uppercase fw-semibold small">Response SLA</span>
                                    <span class="badge <?php echo e($responseTone['badge']); ?>"><?php echo e($responseTone['label']); ?></span>
                                </div>
                                <h4 class="mb-2"><?php echo e(number_format($responseCompliance, 2)); ?>%</h4>
                                <div class="progress bg-light mb-2" style="height: 8px;">
                                    <div class="progress-bar <?php echo e($responseTone['bar']); ?>" style="width: <?php echo e(max(0, min(100, $responseCompliance))); ?>%"></div>
                                </div>
                                <div class="small text-muted">Breached <?php echo e(number_format($slaSummary['response']['breached'])); ?> ticket pada periode aktif.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted text-uppercase fw-semibold small">Resolution SLA</span>
                                    <span class="badge <?php echo e($resolutionTone['badge']); ?>"><?php echo e($resolutionTone['label']); ?></span>
                                </div>
                                <h4 class="mb-2"><?php echo e(number_format($resolutionCompliance, 2)); ?>%</h4>
                                <div class="progress bg-light mb-2" style="height: 8px;">
                                    <div class="progress-bar <?php echo e($resolutionTone['bar']); ?>" style="width: <?php echo e(max(0, min(100, $resolutionCompliance))); ?>%"></div>
                                </div>
                                <div class="small text-muted">Overdue <?php echo e(number_format($ticketSummary['overdue_resolution_tickets'])); ?> ticket butuh recovery plan.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-2">Inspection Watch</div>
                                <h4 class="mb-1"><?php echo e(number_format($inspectionSummary['abnormal_inspections'])); ?></h4>
                                <div class="small text-muted">Temuan abnormal dari <?php echo e(number_format($inspectionSummary['submitted_inspections'])); ?> inspection submitted.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-2">Unassigned Queue</div>
                                <h4 class="mb-1"><?php echo e(number_format($ticketSummary['unassigned_tickets'])); ?></h4>
                                <div class="small text-muted">Ticket belum punya owner dan bisa menahan response SLA.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 align-items-start">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm dashboard-section-card">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center dashboard-section-header">
                    <div>
                        <h4 class="card-title mb-1">Operational Hotspots</h4>
                        <small class="text-muted">Top issue clusters yang paling banyak membentuk demand saat ini.</small>
                    </div>
                    <span class="badge bg-white text-dark border"><?php echo e($taxonomyHotspots->count()); ?> hotspots</span>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3 align-items-start">
                        <div class="col-lg-8">
                            <div class="table-responsive dashboard-table-shell">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Cluster</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Open</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $taxonomyHotspots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-medium"><?php echo e($row['ticket_sub_category_name']); ?></div>
                                                    <div class="small text-muted"><?php echo e($row['ticket_type_name']); ?> / <?php echo e($row['ticket_category_name']); ?></div>
                                                </td>
                                                <td class="text-center"><?php echo e(number_format($row['total_tickets'])); ?></td>
                                                <td class="text-center"><?php echo e(number_format($row['open_tickets'])); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">No taxonomy breakdown found for selected filter.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                <div class="text-muted text-uppercase fw-semibold small mb-3">Queue Mix</div>
                                <div class="vstack gap-2">
                                    <?php $__currentLoopData = $statusDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="dashboard-chip-stat rounded-3 px-3 py-2 d-flex justify-content-between align-items-center">
                                            <span class="small fw-medium"><?php echo e($status['status_name']); ?></span>
                                            <span class="badge bg-light text-dark"><?php echo e(number_format($status['total_tickets'])); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <div class="text-muted text-uppercase fw-semibold small mt-4 mb-2">Priority Mix</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $priorityDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-primary-subtle text-primary"><?php echo e($priority['priority_name']); ?>: <?php echo e(number_format($priority['total_tickets'])); ?></span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-12">
            <?php if($isOpsRole): ?>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm dashboard-section-card">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center dashboard-section-header">
                                <div>
                                    <h4 class="card-title mb-1">Engineering Leaders</h4>
                                    <small class="text-muted">Engineer dengan efektivitas terbaik pada periode aktif.</small>
                                </div>
                                <a href="<?php echo e(route('dashboard.engineer-effectiveness', request()->query())); ?>" class="btn btn-sm btn-outline-primary">View Detail</a>
                            </div>
                            <div class="card-body pt-3">
                                <?php if($topEngineer): ?>
                                    <div class="dashboard-mini-panel rounded-3 p-3 mb-3">
                                        <div class="text-muted text-uppercase fw-semibold small mb-2">Top Engineer</div>
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <h5 class="mb-1"><?php echo e($topEngineer['engineer_name']); ?></h5>
                                                <div class="small text-muted"><?php echo e($topEngineer['department_name']); ?></div>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary"><?php echo e(number_format($topEngineer['effectiveness_score'], 1)); ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div id="ops-top-engineer-score-chart" class="apex-charts dashboard-chart-tight-sm"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm dashboard-section-card">
                            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center dashboard-section-header">
                                <div>
                                    <h4 class="card-title mb-1">Critical Alerts</h4>
                                    <small class="text-muted">Item yang perlu keputusan cepat tanpa membuka detail lebih dulu.</small>
                                </div>
                                <a href="<?php echo e(route('dashboard.sla-performance', request()->query())); ?>" class="btn btn-sm btn-outline-primary">View Detail</a>
                            </div>
                            <div class="card-body pt-3">
                                <div class="vstack gap-2">
                                    <?php $__empty_1 = true; $__currentLoopData = $criticalAlerts->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="dashboard-alert-card rounded-3 p-3">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div class="pe-2">
                                                    <div class="fw-medium mb-1"><?php echo e($ticket['ticket_number']); ?></div>
                                                    <div class="small text-muted"><?php echo e($ticket['title']); ?></div>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 justify-content-end flex-shrink-0">
                                                    <?php if($ticket['response_breached']): ?>
                                                        <span class="badge bg-warning-subtle text-warning">Response</span>
                                                    <?php endif; ?>
                                                    <?php if($ticket['resolution_breached']): ?>
                                                        <span class="badge bg-danger-subtle text-danger">Resolution</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="dashboard-alert-card rounded-3 p-4 text-center text-muted">
                                            Tidak ada SLA breach pada periode aktif.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif(auth()->user()?->role === 'engineer' && $myPerformance !== null): ?>
                <div class="card border-0 shadow-sm dashboard-section-card">
                    <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                        <h4 class="card-title mb-1">My Engineering Effectiveness</h4>
                        <small class="text-muted">Ringkasan performa pribadi untuk periode filter yang sedang aktif.</small>
                    </div>
                    <div class="card-body pt-3">
                        <?php if($myPerformance['engineer'] !== null): ?>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                        <p class="text-muted mb-1">Assigned</p>
                                        <h4 class="mb-0"><?php echo e(number_format($myPerformance['engineer']['assigned_tickets'])); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                        <p class="text-muted mb-1">Completed</p>
                                        <h4 class="mb-0"><?php echo e(number_format($myPerformance['engineer']['completed_tickets'])); ?></h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                        <p class="text-muted mb-1">Resolution SLA</p>
                                        <h4 class="mb-0"><?php echo e(number_format($myPerformance['engineer']['resolution_compliance_rate'], 2)); ?>%</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="dashboard-mini-panel rounded-3 p-3 h-100">
                                        <p class="text-muted mb-1">Effectiveness Score</p>
                                        <h4 class="mb-0"><?php echo e(number_format($myPerformance['engineer']['effectiveness_score'], 2)); ?></h4>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No assigned ticket in selected period.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm dashboard-section-card">
                    <div class="card-header bg-transparent border-0 pb-0 dashboard-section-header">
                        <h4 class="card-title mb-1">Queue Snapshot</h4>
                        <small class="text-muted">Ringkasan singkat status dan prioritas operasional untuk pembacaan cepat.</small>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-3">
                            <?php $__currentLoopData = $statusDistribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <div class="dashboard-chip-stat rounded-3 px-3 py-3 d-flex justify-content-between align-items-center">
                                        <span class="small fw-medium"><?php echo e($status['status_name']); ?></span>
                                        <span class="badge bg-light text-dark"><?php echo e(number_format($status['total_tickets'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .dashboard-premium-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .dashboard-premium-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 1rem 2rem rgba(15, 23, 42, 0.08) !important;
        }

        .dashboard-premium-card::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, rgba(255,255,255,0.06), transparent 38%);
        }

        .dashboard-premium-card-primary,
        .dashboard-premium-card-warning,
        .dashboard-premium-card-danger,
        .dashboard-premium-card-info {
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.14);
        }

        .dashboard-premium-card-primary {
            box-shadow: inset 0 3px 0 rgba(37, 99, 235, 0.9);
        }

        .dashboard-premium-card-warning {
            box-shadow: inset 0 3px 0 rgba(14, 116, 144, 0.85);
        }

        .dashboard-premium-card-danger {
            box-shadow: inset 0 3px 0 rgba(30, 64, 175, 0.78);
        }

        .dashboard-premium-card-info {
            box-shadow: inset 0 3px 0 rgba(59, 130, 246, 0.7);
        }

        .dashboard-icon-shell {
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.35);
        }

        .dashboard-section-card {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 1));
        }

        .dashboard-executive-brief {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.96));
            color: #fff;
        }

        .dashboard-executive-brief .text-muted {
            color: rgba(226, 232, 240, 0.76) !important;
        }

        .dashboard-section-header {
            padding-top: 1.4rem !important;
        }

        .dashboard-table-shell {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.92);
        }

        .dashboard-table-shell thead th {
            background: rgba(248, 250, 252, 0.95);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            border-bottom-width: 1px;
        }

        .dashboard-table-shell tbody tr:last-child td {
            border-bottom: 0;
        }

        .dashboard-mini-panel {
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.85), rgba(255, 255, 255, 1));
        }

        .dashboard-chip-stat,
        .dashboard-alert-card {
            border: 1px solid rgba(148, 163, 184, 0.16);
            background: rgba(248, 250, 252, 0.72);
        }

        .dashboard-chart-tight {
            height: 170px;
            min-height: 170px;
        }

        .dashboard-chart-tight-sm {
            height: 160px;
            min-height: 160px;
        }

        .dashboard-chart-compact {
            height: 250px;
            min-height: 250px;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.operationsDashboardPayload = {
            page: 'operations-dashboard',
            overview: <?php echo json_encode($overview, 15, 512) ?>,
            slaPerformance: <?php echo json_encode($slaPerformance, 15, 512) ?>,
            engineerEffectiveness: <?php echo json_encode($engineerEffectiveness, 15, 512) ?>,
        };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/operations-charts.js']); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/dashboard/operations/index.blade.php ENDPATH**/ ?>