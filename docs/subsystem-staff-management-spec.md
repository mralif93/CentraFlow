# Sub-System Staff & Access Control Integration Specification
## Universal SSO & Centralized Staff Management Manual

- **Standard Compliance:** IEEE 830-1998, OAuth 2.0 (RFC 6749), PKCE (RFC 7636)
- **Document Version:** 2.1.0
- **Governing Hub:** **CentraFlow Identity & Governance Center** (`:8004`)
- **Consumer Systems:**
  1. **PulseHR Management Suite** (`:8001`)
  2. **PayFlow MY Payroll Suite** (`:8002`)
  3. **Clinic Invoicing System (CIS)** (`:8003`)

---

## 1. Architectural Mandate: CentraFlow as Master Directory

In this architecture, **CentraFlow is the single authoritative source of truth for all enterprise user and staff data**. 

### Rules of Engagement:
1. **Decommission Local Staff Creation & Registration**:
   - Sub-systems must **never** offer local user registration (`/register`) or direct user password creation.
   - All employees, HR managers, payroll officers, and clinical administrators are provisioned and administered centrally in **CentraFlow's ID Management Portal** (`http://localhost:8004/admin/id-management`).
2. **Access Rejection Policy**:
   - Sub-systems must evaluate the `access_control[subsystem].allowed` boolean returned by CentraFlow.
   - If `allowed === false` or the account status is inactive, the sub-system **must deny access immediately** and terminate the session.
3. **Just-In-Time (JIT) Profile Synchronization**:
   - Upon successful OAuth 2.0 PKCE authentication, sub-systems retrieve the authoritative profile via `GET /api/v1/me` and synchronize their local database records automatically.

---

## 2. Master Payload Contract (`GET /api/v1/me`)

Every sub-system authenticates against CentraFlow using its Passport Bearer access token:

```http
GET http://localhost:8004/api/v1/me HTTP/1.1
Authorization: Bearer eyJhbGciOiJSUzI1NiIs...
Accept: application/json
```

### JSON Response Schema:
```json
{
  "status": "success",
  "data": {
    "uuid": "8f8b725c-8df0-4b95-a226-c2eb391f1ad7",
    "name": "Alexander Vance",
    "email": "alexander.vance@centraflow.local",
    "staff_id": "EMP-2026-0001",
    "employee_code": "EMP-2026-0001",
    "phone": "+60123456789",
    "role": "superadmin",
    "department": "Executive Office",
    "job_title": "Chief Technology Officer",
    "status": "active",
    "permissions": [
      "hrms:admin",
      "hrms:employees.manage",
      "hrms:attendance.manage",
      "hrms:leaves.approve",
      "payroll:admin",
      "payroll:calculate",
      "payroll:approve",
      "invoice:admin",
      "invoice:catalog.manage",
      "invoice:create"
    ],
    "subsystem_roles": {
      "hrms": "Super Admin",
      "payroll": "super_admin",
      "clinic": "admin"
    },
    "access_control": {
      "hrms": {
        "allowed": true,
        "role": "Super Admin",
        "permissions": [
          "hrms:admin",
          "hrms:employees.manage",
          "hrms:attendance.manage",
          "hrms:leaves.approve"
        ]
      },
      "payroll": {
        "allowed": true,
        "role": "super_admin",
        "permissions": [
          "payroll:admin",
          "payroll:calculate",
          "payroll:approve"
        ]
      },
      "clinic": {
        "allowed": true,
        "role": "admin",
        "permissions": [
          "invoice:admin",
          "invoice:catalog.manage",
          "invoice:create"
        ]
      }
    },
    "scopes": [
      "hrms:read",
      "hrms:write"
    ]
  }
}
```

---

## 3. Sub-System 1: PulseHR Management Suite (`:8001`)

### 3.1 Functional Requirements
- **FR-HRMS-01 (Access Control Guard):** Deny login if `data.access_control.hrms.allowed === false`.
- **FR-HRMS-02 (Role Binding):** Synchronize `users.role` with `data.access_control.hrms.role` (`'Super Admin'`, `'HR Administrator'`, `'Department Manager'`, `'Employee'`).
- **FR-HRMS-03 (Staff Sync):** Map `data.employee_code` to `users.employee_code`, `data.department` to `users.department`, and `data.job_title` to `users.designation`.
- **FR-HRMS-04 (Session Permissions):** Store `data.access_control.hrms.permissions` in the Laravel session (`centraflow_permissions`).

