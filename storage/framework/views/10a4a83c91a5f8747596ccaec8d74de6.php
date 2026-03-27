<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Engineer', 'subtitle' => 'My Performance'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php
        $engineer = $data['engineer'];
        $sla = $data['sla'];
    ?>

    <div class="card">
        <div class="card-body">
            <?php echo $__env->make('modules.dashboard.operations.partials.filter', ['routeName' => 'engineer-performance'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>

    <?php if($engineer !== null): ?>
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Assigned Tickets</p>
                        <h3 class="mb-0"><?php echo e(number_format($engineer['assigned_tickets'])); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Completed Tickets</p>
                        <h3 class="mb-0"><?php echo e(number_format($engineer['completed_tickets'])); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Resolution SLA</p>
                        <h3 class="mb-0"><?php echo e(number_format($engineer['resolution_compliance_rate'], 2)); ?>%</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted mb-1">Effectiveness Score</p>
                        <h3 class="mb-0"><?php echo e(number_format($engineer['effectiveness_score'], 2)); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">My SLA Snapshot</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1">Response Compliance</p>
                        <h5><?php echo e(number_format($sla['response']['compliance_rate'], 2)); ?>%</h5>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1">Resolution Compliance</p>
                        <h5><?php echo e(number_format($sla['resolution']['compliance_rate'], 2)); ?>%</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Recent Assigned Tickets</h4>
            </div>
            <div class="card-body table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $data['recent_tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <div><?php echo e($ticket['ticket_number']); ?></div>
                                    <small class="text-muted"><?php echo e($ticket['title']); ?></small>
                                </td>
                                <td><?php echo e($ticket['status_name'] ?? '-'); ?></td>
                                <td><?php echo e($ticket['priority_name'] ?? '-'); ?></td>
                                <td><?php echo e(optional($ticket['created_at'])->format('Y-m-d H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No recent ticket in selected period.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-0">No engineer activity found in selected period.</p>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'My Performance'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/dashboard/operations/my-performance.blade.php ENDPATH**/ ?>