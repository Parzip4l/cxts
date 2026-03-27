<?php
    $user = auth()->user();
    $canViewOpsDashboard = $user?->hasPermission('dashboard.view_ops') ?? false;
    $canViewOwnPerformance = $user?->hasPermission('dashboard.view_own_performance') ?? false;
    $canManageOrganization = $user?->hasPermission('organization.manage') ?? false;
    $canManageWorkforce = $user?->hasPermission('workforce.manage') ?? false;
    $canManageAssets = $user?->hasPermission('asset.manage') ?? false;
    $canManageTaxonomy = $user?->hasPermission('taxonomy.manage') ?? false;
    $canManageSla = $user?->hasPermission('sla.manage') ?? false;
    $canManageInspectionTemplate = $user?->hasPermission('inspection_template.manage') ?? false;
    $canManageAccess = $user?->hasPermission('access.manage') ?? false;
    $canViewTicketOps = $user?->hasAnyPermission(['ticket.view_all', 'ticket.view_department']) ?? false;
    $canCreateTicketOps = $user?->hasAnyPermission(['ticket.create_any', 'ticket.create_self']) ?? false;
    $canViewEngineerTasks = $user?->hasPermission('engineer_task.view_assigned') ?? false;
    $canViewOwnInspectionTasks = $user?->hasPermission('inspection_task.view_assigned') ?? false;
    $canViewOwnInspectionResults = $user?->hasPermission('inspection_result.view_assigned') ?? false;
    $canViewEngineeringBoard = $user?->hasAnyPermission(['dashboard.view_ops', 'workforce.manage', 'engineer_task.view_assigned']) ?? false;
    $isSuperAdmin = $user?->role === 'super_admin';
    $showConfiguration = $canManageOrganization || $canManageWorkforce || $canManageAssets || $canManageTaxonomy || $canManageSla || $canManageInspectionTemplate || $canManageAccess;
?>

<div class="app-sidebar">
    <div class="logo-box">
        <a href="<?php echo e(route('dashboard')); ?>" class="logo-dark">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
        </a>

        <a href="<?php echo e(route('dashboard')); ?>" class="logo-light">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-light.png" class="logo-lg" alt="logo light">
        </a>
    </div>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title">Main</li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo e(route('dashboard')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:widget-2-outline"></iconify-icon>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo e(route('profile.edit')); ?>">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:user-outline"></iconify-icon>
                    </span>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>
