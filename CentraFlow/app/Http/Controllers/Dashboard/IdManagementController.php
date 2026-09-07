<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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

        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentTokens = Token::with('client')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', [
            'userCount' => User::count(),
            'sessionCount' => DB::table('sessions')->count(),
            'clientCount' => Client::count(),
            'oauthClients' => Client::orderBy('created_at', 'desc')->get(),
            'modules' => $modules,
            'recentUsers' => $recentUsers,
            'recentTokens' => $recentTokens,
        ]);
    }

    /**
     * Display ID Management and Active Sessions Governance.
     */
    public function index(Request $request): View
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        $oauthClients = Client::orderBy('created_at', 'desc')->get();

        // Retrieve active web sessions from sessions table
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

        // Retrieve issued OAuth tokens (API & SSO sessions)
        $oauthTokens = Token::with('client')
            ->where('revoked', false)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.id_management', [
            'users' => $users,
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
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,suspended,resigned'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'staff_id' => $validated['staff_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return back()->with('success', "Identity '{$validated['name']}' created successfully.");
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
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,suspended,resigned'],
        ]);

        $user->update($validated);

        return back()->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Delete an identity.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own currently active identity.');
        }

        // Revoke associated tokens and sessions
        Token::where('user_id', $user->id)->update(['revoked' => true]);
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('success', 'User identity deleted.');
    }

    /**
     * Terminate / Invalidate a specific active web session.
     */
    public function revokeSession(string $sessionId): RedirectResponse
    {
        DB::table('sessions')->where('id', $sessionId)->delete();

        return back()->with('success', 'Session terminated successfully.');
    }

    /**
     * Terminate all sessions for a specific user.
     */
    public function revokeUserSessions(User $user): RedirectResponse
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
        Token::where('user_id', $user->id)->update(['revoked' => true]);

        return back()->with('success', "All sessions and tokens for {$user->name} have been revoked.");
    }

    /**
     * Revoke an OAuth access token.
     */
    public function revokeOAuthToken(string $tokenId): RedirectResponse
    {
        Token::where('id', $tokenId)->update(['revoked' => true]);

        return back()->with('success', 'OAuth Access Token revoked.');
    }
}
