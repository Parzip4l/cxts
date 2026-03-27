<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'Engineer Effectiveness'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $summary = $data['summary'];
    ?>

    <div class="card">
        <div class="card-body">
            <?php echo $__env->make('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard.engineer-effectiveness'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Engineer Count</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['engineer_count'])); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Assigned Tickets</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['total_assigned_tickets'])); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Completion Rate</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['overall_completion_rate'], 2)); ?>%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Avg Effectiveness Score</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['avg_effectiveness_score'], 2)); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Effectiveness Score by Engineer</h4>
                </div>
                <div class="card-body">
                    <div id="eng-effectiveness-score-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Completed vs Open Ticket</h4>
                </div>
                <div class="card-body">
                    <div id="eng-ticket-outcome-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">SLA Compliance Radar</h4>
                </div>
                <div class="card-body">
                    <div id="eng-sla-compliance-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Response vs Resolution Scatter</h4>
                </div>
                <div class="card-body">
                    <div id="eng-time-scatter-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Engineer Performance Table</h4>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Engineer</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Completion %</th>
                        <th>Response SLA %</th>
                        <th>Resolution SLA %</th>
                        <th>Avg Response (min)</th>
                        <th>Avg Resolution (min)</th>
                        <th>Worklog (min)</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $data['engineers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $engineer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div><?php echo e($engineer['engineer_name']); ?></div>
                                <small class="text-muted"><?php echo e($engineer['department_name'] ?? '-'); ?></small>
                            </td>
                            <td><?php echo e(number_format($engineer['assigned_tickets'])); ?></td>
                            <td><?php echo e(number_format($engineer['completed_tickets'])); ?></td>
                            <td><?php echo e(number_format($engineer['completion_rate'], 2)); ?>%</td>
                            <td><?php echo e(number_format($engineer['response_compliance_rate'], 2)); ?>%</td>
                            <td><?php echo e(number_format($engineer['resolution_compliance_rate'], 2)); ?>%</td>
                            <td><?php echo e($engineer['avg_response_minutes'] !== null ? number_format($engineer['avg_response_minutes'], 2) : '-'); ?></td>
                            <td><?php echo e($engineer['avg_resolution_minutes'] !== null ? number_format($engineer['avg_resolution_minutes'], 2) : '-'); ?></td>
                            <td><?php echo e(number_format($engineer['total_worklog_minutes'])); ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?php echo e(number_format($engineer['effectiveness_score'], 2)); ?></span></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">No engineering effectiveness data in selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        window.operationsDashboardPayload = {
            page: 'engineer-effectiveness',
            engineerEffectiveness: <?php echo json_encode($data, 15, 512) ?>,
        };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/operations-charts.js']); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Engineer Effectiveness'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/dashboard/operations/engineer-effectiveness.blade.php ENDPATH**/ ?>