<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

class IdManagementController extends Controller
{
    /**
     * Display Admin Overview Dashboard.
     */
    public function dashboard(): View
    {
        $modules = [
            [
                'name' => 'HRMS Module',
                'repo' => 'human-resources-management-system',
                'port' => ':8001',
                'url' => 'http://localhost:8001/login',
                'scope' => 'hrms:read hrms:write',
                'event_role' => 'Publisher (LeaveApproved)',
                'icon' => 'bxs-user-badge',
                'color' => 'blue',
                'client_id' => '9d12a101-0001-4000-8000-000000000001',
            ],
            [
                'name' => 'Payroll Module',
                'repo' => 'payroll-management-system',
                'port' => ':8002',
                'url' => 'http://localhost:8002/login',
                'scope' => 'payroll:run payroll:read',
                'event_role' => 'Ingestor (ClaimApproved)',
                'icon' => 'bx-wallet',
                'color' => 'emerald',
                'client_id' => '9d12a101-0002-4000-8000-000000000002',
            ],
            [
                'name' => 'Clinic Invoicing',
                'repo' => 'clinic-invoice-system',
                'port' => ':8003',
                'url' => 'http://localhost:8003/login',
                'scope' => 'invoice:manage invoice:read',
                'event_role' => 'Ingestor (TimesheetApproved)',
                'icon' => 'bx-receipt',
                'color' => 'purple',
                'client_id' => '9d12a101-0003-4000-8000-000000000003',
            ],
        ];

        // Perform health check on modules
        foreach ($modules as &$mod) {
            $ch = @curl_init($mod['url']);
            if ($ch) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                @curl_exec($ch);
                $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
                @curl_close($ch);
                $mod['online'] = ($code >= 200 && $code < 400);
            } else {
                $mod['online'] = false;
            }
        }