### 3.2 Implementation Code (HRMS Callback Controller)
File: `app/Http/Controllers/Auth/CentraFlowAuthController.php`

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CentraFlowAuthController extends Controller
{
    public function handleCallback(Request $request)
    {
        // 1. Verify OAuth state
        if ($request->state !== session('centraflow_oauth_state')) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid OAuth state token.']);
        }

        // 2. Exchange authorization code for token
        $tokenResponse = Http::asForm()->post(config('services.centraflow.url') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.centraflow.client_id'),
            'client_secret' => config('services.centraflow.client_secret'),
            'redirect_uri' => route('centraflow.callback'),
            'code_verifier' => session('centraflow_code_verifier'),
            'code' => $request->code,
        ]);

        if ($tokenResponse->failed()) {
            return redirect()->route('login')->withErrors(['email' => 'Failed to retrieve access token from CentraFlow.']);
        }

        $accessToken = $tokenResponse->json('access_token');

        // 3. Fetch Master Profile
        $profileResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get(config('services.centraflow.url') . '/api/v1/me');

        if ($profileResponse->failed()) {
            return redirect()->route('login')->withErrors(['email' => 'Unable to fetch user profile from CentraFlow.']);
        }

        $profile = $profileResponse->json('data');

        // 4. Access Control Verification
        $hrmsAccess = $profile['access_control']['hrms'] ?? null;
        if (!$hrmsAccess || !($hrmsAccess['allowed'] ?? false)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Access Denied: You do not have permission to access the HRMS portal. Contact your CentraFlow administrator.'
            ]);
        }

        // 5. Just-In-Time User Provisioning
        $user = User::firstOrNew(['email' => $profile['email']]);
        $user->name = $profile['name'];
        $user->role = $hrmsAccess['role'] ?? 'Employee';
        $user->employee_code = $profile['employee_code'] ?? $profile['staff_id'];
        $user->department = $profile['department'];
        $user->job_title = $profile['job_title'];
        $user->phone = $profile['phone'] ?? null;
        
        if (!$user->exists) {
            $user->password = bcrypt(Str::random(32));
        }
        $user->save();

        // 6. Cache permissions in session
        session(['centraflow_permissions' => $hrmsAccess['permissions'] ?? []]);

        Auth::login($user, true);
        return redirect()->intended('/dashboard');
    }
}
```

---

## 4. Sub-System 2: PayFlow MY Payroll Suite (`:8002`)

### 4.1 Functional Requirements
- **FR-PAY-01 (Access Control Guard):** Deny login if `data.access_control.payroll.allowed === false`.
- **FR-PAY-02 (RBAC Pivot Sync):** Query native `Role::where('name', $payrollAccess['role'])->first()` and synchronize to `user_roles` pivot table.
- **FR-PAY-03 (Staff Sync):** Map `data.staff_id` to `users.staff_id`.
- **FR-PAY-04 (Session Permissions):** Store `data.access_control.payroll.permissions` in session for fine-grained calculation & approval gating.

### 4.2 Implementation Code (Payroll Callback Controller)
File: `app/Http/Controllers/Auth/CentraFlowSsoController.php`

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CentraFlowSsoController extends Controller
{
    public function callback(Request $request)
    {
        // 1. Verify OAuth state & token exchange
        // ... (standard PKCE exchange code) ...

        $accessToken = $tokenResponse->json('access_token');

        // 2. Fetch authoritative profile
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get(config('services.centraflow.url') . '/api/v1/me');

        $profile = $response->json('data');

        // 3. Evaluate Payroll Access Clearance
        $payrollAccess = $profile['access_control']['payroll'] ?? null;
        if (!$payrollAccess || !($payrollAccess['allowed'] ?? false)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Access Denied: Your account is not cleared for Payroll operations.'
            ]);
        }

        // 4. JIT Provisioning
        $user = User::firstOrNew(['email' => $profile['email']]);
        $user->name = $profile['name'];
        $user->staff_id = $profile['staff_id'];
        
        if (!$user->exists) {
            $user->password = bcrypt(Str::random(32));
        }
        $user->save();

        // 5. Synchronize Native RBAC Pivot Table
        $targetRoleName = $payrollAccess['role'] ?? 'auditor';
        $roleModel = Role::where('name', $targetRoleName)->first();
        if ($roleModel) {
            $user->roles()->sync([$roleModel->id]);
        }

        // 6. Cache granular permissions
        session(['centraflow_permissions' => $payrollAccess['permissions'] ?? []]);

        Auth::login($user, true);
        return redirect()->intended('/payroll/cycles');
    }
}
```

---

## 5. Sub-System 3: Clinic Invoicing System (`:8003`)

### 5.1 Functional Requirements
- **FR-CIS-01 (Access Control Guard):** Deny login if `data.access_control.clinic.allowed === false`.
- **FR-CIS-02 (Role Binding):** Synchronize `users.role` with `data.access_control.clinic.role` (`'admin'` for Doctors/Leads, `'receptionist'` for Cashiers/Front-desk).
- **FR-CIS-03 (Staff Sync):** Synchronize `users.staff_id` with `data.staff_id`.

### 5.2 Implementation Code (Clinic Callback Controller)
File: `app/Http/Controllers/Auth/CentraFlowLoginController.php`

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CentraFlowLoginController extends Controller
{
    public function callback(Request $request)
    {
        // 1. Verify OAuth token exchange
        // ... (standard token exchange) ...

        $profile = $response->json('data');

        // 2. Evaluate Clinic Access Clearance
        $clinicAccess = $profile['access_control']['clinic'] ?? null;
        if (!$clinicAccess || !($clinicAccess['allowed'] ?? false)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Access Denied: You do not have clearance to access Clinic Invoicing.'
            ]);
        }

        // 3. JIT Provisioning
        $user = User::updateOrCreate(
            ['email' => $profile['email']],
            [
                'name' => $profile['name'],
                'role' => $clinicAccess['role'] ?? 'receptionist',
                'staff_id' => $profile['staff_id'] ?? 'STF-001',
                'phone' => $profile['phone'] ?? null,
                'status' => 'active',
                'password' => bcrypt(Str::random(32)),
            ]
        );

        // 4. Cache permissions
        session(['centraflow_permissions' => $clinicAccess['permissions'] ?? []]);

        Auth::login($user, true);
        return redirect()->intended('/invoices');
    }
}
```

---

## 6. Summary of Architectural Advantages

| Metric | Decentralized (Old) | CentraFlow Federated (New) |
| :--- | :--- | :--- |
| **Staff Directory** | Fragmented across 3 databases | Single master registry in CentraFlow |
| **User Deactivation** | Must delete/suspend 3 separate accounts | 1-Click suspension in CentraFlow blocks all 3 systems |
| **Role Maintenance** | Confusing mismatches across portals | Explicit `access_control[system].role` resolved automatically |
| **Password Security** | Passwords duplicated in multiple tables | Passwords exist **only** inside CentraFlow Hub |
| **Audit & Governance** | No central visibility | Complete token & session revoking from CentraFlow console |
