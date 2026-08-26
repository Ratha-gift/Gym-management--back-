<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\MembershipPackageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Token-based (Sanctum) API for the Gym Pro React frontend. Every route
| below is prefixed with /api by RouteServiceProvider.
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [AuthController::class, 'updateProfile']);
    Route::patch('/me/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-members', [DashboardController::class, 'recentMembers']);
    Route::get('/reports/summary', [ReportController::class, 'summary']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::patch('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('members', MemberController::class);
    Route::apiResource('membership-packages', MembershipPackageController::class)
        ->parameters(['membership-packages' => 'membershipPackage']);
    Route::apiResource('memberships', MembershipController::class);
    Route::apiResource('payments', PaymentController::class)->except(['update']);
    Route::patch('payments/{payment}', [PaymentController::class, 'update']);
    Route::apiResource('attendance', AttendanceController::class);
    Route::apiResource('product-categories', ProductCategoryController::class)
        ->parameters(['product-categories' => 'productCategory']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('sales', SaleController::class)->except(['update']);
    Route::apiResource('settings', SettingController::class);
    Route::apiResource('audit-logs', AuditLogController::class)
        ->parameters(['audit-logs' => 'auditLog'])  
        ->only(['index', 'show']);
});
    