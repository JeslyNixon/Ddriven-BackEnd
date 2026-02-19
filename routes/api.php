<?php
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PermissionController;

// Public route - no authentication needed
Route::prefix('auth')->group(function () {
  Route::post('login', [AuthController::class, 'login']);
  Route::get('company-settings', [CompanySettingController::class, 'index']);
});

   Route::prefix('properties')->group(function () {
    Route::post('get-properties', [PropertyController::class, 'index']);
    Route::post('save', [PropertyController::class, 'saveProperties']);
    Route::get('/{id}', [PropertyController::class, 'show']);
    Route::put('/{id}', [PropertyController::class, 'update']);
    Route::post('delete-property', [PropertyController::class, 'destroy']);
    Route::get('/project/{projectId}', [PropertyController::class, 'getByProject']);
    Route::get('/status/{status}', [PropertyController::class, 'getByStatus']);
    Route::post('get-properties-pdf', [PropertyController::class, 'generatePdf']);
    Route::post('approve-property', [PropertyController::class, 'approveProperty']);
    Route::post('bulk-approve-properties', [PropertyController::class, 'bulkApproveProperties']);
    Route::post('export-properties', [PropertyController::class, 'generateSpreadsheet']);
    });
    Route::prefix('users')->group(function () {
    Route::post('get-users', [UserController::class, 'index']);
    Route::post('add-user', [UserController::class, 'store']);
    Route::post('update-user', [UserController::class, 'update']);
    Route::post('get-user', [UserController::class, 'show']);
    Route::post('delete-user', [UserController::class, 'destroy']);
    Route::post('assign-role', [UserController::class, 'assignRole']);
    Route::post('remove-role', [UserController::class, 'removeRole']);
    Route::get('get-user-roles', [UserController::class, 'getUserRoles']);
    Route::get('sync-user-roles', [UserController::class, 'SyncUserRoles']);
    });
    
    Route::get('masters', [MasterController::class, 'index']);

    Route::prefix('permissions')->group(function () {
    Route::post('get-permissions-by-role', [PermissionController::class, 'index']);
    Route::get('get-permissions', [PermissionController::class, 'getPermissions']);
    Route::post('role-permissions-update', [PermissionController::class, 'updateRolePermissions']);
    });

    Route::prefix('roles')->group(function () {
    Route::post('get-roles-all', [RoleController::class, 'index']);
    Route::get('get-roles', [RoleController::class, 'getRoles']);
    Route::post('add-role', [RoleController::class, 'store']);
    Route::post('update-role', [RoleController::class, 'update']);
    Route::post('delete-role', [RoleController::class, 'destroy']);
    });
    Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('login')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    });

});