        // Add admin deep action shortcuts
        $modules[0]['shortcuts'] = [
            ['label' => 'Staff Directory', 'url' => 'http://localhost:8001/admin/employees', 'icon' => 'bx-user-check'],
            ['label' => 'Leave Approvals', 'url' => 'http://localhost:8001/admin/leaves', 'icon' => 'bx-calendar-check'],
            ['label' => 'Claims Review', 'url' => 'http://localhost:8001/admin/claims', 'icon' => 'bx-receipt'],
        ];
        $modules[1]['shortcuts'] = [
            ['label' => 'Run Payroll', 'url' => 'http://localhost:8002/admin/payroll/runs', 'icon' => 'bx-calculator'],
            ['label' => 'Payslip Batches', 'url' => 'http://localhost:8002/admin/payroll/payslips', 'icon' => 'bx-file'],
            ['label' => 'Statutory PCB/EPF', 'url' => 'http://localhost:8002/admin/reports/statutory', 'icon' => 'bx-pie-chart-alt-2'],
        ];
        $modules[2]['shortcuts'] = [
            ['label' => 'Panel Invoices', 'url' => 'http://localhost:8003/admin/invoices', 'icon' => 'bx-receipt'],
            ['label' => 'Treatment Tariffs', 'url' => 'http://localhost:8003/admin/services', 'icon' => 'bx-plus-medical'],
            ['label' => 'Receivables Ledger', 'url' => 'http://localhost:8003/admin/reports/ar', 'icon' => 'bx-dollar-circle'],
        ];

        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentTokens = Token::with('client')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentAuditLogs = AuditLog::orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', [
            'userCount' => User::count(),
            'sessionCount' => DB::table('sessions')->count(),
            'clientCount' => Client::count(),
            'oauthClients' => Client::orderBy('created_at', 'desc')->get(),
            'modules' => $modules,
            'recentUsers' => $recentUsers,
            'recentTokens' => $recentTokens,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }



    /**
     * Display Users Management Directory.
     */
    public function users(Request $request): View
    {
        $users = User::with('roles.permissions')->orderBy('created_at', 'desc')->paginate(10);
        $roles = \App\Models\Role::with('permissions')->orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    /**
     * Display Active Sessions Governance.
     */
    public function sessions(Request $request): View
    {
        $oauthClients = Client::orderBy('created_at', 'desc')->get();

        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id as session_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'sessions.user_id',
                'users.name as user_name',
                'users.email as user_email',
                'users.role as user_role',
                'users.uuid as user_uuid'
            )
            ->orderBy('sessions.last_activity', 'desc')
            ->get();

        $oauthTokens = Token::with('client')
            ->where('revoked', false)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.sessions.index', [
            'userCount' => User::count(),
            'oauthClients' => $oauthClients,
            'activeSessions' => $activeSessions,
            'oauthTokens' => $oauthTokens,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }



    /**
     * Create a new user identity.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(['superadmin', 'hr_manager', 'payroll_officer', 'finance_officer', 'employee'])],
            'staff_id' => ['nullable', 'string', 'max:50'],
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:users,employee_code'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,suspended,resigned,inactive'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
            'hrms_access' => ['nullable', 'boolean'],
            'hrms_role' => ['nullable', 'string', 'max:50'],
            'payroll_access' => ['nullable', 'boolean'],
            'payroll_role' => ['nullable', 'string', 'max:50'],
            'clinic_access' => ['nullable', 'boolean'],
            'clinic_role' => ['nullable', 'string', 'max:50'],
        ]);

        $code = $validated['employee_code'] ?? $validated['staff_id'] ?? null;
        $title = $validated['job_title'] ?? $validated['designation'] ?? null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'staff_id' => $validated['staff_id'] ?? $code,
            'employee_code' => $code,
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'job_title' => $title,
            'designation' => $validated['designation'] ?? $title,
            'status' => $validated['status'] ?? 'active',
            'hrms_access' => $request->boolean('hrms_access', true),
            'hrms_role' => $validated['hrms_role'] ?? null,
            'payroll_access' => $request->boolean('payroll_access', true),
            'payroll_role' => $validated['payroll_role'] ?? null,
            'clinic_access' => $request->boolean('clinic_access', true),
            'clinic_role' => $validated['clinic_role'] ?? null,
        ]);

        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        } else {
            // Automatically sync matching standard role if exists
            $defaultRole = \App\Models\Role::where('name', $validated['role'])->first();
            if ($defaultRole) {
                $user->roles()->sync([$defaultRole->id]);
            }
        }

        AuditLog::record(
            'user.created',
            "Master staff identity created for '{$user->name}' ({$user->email}) with role '{$user->role}'.",
            'user_management',
            [
                'target_user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'designation' => $user->designation,
                'hrms_access' => $user->hrms_access,
                'payroll_access' => $user->payroll_access,
                'clinic_access' => $user->clinic_access,
            ]
        );

        return back()->with('success', "Master Staff Identity '{$validated['name']}' created successfully.");
    }

    /**
     * Update user role or designation.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(['superadmin', 'hr_manager', 'payroll_officer', 'finance_officer', 'employee'])],
            'staff_id' => ['nullable', 'string', 'max:50'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_code')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,suspended,resigned,inactive'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['exists:roles,id'],
            'hrms_access' => ['nullable', 'boolean'],
            'hrms_role' => ['nullable', 'string', 'max:50'],
            'payroll_access' => ['nullable', 'boolean'],
            'payroll_role' => ['nullable', 'string', 'max:50'],
            'clinic_access' => ['nullable', 'boolean'],
            'clinic_role' => ['nullable', 'string', 'max:50'],
        ]);

        $code = $validated['employee_code'] ?? $validated['staff_id'] ?? $user->employee_code;
        $title = $validated['job_title'] ?? $validated['designation'] ?? $user->job_title;

        $oldAttributes = [
            'name' => $user->name,
            'role' => $user->role,
            'status' => $user->status,
            'department' => $user->department,
        ];

        $user->update([
            'name' => $validated['name'],
            'role' => $validated['role'],
            'staff_id' => $validated['staff_id'] ?? $code,
            'employee_code' => $code,
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'job_title' => $title,
            'designation' => $validated['designation'] ?? $title,
            'status' => $validated['status'] ?? 'active',
            'hrms_access' => $request->boolean('hrms_access'),
            'hrms_role' => $validated['hrms_role'] ?? null,
            'payroll_access' => $request->boolean('payroll_access'),
            'payroll_role' => $validated['payroll_role'] ?? null,
            'clinic_access' => $request->boolean('clinic_access'),
            'clinic_role' => $validated['clinic_role'] ?? null,
        ]);

        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        AuditLog::record(
            'user.updated',
            "Master staff identity updated for '{$user->name}' ({$user->email}).",
            'user_management',
            [
                'target_user_id' => $user->id,
                'before' => $oldAttributes,
                'after' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'status' => $user->status,
                    'department' => $user->department,
                ],
            ]
        );

        return back()->with('success', "Staff Identity '{$user->name}' updated successfully.");
    }

    /**
     * Delete an identity.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own currently active identity.');
        }

        $deletedDetails = [
            'target_user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        // Revoke associated tokens and sessions
        Token::where('user_id', $user->id)->update(['revoked' => true]);
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->delete();

        AuditLog::record(
            'user.deleted',
            "Master staff identity deleted for '{$deletedDetails['name']}' ({$deletedDetails['email']}).",
            'user_management',
            $deletedDetails
        );

        return back()->with('success', 'User identity deleted.');
    }

    /**
     * Terminate / Invalidate a specific active web session.
     */
    public function revokeSession(string $sessionId): RedirectResponse
    {
        DB::table('sessions')->where('id', $sessionId)->delete();

        AuditLog::record(
            'session.revoked',
            "Web session ID '{$sessionId}' was forcefully terminated by administrator.",
            'session',
            ['session_id' => $sessionId]
        );

        return back()->with('success', 'Session terminated successfully.');
    }

    /**
     * Terminate all sessions for a specific user.
     */
    public function revokeUserSessions(User $user): RedirectResponse
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
        Token::where('user_id', $user->id)->update(['revoked' => true]);

        AuditLog::record(
            'session.user_revoked_all',
            "All active web sessions and OAuth tokens revoked for user '{$user->name}' ({$user->email}).",
            'session',
            ['target_user_id' => $user->id, 'email' => $user->email]
        );

        return back()->with('success', "All sessions and tokens for {$user->name} have been revoked.");
    }

    /**
     * Revoke an OAuth access token.
     */
    public function revokeOAuthToken(string $tokenId): RedirectResponse
    {
        Token::where('id', $tokenId)->update(['revoked' => true]);

        AuditLog::record(
            'oauth.token.revoked',
            "OAuth Access Token ID '{$tokenId}' was revoked by administrator.",
            'oauth',
            ['token_id' => $tokenId]
        );

        return back()->with('success', 'OAuth Access Token revoked.');
    }
}
