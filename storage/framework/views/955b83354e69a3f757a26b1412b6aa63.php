<?php
     $topbarUser = auth()->user();
     $notificationService = $topbarUser ? app(\App\Modules\Notifications\NotificationCenterService::class) : null;
     $topbarNotifications = $topbarUser ? $notificationService->latestForUser($topbarUser, 5) : collect();
     $topbarNotificationCount = $topbarUser ? $notificationService->unreadCountForUser($topbarUser) : 0;
     $topbarRoleLabel = $topbarUser?->roleRef?->name ?? str($topbarUser?->role ?? 'user')->replace('_', ' ')->title();
?>
<header class="app-topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center gap-2">
                    <!-- Menu Toggle Button -->
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu topbar-button">
                              <iconify-icon icon="solar:hamburger-menu-outline"
                                   class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- App Search-->
                    <form class="app-search d-none d-md-block me-auto">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Search tickets, engineer, or service..."
                                   autocomplete="off" value="">
                              <iconify-icon icon="solar:magnifer-outline" class="search-widget-icon"></iconify-icon>
                         </div>
                    </form>
               </div>

               <div class="d-flex align-items-center gap-2">

                    <!-- Theme Color (Light/Dark) -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" id="light-dark-mode">
                              <iconify-icon icon="solar:moon-outline"
                                   class="fs-22 align-middle light-mode"></iconify-icon>
                              <iconify-icon icon="solar:sun-2-outline"
                                   class="fs-22 align-middle dark-mode"></iconify-icon>
                         </button>
                    </div>

                    <!-- Notification -->
                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button position-relative"
                              id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                              aria-expanded="false">
                              <iconify-icon icon="solar:bell-bing-outline" class="fs-22 align-middle"></iconify-icon>
                              <?php if($topbarNotificationCount > 0): ?>
                                   <span
                                        class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill"><?php echo e(min($topbarNotificationCount, 99)); ?><span
                                             class="visually-hidden">unread messages</span></span>
                              <?php endif; ?>
                         </button>
                         <div class="dropdown-menu py-0 dropdown-lg dropdown-menu-end shadow border-0"
                              aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-2 border-bottom bg-light bg-opacity-50">
                                   <div class="row align-items-center g-2">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold">Notifications</h6>
                                             <small class="text-muted"><?php echo e($topbarNotifications->count()); ?> recent updates</small>
                                        </div>
                                        <div class="col-auto">
                                             <span class="badge bg-primary-subtle text-primary"><?php echo e($topbarNotificationCount); ?> unread</span>
                                        </div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 250px;">
                                   <?php $__empty_1 = true; $__currentLoopData = $topbarNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <a href="<?php echo e($notification['url']); ?>" class="dropdown-item p-2 border-bottom text-wrap">
                                             <div class="d-flex">
                                                  <div class="flex-shrink-0">
                                                       <div class="avatar-sm me-2">
                                                            <span class="avatar-title rounded-circle bg-<?php echo e($notification['badge_class']); ?>-subtle text-<?php echo e($notification['badge_class']); ?>">
                                                                 <iconify-icon icon="<?php echo e($notification['icon']); ?>"></iconify-icon>
                                                            </span>
                                                       </div>
                                                  </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 fw-medium"><?php echo e($notification['title']); ?></p>
                                                  <p class="mb-0 text-wrap text-muted"><?php echo e($notification['message']); ?></p>
                                                  <small class="text-muted"><?php echo e($notification['occurred_at']->diffForHumans()); ?></small>
                                             </div>
                                             </div>
                                        </a>
                                   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="p-3 text-center text-muted small">No notifications yet.</div>
                                   <?php endif; ?>
                              </div>
                              <div class="text-center p-2">
                                   <a href="<?php echo e(route('notifications.center')); ?>" class="btn btn-primary btn-sm">View All Notification <i
                                             class="bx bx-right-arrow-alt ms-1"></i></a>
                              </div>
                         </div>
                    </div>

                    <!-- User -->
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button px-2 py-1 rounded-3 border bg-light-subtle d-flex align-items-center gap-2" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                              aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center">
                                   <?php if($topbarUser?->profilePhotoUrl()): ?>
                                        <img class="rounded-circle object-fit-cover" width="32" height="32" src="<?php echo e($topbarUser->profilePhotoUrl()); ?>"
                                             alt="<?php echo e($topbarUser->name); ?>">
                                   <?php else: ?>
                                        <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center fw-bold"
                                             style="width: 32px; height: 32px;">
                                             <?php echo e(collect(explode(' ', trim($topbarUser?->name ?: 'NA')))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'NA'); ?>

                                        </span>
                                   <?php endif; ?>
                              </span>
                              <span class="d-none d-md-flex flex-column align-items-start lh-sm">
                                   <span class="fw-semibold text-dark"><?php echo e($topbarUser?->name ?? 'User'); ?></span>
                                   <span class="small text-muted"><?php echo e($topbarRoleLabel); ?></span>
                              </span>
                              <iconify-icon icon="solar:alt-arrow-down-outline" class="text-muted d-none d-md-inline-flex"></iconify-icon>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end shadow border-0">
                              <!-- item-->
                              <div class="px-3 py-3 border-bottom bg-light bg-opacity-50">
                                   <div class="fw-semibold"><?php echo e($topbarUser?->name ?? 'User'); ?></div>
                                   <div class="small text-muted"><?php echo e($topbarUser?->email ?? '-'); ?></div>
                                   <div class="small text-muted"><?php echo e($topbarRoleLabel); ?></div>
                              </div>

                              <a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>">
                                   <iconify-icon icon="solar:user-outline"
                                        class="align-middle me-2 fs-18"></iconify-icon><span class="align-middle">My Account</span>
                              </a>

                              <a class="dropdown-item" href="<?php echo e(route('notifications.center')); ?>">
                                   <iconify-icon icon="solar:bell-bing-outline"
                                        class="align-middle me-2 fs-18"></iconify-icon><span class="align-middle">Notifications</span>
                              </a>

                              <a class="dropdown-item" href="<?php echo e(route('engineering.index')); ?>">
                                   <iconify-icon icon="solar:users-group-two-rounded-outline"
                                        class="align-middle me-2 fs-18"></iconify-icon><span class="align-middle">Engineering Board</span>
                              </a>
                              <a class="dropdown-item" href="<?php echo e(route('dashboard')); ?>">
                                   <iconify-icon icon="solar:help-outline"
                                        class="align-middle me-2 fs-18"></iconify-icon><span class="align-middle">Operations Dashboard</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <form method="POST" action="<?php echo e(route('logout')); ?>">
                                   <?php echo csrf_field(); ?>
                                   <button type="submit" class="dropdown-item text-danger">
                                        <iconify-icon icon="solar:logout-3-outline"
                                             class="align-middle me-2 fs-18"></iconify-icon><span
                                             class="align-middle">Logout</span>
                                   </button>
                              </form>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/layouts/partials/topbar.blade.php ENDPATH**/ ?>