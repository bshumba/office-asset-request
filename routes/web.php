<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PermissionManagementController as AdminPermissionManagementController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RoleManagementController as AdminRoleManagementController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Admin\AssetAdjustmentController as AdminAssetAdjustmentController;
use App\Http\Controllers\Admin\AssetIssueController as AdminAssetIssueController;
use App\Http\Controllers\Admin\AssetRequestApprovalController as AdminAssetRequestApprovalController;
use App\Http\Controllers\Admin\AssetReturnController as AdminAssetReturnController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Manager\AssetRequestReviewController as ManagerAssetRequestReviewController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\ReportController as ManagerReportController;
use App\Http\Controllers\Staff\AssignedAssetController as StaffAssignedAssetController;
use App\Http\Controllers\Staff\AssetRequestController as StaffAssetRequestController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'active.user'])->group(function (): void {
    Route::get('/dashboard', DashboardRedirectController::class)
        ->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])
        ->name('notifications.open');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:Super Admin')
        ->group(function (): void {
            Route::get('/dashboard', AdminDashboardController::class)
                ->middleware('permission:dashboard.view-admin')
                ->name('dashboard');

            Route::get('/users', [AdminUserManagementController::class, 'index'])
                ->middleware('permission:users.view')
                ->name('users.index');
            Route::get('/users/create', [AdminUserManagementController::class, 'create'])
                ->middleware('permission:users.create')
                ->name('users.create');
            Route::post('/users', [AdminUserManagementController::class, 'store'])
                ->middleware('permission:users.create')
                ->name('users.store');

            Route::get('/roles', [AdminRoleManagementController::class, 'index'])
                ->middleware('permission:roles.view')
                ->name('roles.index');
            Route::get('/roles/create', [AdminRoleManagementController::class, 'create'])
                ->middleware('permission:roles.create')
                ->name('roles.create');
            Route::post('/roles', [AdminRoleManagementController::class, 'store'])
                ->middleware('permission:roles.create')
                ->name('roles.store');
            Route::get('/roles/{role}/edit', [AdminRoleManagementController::class, 'edit'])
                ->middleware('permission:roles.update')
                ->name('roles.edit');
            Route::patch('/roles/{role}', [AdminRoleManagementController::class, 'update'])
                ->middleware('permission:roles.update')
                ->name('roles.update');

            Route::get('/permissions', [AdminPermissionManagementController::class, 'index'])
                ->middleware('permission:permissions.view')
                ->name('permissions.index');
            Route::post('/permissions', [AdminPermissionManagementController::class, 'store'])
                ->middleware('permission:permissions.assign')
                ->name('permissions.store');

            Route::get('/requests', [AdminAssetRequestApprovalController::class, 'index'])
                ->middleware('permission:requests.view-all')
                ->name('requests.index');
            Route::get('/requests/{assetRequest}', [AdminAssetRequestApprovalController::class, 'show'])
                ->middleware(['permission:requests.view-all', 'can:view,assetRequest'])
                ->name('requests.show');
            Route::patch('/requests/{assetRequest}/approve', [AdminAssetRequestApprovalController::class, 'approve'])
                ->middleware(['permission:requests.admin-approve', 'can:adminApprove,assetRequest'])
                ->name('requests.approve');
            Route::patch('/requests/{assetRequest}/reject', [AdminAssetRequestApprovalController::class, 'reject'])
                ->middleware(['permission:requests.reject', 'can:reject,assetRequest'])
                ->name('requests.reject');

            Route::get('/issues', [AdminAssetIssueController::class, 'index'])
                ->middleware('permission:issues.view')
                ->name('issues.index');
            Route::get('/issues/{assetIssue}', [AdminAssetIssueController::class, 'show'])
                ->middleware(['permission:issues.view', 'can:view,assetIssue'])
                ->name('issues.show');
            Route::post('/requests/{assetRequest}/issue', [AdminAssetIssueController::class, 'store'])
                ->middleware(['permission:issues.create', 'can:create,assetRequest'])
                ->name('issues.store');

            Route::post('/issues/{assetIssue}/returns', [AdminAssetReturnController::class, 'store'])
                ->middleware(['permission:returns.create', 'can:createReturn,assetIssue'])
                ->name('returns.store');

            Route::get('/adjustments', [AdminAssetAdjustmentController::class, 'index'])
                ->middleware('permission:assets.adjust-stock')
                ->name('adjustments.index');
            Route::post('/adjustments', [AdminAssetAdjustmentController::class, 'store'])
                ->middleware('permission:assets.adjust-stock')
                ->name('adjustments.store');

            Route::prefix('reports')
                ->name('reports.')
                ->middleware('permission:reports.view')
                ->group(function (): void {
                    Route::get('/stock', [AdminReportController::class, 'stock'])
                        ->name('stock');
                    Route::get('/requests', [AdminReportController::class, 'requests'])
                        ->name('requests');
                    Route::get('/issues', [AdminReportController::class, 'issues'])
                        ->name('issues');
                    Route::get('/low-stock', [AdminReportController::class, 'lowStock'])
                        ->name('low-stock');
                });
        });

    Route::prefix('manager')
        ->name('manager.')
        ->middleware('role:Department Manager')
        ->group(function (): void {
            Route::get('/dashboard', ManagerDashboardController::class)
                ->middleware('permission:dashboard.view-manager')
                ->name('dashboard');

            Route::get('/requests', [ManagerAssetRequestReviewController::class, 'index'])
                ->middleware('permission:requests.view-department')
                ->name('requests.index');
            Route::get('/requests/{assetRequest}', [ManagerAssetRequestReviewController::class, 'show'])
                ->middleware(['permission:requests.view-department', 'can:view,assetRequest'])
                ->name('requests.show');
            Route::patch('/requests/{assetRequest}/approve', [ManagerAssetRequestReviewController::class, 'approve'])
                ->middleware(['permission:requests.manager-approve', 'can:managerApprove,assetRequest'])
                ->name('requests.approve');
            Route::patch('/requests/{assetRequest}/reject', [ManagerAssetRequestReviewController::class, 'reject'])
                ->middleware(['permission:requests.reject', 'can:reject,assetRequest'])
                ->name('requests.reject');

            Route::prefix('reports')
                ->name('reports.')
                ->middleware('permission:reports.view')
                ->group(function (): void {
                    Route::get('/stock', [ManagerReportController::class, 'stock'])
                        ->name('stock');
                    Route::get('/requests', [ManagerReportController::class, 'requests'])
                        ->name('requests');
                    Route::get('/issues', [ManagerReportController::class, 'issues'])
                        ->name('issues');
                    Route::get('/low-stock', [ManagerReportController::class, 'lowStock'])
                        ->name('low-stock');
                });
        });

    Route::prefix('staff')
        ->name('staff.')
        ->middleware('role:Staff')
        ->group(function (): void {
            Route::get('/dashboard', StaffDashboardController::class)
                ->middleware('permission:dashboard.view-staff')
                ->name('dashboard');

            Route::get('/assigned-assets', [StaffAssignedAssetController::class, 'index'])
                ->middleware('permission:issues.view')
                ->name('assigned-assets.index');
            Route::get('/assigned-assets/{assetIssue}', [StaffAssignedAssetController::class, 'show'])
                ->middleware(['permission:issues.view', 'can:view,assetIssue'])
                ->name('assigned-assets.show');

            Route::get('/requests', [StaffAssetRequestController::class, 'index'])
                ->middleware('permission:requests.view-own')
                ->name('requests.index');
            Route::get('/requests/create', [StaffAssetRequestController::class, 'create'])
                ->middleware('permission:requests.create')
                ->name('requests.create');
            Route::post('/requests', [StaffAssetRequestController::class, 'store'])
                ->middleware('permission:requests.create')
                ->name('requests.store');
            Route::get('/requests/{assetRequest}', [StaffAssetRequestController::class, 'show'])
                ->middleware(['permission:requests.view-own', 'can:view,assetRequest'])
                ->name('requests.show');
            Route::patch('/requests/{assetRequest}/cancel', [StaffAssetRequestController::class, 'cancel'])
                ->middleware(['permission:requests.cancel-own', 'can:cancel,assetRequest'])
                ->name('requests.cancel');
        });
});
