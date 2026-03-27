<?php

use App\Http\Controllers\RoutingController;
use App\Modules\Dashboards\Operations\Web\OperationsDashboardController;
use App\Modules\AuditTrail\Web\AuditTrailController;
use App\Modules\Engineering\Web\EngineeringController;
use App\Modules\MasterData\AssetCategories\Web\AssetCategoryController;
use App\Modules\MasterData\AssetLocations\Web\AssetLocationController;
use App\Modules\MasterData\Assets\Web\AssetController;
use App\Modules\MasterData\AssetStatuses\Web\AssetStatusController;
use App\Modules\MasterData\Departments\Web\DepartmentController;
use App\Modules\MasterData\EngineerSkills\Web\EngineerSkillController;
use App\Modules\MasterData\EngineerSchedules\Web\EngineerScheduleController;
use App\Modules\MasterData\Services\Web\ServiceCatalogController;
use App\Modules\MasterData\Shifts\Web\ShiftController;
use App\Modules\MasterData\Users\Web\UserController as MasterUserController;
use App\Modules\MasterData\Permissions\Web\PermissionController;
use App\Modules\MasterData\RolePermissions\Web\RolePermissionController;
use App\Modules\MasterData\Vendors\Web\VendorController;
use App\Modules\Inspections\Inspections\Web\InspectionController;
use App\Modules\Inspections\Results\Web\InspectionResultController;
use App\Modules\Inspections\PublicAccess\Web\PublicInspectionController;
use App\Modules\Inspections\InspectionTemplates\Web\InspectionTemplateController;
use App\Modules\Notifications\Web\NotificationController;
use App\Modules\Profile\Web\ProfileController;
use App\Modules\Tickets\PublicAccess\Web\PublicTicketController;
use App\Modules\Tickets\EngineerTasks\Web\EngineerTaskController;
use App\Modules\Tickets\SlaPolicies\Web\SlaPolicyController;
use App\Modules\Tickets\SlaPolicyAssignments\Web\SlaPolicyAssignmentController;
use App\Modules\Tickets\TicketCategories\Web\TicketCategoryController;
use App\Modules\Tickets\TicketPriorities\Web\TicketPriorityController;
use App\Modules\Tickets\TicketStatuses\Web\TicketStatusController;
use App\Modules\Tickets\TicketDetailSubcategories\Web\TicketDetailSubcategoryController;
use App\Modules\Tickets\TicketSubcategories\Web\TicketSubcategoryController;
use App\Modules\Tickets\Tickets\Web\TicketController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::prefix('public')->name('public.')->group(function (): void {
    Route::get('tickets/create', [PublicTicketController::class, 'create'])->name('tickets.create');
    Route::post('tickets', [PublicTicketController::class, 'store'])->middleware('audit')->name('tickets.store');

    Route::get('inspections/create', [PublicInspectionController::class, 'create'])->name('inspections.create');
    Route::post('inspections', [PublicInspectionController::class, 'store'])->middleware('audit')->name('inspections.store');
});

