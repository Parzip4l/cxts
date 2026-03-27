<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $ticketSummary = $overview['summary'];
        $slaSummary = $overview['sla'];
        $inspectionSummary = $overview['inspection_summary'];
        $reportStructure = $overview['report_structure'];
    ?>

    <div class="card">
        <div class="card-body">
            <?php echo $__env->make('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Tickets</p>
                    <h3 class="mb-0"><?php echo e(number_format($ticketSummary['total_tickets'])); ?></h3>
                    <small class="text-muted">Open: <?php echo e(number_format($ticketSummary['open_tickets'])); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">SLA Response</p>
                    <h3 class="mb-0"><?php echo e(number_format($slaSummary['response']['compliance_rate'], 2)); ?>%</h3>
                    <small class="text-danger">Breached: <?php echo e(number_format($slaSummary['response']['breached'])); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">SLA Resolution</p>
                    <h3 class="mb-0"><?php echo e(number_format($slaSummary['resolution']['compliance_rate'], 2)); ?>%</h3>
                    <small class="text-danger">Breached: <?php echo e(number_format($slaSummary['resolution']['breached'])); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Inspections Submitted</p>
                    <h3 class="mb-0"><?php echo e(number_format($inspectionSummary['submitted_inspections'])); ?></h3>
                    <small class="text-muted">Normal: <?php echo e(number_format($inspectionSummary['normal_inspections'])); ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Ticket Daily Trend (Chart)</h4>
                </div>
                <div class="card-body">
                    <div id="ops-daily-trend-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Inspection Normality Rate</h4>
                </div>
                <div class="card-body">
                    <div id="ops-inspection-result-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Response SLA Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="ops-sla-response-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Resolution SLA Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="ops-sla-resolution-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">Taxonomy Breakdown</h4>
                        <small class="text-muted">Enterprise-friendly grouping by Ticket Type, Category, and Sub Category.</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary"><?php echo e(count($reportStructure['taxonomy_breakdown'])); ?> rows</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ticket Type</th>
                                <th>Ticket Category</th>
                                <th>Ticket Sub Category</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Open</th>
                                <th class="text-center">Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $reportStructure['taxonomy_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($row['ticket_type_name']); ?></td>
                                    <td><?php echo e($row['ticket_category_name']); ?></td>
                                    <td><?php echo e($row['ticket_sub_category_name']); ?></td>
                                    <td class="text-center"><?php echo e(number_format($row['total_tickets'])); ?></td>
                                    <td class="text-center"><?php echo e(number_format($row['open_tickets'])); ?></td>
                                    <td class="text-center"><?php echo e(number_format($row['completed_tickets'])); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No taxonomy breakdown found for selected filter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">Reporting Query Structure</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Gunakan dimensi berikut sebagai fondasi query report agar dashboard, SLA, dan trend memakai basis klasifikasi yang sama.</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php $__currentLoopData = $reportStructure['query_dimensions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dimension): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-secondary-subtle text-secondary"><?php echo e(str($dimension)->replace('_', ' ')->title()); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <p class="text-muted mb-2">Recommended group by:</p>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php $__currentLoopData = $reportStructure['recommended_grouping']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-primary-subtle text-primary"><?php echo e(str($group)->replace('_', ' ')->title()); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <h6 class="mb-2">Status Distribution</h6>
                                <?php $__empty_1 = true; $__currentLoopData = $reportStructure['status_distribution']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span><?php echo e($status['status_name']); ?></span>
                                        <span class="badge bg-light text-dark"><?php echo e(number_format($status['total_tickets'])); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-muted mb-0">No status data for selected filter.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <h6 class="mb-2">Priority Distribution</h6>
                                <?php $__empty_1 = true; $__currentLoopData = $reportStructure['priority_distribution']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span><?php echo e($priority['priority_name']); ?></span>
                                        <span class="badge bg-light text-dark"><?php echo e(number_format($priority['total_tickets'])); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="text-muted mb-0">No priority data for selected filter.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($isOpsRole): ?>
        <div class="row">
            <div class="col-xl-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Top Engineer Effectiveness (Chart)</h4>
                        <a href="<?php echo e(route('dashboard.engineer-effectiveness', request()->query())); ?>" class="btn btn-sm btn-outline-primary">View Detail</a>
                    </div>
                    <div class="card-body">
                        <div id="ops-top-engineer-score-chart" class="apex-charts"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">SLA Breach Alerts</h4>
                        <a href="<?php echo e(route('dashboard.sla-performance', request()->query())); ?>" class="btn btn-sm btn-outline-primary">View Detail</a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $slaPerformance['breach_tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div><?php echo e($ticket['ticket_number']); ?></div>
                                            <small class="text-muted"><?php echo e($ticket['title']); ?></small>
                                        </td>
                                        <td>
                                            <?php if($ticket['response_breached']): ?>
                                                <span class="badge bg-warning-subtle text-warning">Response</span>
                                            <?php endif; ?>
                                            <?php if($ticket['resolution_breached']): ?>
                                                <span class="badge bg-danger-subtle text-danger">Resolution</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No SLA breach in selected period.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif(auth()->user()?->role === 'engineer' && $myPerformance !== null): ?>
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">My Engineering Effectiveness</h4>
            </div>
            <div class="card-body">
                <?php if($myPerformance['engineer'] !== null): ?>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Assigned</p>
                            <h4><?php echo e(number_format($myPerformance['engineer']['assigned_tickets'])); ?></h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Completed</p>
                            <h4><?php echo e(number_format($myPerformance['engineer']['completed_tickets'])); ?></h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Resolution SLA</p>
                            <h4><?php echo e(number_format($myPerformance['engineer']['resolution_compliance_rate'], 2)); ?>%</h4>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Effectiveness Score</p>
                            <h4><?php echo e(number_format($myPerformance['engineer']['effectiveness_score'], 2)); ?></h4>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No assigned ticket in selected period.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

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