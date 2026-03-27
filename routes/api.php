<?php

use App\Modules\Api\Auth\AuthTokenController;
use App\Modules\Api\Mobile\MobileNotificationController;
use App\Modules\Dashboards\Operations\Api\EngineerPerformanceController;
use App\Modules\Dashboards\Operations\Api\OperationsDashboardController;
use App\Modules\MasterData\AssetCategories\Api\AssetCategoryController;
use App\Modules\MasterData\AssetLocations\Api\AssetLocationController;
use App\Modules\MasterData\Assets\Api\AssetController;
use App\Modules\MasterData\AssetStatuses\Api\AssetStatusController;
use App\Modules\MasterData\Departments\Api\DepartmentController;
use App\Modules\MasterData\EngineerSchedules\Api\EngineerScheduleController;
use App\Modules\MasterData\Services\Api\ServiceCatalogController;
use App\Modules\MasterData\Shifts\Api\ShiftController;
use App\Modules\MasterData\Users\Api\UserController as MasterUserController;
use App\Modules\MasterData\Vendors\Api\VendorController;
use App\Modules\Inspections\Inspections\Api\MyInspectionController;
use App\Modules\Inspections\Results\Api\InspectionResultController;
use App\Modules\Inspections\InspectionTemplates\Api\InspectionTemplateController;
use App\Modules\Tickets\EngineerTasks\Api\EngineerTaskController;
use App\Modules\Tickets\SlaPolicies\Api\SlaPolicyController;
use App\Modules\Tickets\SlaPolicyAssignments\Api\SlaPolicyAssignmentController;
use App\Modules\Tickets\TicketCategories\Api\TicketCategoryController;
use App\Modules\Tickets\TicketPriorities\Api\TicketPriorityController;
use App\Modules\Tickets\TicketStatuses\Api\TicketStatusController;
use App\Modules\Tickets\TicketDetailSubcategories\Api\TicketDetailSubcategoryController;
use App\Modules\Tickets\TicketSubcategories\Api\TicketSubcategoryController;
use App\Modules\Tickets\Tickets\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/auth/login', [AuthTokenController::class, 'login'])->name('auth.login');

    Route::middleware('api.token')->group(function (): void {
        Route::post('/auth/logout', [AuthTokenController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthTokenController::class, 'me'])->name('auth.me');
        Route::put('/auth/me', [AuthTokenController::class, 'updateMe'])->name('auth.me.update');

        Route::middleware('permission:dashboard.view_ops')->group(function (): void {
            Route::get('/dashboard/overview', [OperationsDashboardController::class, 'overview'])->name('dashboard.overview');
            Route::get('/dashboard/sla-performance', [OperationsDashboardController::class, 'slaPerformance'])->name('dashboard.sla-performance');
            Route::get('/dashboard/engineer-effectiveness', [OperationsDashboardController::class, 'engineerEffectiveness'])->name('dashboard.engineer-effectiveness');
        });

        Route::middleware('permission:organization.manage')->group(function (): void {
            Route::apiResource('departments', DepartmentController::class);
            Route::apiResource('vendors', VendorController::class);
            Route::apiResource('services', ServiceCatalogController::class);
            Route::apiResource('users', MasterUserController::class);
        });

        Route::middleware('permission:workforce.manage')->group(function (): void {
            Route::apiResource('shifts', ShiftController::class);
            Route::apiResource('engineer-schedules', EngineerScheduleController::class);
        });

        Route::middleware('permission:asset.manage')->group(function (): void {
            Route::apiResource('asset-categories', AssetCategoryController::class);
            Route::apiResource('asset-statuses', AssetStatusController::class);
            Route::apiResource('asset-locations', AssetLocationController::class);
            Route::apiResource('assets', AssetController::class);
        });

        Route::middleware('permission:taxonomy.manage')->group(function (): void {
            Route::apiResource('ticket-categories', TicketCategoryController::class);
            Route::apiResource('ticket-subcategories', TicketSubcategoryController::class);
            Route::apiResource('ticket-detail-subcategories', TicketDetailSubcategoryController::class);
            Route::apiResource('ticket-priorities', TicketPriorityController::class);
            Route::apiResource('ticket-statuses', TicketStatusController::class);
        });

        Route::middleware('permission:sla.manage')->group(function (): void {
            Route::apiResource('sla-policies', SlaPolicyController::class);
            Route::apiResource('sla-policy-assignments', SlaPolicyAssignmentController::class);
        });

        Route::middleware('permission:inspection_template.manage')->group(function (): void {
            Route::apiResource('inspection-templates', InspectionTemplateController::class);
        });

        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/approve', [TicketController::class, 'approve'])->name('tickets.approve');
        Route::post('/tickets/{ticket}/reject', [TicketController::class, 'reject'])->name('tickets.reject');
        Route::post('/tickets/{ticket}/mark-ready', [TicketController::class, 'markReady'])->name('tickets.mark-ready');
        Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::get('/tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'showAttachment'])->name('tickets.attachments.show');

        Route::prefix('/mobile')->name('mobile.')->middleware('role:engineer,inspection_officer')->group(function (): void {
            Route::get('/notifications', [MobileNotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/device-token', [MobileNotificationController::class, 'storeDeviceToken'])->name('notifications.device-token.store');
            Route::delete('/notifications/device-token', [MobileNotificationController::class, 'destroyDeviceToken'])->name('notifications.device-token.destroy');
            Route::get('/notifications/firebase-config', [MobileNotificationController::class, 'firebaseConfig'])->name('notifications.firebase-config');
        });

        Route::prefix('/engineer')->name('engineer.')->middleware('permission:engineer_task.view_assigned')->group(function (): void {
            Route::get('/performance', [EngineerPerformanceController::class, 'show'])->name('performance.show');
            Route::get('/tasks', [EngineerTaskController::class, 'index'])->name('tasks.index');
            Route::get('/tasks/history', [EngineerTaskController::class, 'history'])->name('tasks.history');
            Route::get('/schedules', [EngineerTaskController::class, 'schedules'])->name('schedules.index');
            Route::get('/tasks/{ticket}', [EngineerTaskController::class, 'show'])->name('tasks.show');
            Route::post('/tasks/{ticket}/start', [EngineerTaskController::class, 'start'])->name('tasks.start');
            Route::post('/tasks/{ticket}/pause', [EngineerTaskController::class, 'pause'])->name('tasks.pause');
            Route::post('/tasks/{ticket}/resume', [EngineerTaskController::class, 'resume'])->name('tasks.resume');
            Route::post('/tasks/{ticket}/complete', [EngineerTaskController::class, 'complete'])->name('tasks.complete');
            Route::post('/tasks/{ticket}/worklogs', [EngineerTaskController::class, 'storeWorklog'])->name('tasks.worklogs.store');
        });

        Route::prefix('/inspection')->name('inspection.')->middleware('role:inspection_officer,engineer')->group(function (): void {
            Route::get('/templates', [MyInspectionController::class, 'templates'])->name('templates.index');
            Route::get('/assets', [MyInspectionController::class, 'assets'])->name('assets.index');
            Route::get('/asset-locations', [MyInspectionController::class, 'locations'])->name('asset-locations.index');
            Route::get('/my-inspections', [MyInspectionController::class, 'index'])->name('my-inspections.index');
            Route::post('/my-inspections', [MyInspectionController::class, 'store'])->name('my-inspections.store');
            Route::get('/my-inspections/{inspection}', [MyInspectionController::class, 'show'])->name('my-inspections.show');
            Route::post('/my-inspections/{inspection}/items', [MyInspectionController::class, 'updateItems'])->name('my-inspections.items.update');
            Route::post('/my-inspections/{inspection}/submit', [MyInspectionController::class, 'submit'])->name('my-inspections.submit');
            Route::post('/my-inspections/{inspection}/evidences', [MyInspectionController::class, 'storeEvidence'])->name('my-inspections.evidences.store');
        });

        Route::prefix('/inspection')->name('inspection.')->middleware('role:super_admin,operational_admin,supervisor,inspection_officer,engineer')->group(function (): void {
            Route::get('/results', [InspectionResultController::class, 'index'])->name('results.index');
            Route::get('/results/{inspection}', [InspectionResultController::class, 'show'])->name('results.show');
        });
    });
});
