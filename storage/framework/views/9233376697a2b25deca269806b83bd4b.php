<?php $__env->startSection('body-attribuet'); ?>
class="authentication-bg"
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="account-pages py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center">
                            <div class="mx-auto mb-4 text-center auth-logo">
                                <a href="<?php echo e(route('any', 'index')); ?>" class="logo-dark">
                                    <img src="/images/logo-dark.png" height="32" alt="logo dark">
                                </a>

                                <a href="<?php echo e(route('any', 'index')); ?>" class="logo-light">
                                    <img src="/images/logo-light.png" height="28" alt="logo light">
                                </a>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Sign Up</h3>
                                <p class="text-muted">New to our platform? Sign up now! It only takes a
                                    minute.
                                </p>
                        </div>

                        <form action="<?php echo e(route('any', 'index')); ?>" class="mt-4">
                            <div class="mb-3">
                                <label class="form-label" for="example-name">Name</label>
                                <input type="name" id="example-name" name="example-name" class="form-control"
                                    placeholder="Enter your name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-email">Email</label>
                                <input type="email" id="example-email" name="example-email" class="form-control"
                                    placeholder="Enter your email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="example-password">Password</label>
                                <input type="text" id="example-password" class="form-control"
                                    placeholder="Enter your password">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                    <label class="form-check-label" for="checkbox-signin">I accept Terms
                                        and Condition</label>
                                </div>
                            </div>

                            <div class="mb-1 text-center d-grid">
                                <button class="btn btn-dark btn-lg fw-medium" type="submit">Sign
                                    Up</button>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-4 text-white text-opacity-50">I already have an account
                    <a href="<?php echo e(route('second', ['auth', 'signin'])); ?>"
                        class="text-decoration-none text-white fw-bold">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.base', ['subtitle' => 'Sign Up'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/auth/signup.blade.php ENDPATH**/ ?>