<?php if($canViewTicketOps || $canViewOpsDashboard): ?>
                <li class="menu-title">Operations</li>

                <?php if($canViewTicketOps || $canCreateTicketOps): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarTicketingOps" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarTicketingOps">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:ticket-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Ticket Operations</span>
                    </a>

                    <div class="collapse" id="sidebarTicketingOps">
                        <ul class="nav sub-navbar-nav">
                            <?php if($canViewTicketOps): ?>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('tickets.index')); ?>">Ticket List</a></li>
                            <?php endif; ?>
                            <?php if($canCreateTicketOps): ?>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('tickets.create')); ?>">Create Ticket</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($canViewOpsDashboard): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarOpsMonitoring" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOpsMonitoring">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:chart-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Monitoring & Analytics</span>
                    </a>

                    <div class="collapse" id="sidebarOpsMonitoring">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('dashboard.report')); ?>">Executive Report</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('dashboard.sla-performance')); ?>">SLA Performance</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('dashboard.engineer-effectiveness')); ?>">Engineer Effectiveness</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if($canViewEngineeringBoard): ?>
                <li class="menu-title">Engineering</li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('engineering.index')); ?>">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Engineering Team</span>
                    </a>
                </li>

            <?php endif; ?>

            <?php if($canViewEngineerTasks): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarEngineer" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarEngineer">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:users-group-two-rounded-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Engineer Tasks</span>
                    </a>

                    <div class="collapse" id="sidebarEngineer">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('engineer-tasks.index')); ?>">My Tasks</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('engineer-tasks.history')); ?>">Task History</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('engineer-tasks.schedule')); ?>">My Schedule</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('engineer-performance')); ?>">My Performance</a></li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <?php if($canViewOwnInspectionTasks || $canViewOwnInspectionResults): ?>
                <li class="menu-title">Inspection</li>

                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarInspection" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarInspection">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-check-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Inspection Tasks</span>
                    </a>

                    <div class="collapse" id="sidebarInspection">
                        <ul class="nav sub-navbar-nav">
                            <?php if($canViewOwnInspectionResults): ?>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('inspection-results.index')); ?>">Inspection Results</a></li>
                            <?php endif; ?>
                            <?php if($canViewOwnInspectionTasks): ?>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('inspections.index')); ?>">My Inspection Tasks</a></li>
                            <?php endif; ?>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('inspections.create')); ?>">Schedule Inspection Task</a></li>
                        </ul>
                    </div>
                </li>
            <?php endif; ?>

            <?php if($isSuperAdmin): ?>
                <li class="menu-title">Administration</li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('audit-trail.index')); ?>">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:document-text-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Audit Trail</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if($showConfiguration): ?>
                <li class="menu-title">Configuration</li>

                <?php if($canManageOrganization): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarOrgData" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOrgData">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:buildings-3-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Organization</span>
                    </a>

                    <div class="collapse" id="sidebarOrgData">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.users.index')); ?>">Users</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.departments.index')); ?>">Departments</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.vendors.index')); ?>">Vendors</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.services.index')); ?>">Service Catalog</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($canManageWorkforce): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarWorkforce" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarWorkforce">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:users-group-two-rounded-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Workforce</span>
                    </a>

                    <div class="collapse" id="sidebarWorkforce">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.shifts.index')); ?>">Shifts</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.engineer-skills.index')); ?>">Engineer Skills</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.engineer-schedules.index')); ?>">Engineer Schedules</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($canManageAssets): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAssetData" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAssetData">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:server-2-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Assets & Locations</span>
                    </a>

                    <div class="collapse" id="sidebarAssetData">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.asset-categories.index')); ?>">Asset Categories</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.asset-statuses.index')); ?>">Asset Statuses</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.asset-locations.index')); ?>">Asset Locations</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.assets.index')); ?>">Assets</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($canManageTaxonomy || $canManageSla): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarTicketConfig" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarTicketConfig">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:ticket-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Ticket Setup</span>
                    </a>

                    <div class="collapse" id="sidebarTicketConfig">
                        <ul class="nav sub-navbar-nav">
                            <?php if($canManageTaxonomy): ?>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.ticket-categories.index')); ?>">Ticket Types</a></li>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.ticket-subcategories.index')); ?>">Ticket Categories</a></li>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.ticket-detail-subcategories.index')); ?>">Ticket Sub Categories</a></li>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.ticket-priorities.index')); ?>">Ticket Priorities</a></li>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.ticket-statuses.index')); ?>">Workflow Statuses</a></li>
                            <?php endif; ?>
                            <?php if($canManageSla): ?>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.sla-policies.index')); ?>">SLA Policies</a></li>
                                <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.sla-policy-assignments.index')); ?>">SLA Rules</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($canManageInspectionTemplate): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarInspectionConfig" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarInspectionConfig">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-check-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Inspection Setup</span>
                    </a>

                    <div class="collapse" id="sidebarInspectionConfig">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.inspection-templates.index')); ?>">Inspection Templates</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if($canManageAccess): ?>
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAccessControl" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAccessControl">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:shield-keyhole-outline"></iconify-icon>
                        </span>
                        <span class="nav-text">Access Control</span>
                    </a>

                    <div class="collapse" id="sidebarAccessControl">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.permissions.index')); ?>">Permissions</a></li>
                            <li class="sub-nav-item"><a class="sub-nav-link" href="<?php echo e(route('master-data.role-permissions.index')); ?>">Role Permission Matrix</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
            <?php endif; ?>
            
        </ul>
    </div>
</div>
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>