Route::group(['prefix' => '/', 'middleware' => 'auth'], function (): void {
    Route::get('', [OperationsDashboardController::class, 'index'])->name('root');
    Route::get('dashboard', [OperationsDashboardController::class, 'index'])->name('dashboard');
    Route::get('engineer-performance', [OperationsDashboardController::class, 'myPerformance'])
        ->name('engineer-performance')
        ->middleware('permission:dashboard.view_own_performance');
    Route::get('engineering', [EngineeringController::class, 'index'])->name('engineering.index');
    Route::get('users/{user}/profile-photo', [MasterUserController::class, 'profilePhoto'])->name('users.profile-photo');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.center');
    Route::get('audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index')->middleware('role:super_admin');

    Route::prefix('dashboard')->name('dashboard.')->middleware('permission:dashboard.view_ops')->group(function (): void {
        Route::get('report', [OperationsDashboardController::class, 'report'])->name('report');
        Route::get('sla-performance', [OperationsDashboardController::class, 'slaPerformance'])->name('sla-performance');
        Route::get('engineer-effectiveness', [OperationsDashboardController::class, 'engineerEffectiveness'])->name('engineer-effectiveness');
    });

    Route::prefix('master-data')->name('master-data.')->middleware('audit')->group(function (): void {
        Route::middleware('permission:organization.manage')->group(function (): void {
            Route::resource('users', MasterUserController::class)->except('show');
            Route::resource('departments', DepartmentController::class)->except('show');
            Route::resource('vendors', VendorController::class)->except('show');
            Route::resource('services', ServiceCatalogController::class)->except('show');
        });

        Route::middleware('permission:workforce.manage')->group(function (): void {
            Route::resource('engineer-skills', EngineerSkillController::class)->except('show');
            Route::resource('shifts', ShiftController::class)->except('show');
            Route::resource('engineer-schedules', EngineerScheduleController::class)->except('show');
        });

        Route::middleware('permission:asset.manage')->group(function (): void {
            Route::resource('asset-categories', AssetCategoryController::class)->except('show');
            Route::resource('asset-statuses', AssetStatusController::class)->except('show');
            Route::resource('asset-locations', AssetLocationController::class)->except('show');
            Route::resource('assets', AssetController::class)->except('show');
        });

        Route::middleware('permission:taxonomy.manage')->group(function (): void {
            Route::resource('ticket-categories', TicketCategoryController::class)->except('show');
            Route::resource('ticket-subcategories', TicketSubcategoryController::class)->except('show');
            Route::resource('ticket-detail-subcategories', TicketDetailSubcategoryController::class)->except('show');
            Route::resource('ticket-priorities', TicketPriorityController::class)->except('show');
            Route::resource('ticket-statuses', TicketStatusController::class)->except('show');
        });

        Route::middleware('permission:sla.manage')->group(function (): void {
            Route::resource('sla-policies', SlaPolicyController::class)->except('show');
            Route::resource('sla-policy-assignments', SlaPolicyAssignmentController::class)->except('show');
        });

        Route::middleware('permission:inspection_template.manage')->group(function (): void {
            Route::resource('inspection-templates', InspectionTemplateController::class)->except('show');
        });

        Route::middleware('permission:access.manage')->group(function (): void {
            Route::resource('permissions', PermissionController::class)->except('show');
            Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
            Route::get('role-permissions/{role}/edit', [RolePermissionController::class, 'edit'])->name('role-permissions.edit');
            Route::put('role-permissions/{role}', [RolePermissionController::class, 'update'])->name('role-permissions.update');
        });
    });

    Route::prefix('tickets')->name('tickets.')->middleware('audit')->group(function (): void {
        Route::get('', [TicketController::class, 'index'])->name('index');
        Route::get('create', [TicketController::class, 'create'])->name('create');
        Route::post('', [TicketController::class, 'store'])->name('store');
        Route::get('{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('{ticket}/approve', [TicketController::class, 'approve'])->name('approve');
        Route::post('{ticket}/reject', [TicketController::class, 'reject'])->name('reject');
        Route::post('{ticket}/mark-ready', [TicketController::class, 'markReady'])->name('mark-ready');
        Route::post('{ticket}/assign', [TicketController::class, 'assign'])->name('assign');
        Route::get('{ticket}/attachments/{attachment}', [TicketController::class, 'showAttachment'])->name('attachments.show');
    });

    Route::prefix('engineer-tasks')->name('engineer-tasks.')->middleware(['permission:engineer_task.view_assigned', 'audit'])->group(function (): void {
        Route::get('', [EngineerTaskController::class, 'index'])->name('index');
        Route::get('history', [EngineerTaskController::class, 'history'])->name('history');
        Route::get('schedule', [EngineerTaskController::class, 'schedule'])->name('schedule');
        Route::get('{ticket}', [EngineerTaskController::class, 'show'])->name('show');
        Route::post('{ticket}/start', [EngineerTaskController::class, 'start'])->name('start');
        Route::post('{ticket}/pause', [EngineerTaskController::class, 'pause'])->name('pause');
        Route::post('{ticket}/resume', [EngineerTaskController::class, 'resume'])->name('resume');
        Route::post('{ticket}/complete', [EngineerTaskController::class, 'complete'])->name('complete');
        Route::post('{ticket}/worklogs', [EngineerTaskController::class, 'storeWorklog'])->name('worklogs.store');
    });

    Route::prefix('inspections')->name('inspections.')->middleware(['role:super_admin,operational_admin,supervisor,inspection_officer,engineer', 'audit'])->group(function (): void {
        Route::get('', [InspectionController::class, 'index'])->name('index');
        Route::get('create', [InspectionController::class, 'create'])->name('create');
        Route::post('', [InspectionController::class, 'store'])->name('store');
        Route::get('{inspection}', [InspectionController::class, 'show'])->name('show');
        Route::post('{inspection}/items', [InspectionController::class, 'updateItems'])->name('items.update');
        Route::post('{inspection}/submit', [InspectionController::class, 'submit'])->name('submit');
        Route::post('{inspection}/evidences', [InspectionController::class, 'storeEvidence'])->name('evidences.store');
    });

    Route::prefix('inspection-results')->name('inspection-results.')->middleware(['role:super_admin,operational_admin,supervisor,inspection_officer', 'audit'])->group(function (): void {
        Route::get('', [InspectionResultController::class, 'index'])->name('index');
        Route::get('{inspection}', [InspectionResultController::class, 'show'])->name('show');
    });

    Route::prefix('profile')->name('profile.')->middleware('audit')->group(function (): void {
        Route::get('', [ProfileController::class, 'edit'])->name('edit');
        Route::put('', [ProfileController::class, 'update'])->name('update');
    });

    Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
    Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
    Route::get('{any}', [RoutingController::class, 'root'])->name('any');
});
