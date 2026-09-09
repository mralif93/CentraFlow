<?php

use App\Http\Controllers\Auth\CentraFlowAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.home');
})->name('home');

// Public Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [CentraFlowAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CentraFlowAuthController::class, 'login']);

    Route::get('/register', [CentraFlowAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CentraFlowAuthController::class, 'register']);

    Route::get('/forgot-password', [CentraFlowAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [CentraFlowAuthController::class, 'sendResetLink'])->name('password.email');
});

Route::match(['get', 'post'], '/logout', [CentraFlowAuthController::class, 'logout'])->name('logout');

// Authenticated Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
});

// Admin Management & Governance Center (Protected)
Route::middleware('auth')->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'dashboard'])->name('dashboard');

    // Dedicated Users Management
    Route::get('/users', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'users'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/users/{user}/revoke-sessions', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'revokeUserSessions'])->name('users.revoke-sessions');

    // Dedicated Sessions Governance
    Route::get('/sessions', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'sessions'])->name('sessions.index');
    Route::post('/sessions/{sessionId}/revoke', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'revokeSession'])->name('sessions.revoke');
    Route::post('/sessions/tokens/{tokenId}/revoke', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'revokeOAuthToken'])->name('sessions.revoke-oauth-token');

    // Central Activity Audit Trail
    Route::get('/audit-logs', [\App\Http\Controllers\Dashboard\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/export', [\App\Http\Controllers\Dashboard\AuditLogController::class, 'export'])->name('audit-logs.export');
});
