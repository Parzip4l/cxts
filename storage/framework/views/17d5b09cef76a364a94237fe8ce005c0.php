<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('layouts.partials.page-title', ['title' => 'Ticketing', 'subtitle' => 'Enterprise Workflow Diagram'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm workflow-hero-card overflow-hidden">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-3">Presentation Ready</span>
                            <h3 class="mb-2">Enterprise Ticketing Workflow</h3>
                            <p class="text-muted mb-0 workflow-hero-copy">
                                Diagram portrait ini mengikuti flow ticketing CXTS dari intake, approval gate, assignment engineer,
                                pause and resume cycle, sampai closure dan performance logging.
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?php echo e(asset('images/diagrams/enterprise-ticketing-workflow.svg')); ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                                Open SVG
                            </a>
                            <a href="<?php echo e(asset('images/diagrams/enterprise-ticketing-workflow.svg')); ?>" download="enterprise-ticketing-workflow.svg" class="btn btn-primary">
                                Download SVG
                            </a>
                        </div>
                    </div>

                    <div class="workflow-diagram-frame">
                        <img
                            src="<?php echo e(asset('images/diagrams/enterprise-ticketing-workflow.svg')); ?>"
                            alt="Enterprise Ticketing Workflow"
                            class="img-fluid workflow-diagram-image"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .workflow-hero-card {
            background:
                radial-gradient(circle at top right, rgba(219, 234, 254, 0.7), transparent 30%),
                radial-gradient(circle at bottom left, rgba(255, 237, 213, 0.7), transparent 26%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .workflow-hero-copy {
            max-width: 760px;
        }

        .workflow-diagram-frame {
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1.4rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.06);
            display: flex;
            justify-content: center;
        }

        .workflow-diagram-image {
            display: block;
            border-radius: 1rem;
            background: #fff;
            width: min(100%, 760px);
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.vertical', ['subtitle' => 'Ticket Workflow Diagram'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/tickets/workflow-diagram.blade.php ENDPATH**/ ?>