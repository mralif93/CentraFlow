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
});

Route::match(['get', 'post'], '/logout', [CentraFlowAuthController::class, 'logout'])->name('logout');

// Admin Management & Governance Center (Protected)
Route::middleware('auth')->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'dashboard'])->name('dashboard');

    // ID Management & Session Control
    Route::get('/id-management', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'index'])->name('id-management');
    Route::post('/id-management/users', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'storeUser'])->name('id-management.store-user');
    Route::put('/id-management/users/{user}', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'updateUser'])->name('id-management.update-user');
    Route::delete('/id-management/users/{user}', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'destroyUser'])->name('id-management.destroy-user');
    
    // Session & Token Invalidation Controls
    Route::post('/id-management/sessions/{sessionId}/revoke', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'revokeSession'])->name('id-management.revoke-session');
    Route::post('/id-management/users/{user}/revoke-sessions', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'revokeUserSessions'])->name('id-management.revoke-user-sessions');
    Route::post('/id-management/tokens/{tokenId}/revoke', [\App\Http\Controllers\Dashboard\IdManagementController::class, 'revokeOAuthToken'])->name('id-management.revoke-oauth-token');
});

// Backward compatibility alias for /dashboard/id-management
Route::middleware('auth')->get('/dashboard/id-management', function () {
    return redirect()->route('admin.id-management');
})->name('id-management.index');
