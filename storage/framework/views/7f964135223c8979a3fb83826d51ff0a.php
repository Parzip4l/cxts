<?php $__env->startSection('body-attribuet'); ?>
class="authentication-bg"
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="row g-0 overflow-hidden rounded-4 shadow-lg bg-white">
                    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white"
                        style="background: linear-gradient(145deg, #0f172a 0%, #1d4ed8 100%);">
                        <div>
                            <div class="mb-4">
                                <img src="/images/logo-light.png" height="30" alt="logo light">
                            </div>
                            <span class="badge bg-white bg-opacity-10 border border-white border-opacity-25 mb-3">Service Operations Platform</span>
                            <h2 class="fw-bold mb-3 text-white">Kelola ticket, approval, SLA, dan engineering execution dalam satu alur.</h2>
                            <p class="text-white text-opacity-75 mb-0">
                                CXTS dirancang untuk operasional harian yang butuh visibilitas cepat, assignment yang rapi, dan jejak audit yang jelas.
                            </p>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="rounded-3 border border-white border-opacity-10 bg-white bg-opacity-10 p-3 h-100">
                                    <div class="small text-white text-opacity-75 mb-1">Coverage</div>
                                    <div class="fw-semibold">Ticketing, Inspection, SLA</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="rounded-3 border border-white border-opacity-10 bg-white bg-opacity-10 p-3 h-100">
                                    <div class="small text-white text-opacity-75 mb-1">Control</div>
                                    <div class="fw-semibold">Approval, Assignment, Audit Trail</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="h-100 p-4 p-lg-5">
                        <div class="text-center">
                            <div class="mx-auto mb-4 text-center auth-logo">
                                <a href="<?php echo e(route('any', 'index')); ?>" class="logo-dark">
                                    <img src="/images/logo-dark.png" height="32" alt="logo dark">
                                </a>

                                <a href="<?php echo e(route('any', 'index')); ?>" class="logo-light">
                                    <img src="/images/logo-light.png" height="28" alt="logo light">
                                </a>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Masuk ke <?php echo e(config('app.name', 'CXTS')); ?></h4>
                            <p class="text-muted mb-0">Platform operasional untuk ticketing, SLA, approval, asset, dan inspection.</p>
                        </div>

                        <?php if($errors->any()): ?>
                            <div class="alert alert-danger mt-3" role="alert">
                                <div class="fw-semibold mb-1">Login failed</div>
                                <ul class="mb-0 ps-3">
                                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($message); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo e(route('login')); ?>" class="mt-4">

                            <?php echo csrf_field(); ?>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email" name="email" value="<?php echo e(old('email', 'superadmin@demo.com')); ?>"
                                    placeholder="Enter your email">
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="password" class="form-label">Password</label>
                                    <a href="<?php echo e(route('second', ['auth', 'password'])); ?>"
                                        class="text-decoration-none small text-muted">Forgot password?</a>
                                </div>
                                <input type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="password" name="password" value="password"
                                    placeholder="Enter your password">
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember-me" name="remember" value="1" <?php if(old('remember')): echo 'checked'; endif; ?>>
                                <label class="form-check-label" for="remember-me">Remember me</label>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-dark btn-lg fw-medium" type="submit">Sign In</button>
                            </div>
                        </form>

                        <div class="rounded-3 border bg-light-subtle mt-4 p-3">
                            <div class="fw-semibold mb-2">Demo Accounts</div>
                            <div class="small text-muted mb-3">Gunakan akun berikut untuk walkthrough role-based demo.</div>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex justify-content-between gap-3"><span>Super Admin</span><code>superadmin@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Ops Admin</span><code>opsadmin@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Supervisor</span><code>supervisor@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Engineer</span><code>engineer1@demo.com / password</code></div>
                                <div class="d-flex justify-content-between gap-3"><span>Requester</span><code>requester@demo.com / password</code></div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <p class="text-center mt-4 text-white text-opacity-50">Environment demo internal GM Tekno.</p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.base', ['subtitle' => 'Sign In'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/auth/signin.blade.php ENDPATH**/ ?>