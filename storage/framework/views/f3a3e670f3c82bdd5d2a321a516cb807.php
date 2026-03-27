<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Operations', 'subtitle' => 'SLA Performance'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $summary = $data['summary'];
    ?>

    <div class="card">
        <div class="card-body">
            <?php echo $__env->make('modules.dashboard.operations.partials.filter', ['routeName' => 'dashboard.sla-performance'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Response SLA Compliance</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['response']['compliance_rate'], 2)); ?>%</h3>
                    <small class="text-muted">On time <?php echo e(number_format($summary['response']['on_time'])); ?> / Breached <?php echo e(number_format($summary['response']['breached'])); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Resolution SLA Compliance</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['resolution']['compliance_rate'], 2)); ?>%</h3>
                    <small class="text-muted">On time <?php echo e(number_format($summary['resolution']['on_time'])); ?> / Breached <?php echo e(number_format($summary['resolution']['breached'])); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Response Pending</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['response']['pending'])); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-1">Resolution Pending</p>
                    <h3 class="mb-0"><?php echo e(number_format($summary['resolution']['pending'])); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Daily Breach Trend (Chart)</h4>
                </div>
                <div class="card-body">
                    <div id="sla-breach-trend-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Late Minutes by Ticket (Chart)</h4>
                </div>
                <div class="card-body">
                    <div id="sla-late-minutes-chart" class="apex-charts"></div>
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
                    <div id="sla-response-breakdown-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Resolution SLA Breakdown</h4>
                </div>
                <div class="card-body">
                    <div id="sla-resolution-breakdown-chart" class="apex-charts"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Breach Ticket List</h4>
        </div>
        <div class="card-body table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Engineer</th>
                        <th>Status</th>
                        <th>Late Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $data['breach_tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div><?php echo e($ticket['ticket_number']); ?></div>
                                <small class="text-muted"><?php echo e($ticket['title']); ?></small>
                            </td>
                            <td><?php echo e($ticket['assigned_engineer_name'] ?? '-'); ?></td>
                            <td><?php echo e($ticket['status_name'] ?? '-'); ?></td>
                            <td>
                                <?php if($ticket['response_breached']): ?>
                                    <div class="text-warning">Response late <?php echo e(number_format($ticket['response_late_minutes'] ?? 0)); ?> min</div>
                                <?php endif; ?>
                                <?php if($ticket['resolution_breached']): ?>
                                    <div class="text-danger">Resolution late <?php echo e(number_format($ticket['resolution_late_minutes'] ?? 0)); ?> min</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No breached tickets in selected period.</td>
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
            page: 'sla-performance',
            slaPerformance: <?php echo json_encode($data, 15, 512) ?>,
        };
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/operations-charts.js']); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'SLA Performance'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/dashboard/operations/sla-performance.blade.php ENDPATH**/ ?>