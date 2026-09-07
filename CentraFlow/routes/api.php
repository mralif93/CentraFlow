<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function () {
    // Identity endpoint for sub-systems (HRMS, Payroll, Invoicing)
    Route::middleware('auth:api')->get('/me', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'staff_id' => $user->staff_id,
                'employee_code' => $user->staff_id,
                'phone' => $user->phone,
                'role' => $user->role,
                'department' => $user->department,
                'job_title' => $user->job_title,
                'status' => $user->status,
                'permissions' => $user->getPermissions(),
                'subsystem_roles' => [
                    'hrms' => $user->getSubsystemRole('hrms'),
                    'payroll' => $user->getSubsystemRole('payroll'),
                    'clinic' => $user->getSubsystemRole('clinic'),
                ],
                'access_control' => $user->getSubsystemAccess(),
                'scopes' => $request->user()->token()?->scopes ?? [],
            ],
        ]);
    });

    // Sub-system verification endpoints protected by Passport Scopes (per SRS FR-CORE-002)
    Route::middleware('scope:hrms:read')->get('/hrms/profile', function (Request $request) {
        return response()->json(['message' => 'Authorized for HRMS access', 'user_uuid' => $request->user()?->uuid]);
    });

    Route::middleware('scope:payroll:run')->get('/payroll/batch-status', function (Request $request) {
        return response()->json(['message' => 'Authorized for Payroll run', 'user_uuid' => $request->user()?->uuid]);
    });

    Route::middleware('scope:invoice:read')->get('/invoicing/overview', function (Request $request) {
        return response()->json(['message' => 'Authorized for Invoicing access', 'user_uuid' => $request->user()?->uuid]);
    });
});
