# Enterprise Access Control & Permission Matrix Guide
## CentraFlow Federated Governance Manual for Sub-Systems

- **Standard Compliance:** IEEE 830-1998, RFC 6749, RFC 7636
- **Version:** 2.0.0
- **Status:** Contractually Binding Architecture
- **Target Applications:** **PulseHR** (`:8001`), **PayFlow MY** (`:8002`), **Clinic Invoicing** (`:8003`)

---

## 1. Executive Summary

In a federated architecture, **authentication (AuthN)** and **global authorization (AuthZ)** are centralized in **CentraFlow** (`:8004`). Disparate sub-systems must not invent isolated role schemas or maintain out-of-sync privilege levels.

CentraFlow's `/api/v1/me` endpoint delivers:
1. **Normalized Global Role (`role`)**: Universal corporate tier (`superadmin`, `hr_manager`, `payroll_officer`, `finance_officer`, `employee`).
2. **Pre-computed Sub-System Roles (`subsystem_roles`)**: Exact role values formatted specifically for each sub-system's native database schema and guards.
3. **Granular Permissions Array (`permissions`)**: Enterprise-wide capabilities (`hrms:leaves.approve`, `payroll:calculate`, `invoice:create`, etc.) enabling fine-grained UI element gating and middleware protection.

---

## 2. Global Role & Permission Catalog

CentraFlow centrally provisions the following master roles and permission assignments:

| Global Role (`role`) | Target Persona | HRMS Role (`:8001`) | Payroll Role (`:8002`) | Clinic Invoicing Role (`:8003`) | Master Capabilities |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`superadmin`** | Executive / CTO / Lead Admin | `Super Admin` | `super_admin` | `admin` | Full unrestricted access across all modules, configuration, and audit logs. |
| **`hr_manager`** | People Operations Lead | `HR Administrator` | `auditor` (read-only) | `receptionist` (self-service) | Full staff directory (PIM), attendance, leave approval chains, and recruitment. |
| **`payroll_officer`**| Compensation Specialist | `Employee` (staff view) | `payroll_officer` | `receptionist` (self-service) | Statutory deductions (EPF/SOCSO/EIS/PCB), salary batch calculations, and payslips. |
| **`finance_officer`**| Controller / Billing Head | `Employee` (claims view) | `finance_director` | `admin` | Billing catalog, invoice issuing, payment receipts, voiding, and EDC bank reconciliation. |
| **`employee`** | General Workforce | `Employee` | `auditor` (self payslips) | `receptionist` (shift billing) | Self-service profile, clock in/out, leave applications, and expense submissions. |

---

## 3. Centralized Payload Contract (`GET /api/v1/me`)

When any sub-system calls `GET http://localhost:8004/api/v1/me` with a valid Bearer token, CentraFlow responds with the complete authorization profile:

```http
GET /api/v1/me HTTP/1.1
Host: localhost:8004
Authorization: Bearer eyJhbGciOiJSUzI1NiIs...
Accept: application/json
```

```json
{
  "status": "success",
  "data": {
    "uuid": "8f8b725c-8df0-4b95-a226-c2eb391f1ad7",
    "name": "Alexander Vance",
    "email": "alexander.vance@centraflow.local",
    "staff_id": "EMP-2026-0001",
    "employee_code": "EMP-2026-0001",
    "phone": "+1 (555) 019-2831",
    "role": "superadmin",
    "department": "Executive Office",
    "job_title": "Chief Technology Officer",
    "status": "active",
    "subsystem_roles": {
      "hrms": "Super Admin",
      "payroll": "super_admin",
      "clinic": "admin"
    },
    "permissions": [
      "hrms:admin",
      "hrms:employees.manage",
      "hrms:attendance.manage",
      "hrms:leaves.approve",
      "hrms:performance.manage",
      "hrms:recruitment.manage",
      "payroll:admin",
      "payroll:calculate",
      "payroll:approve",
      "payroll:lock",
      "payroll:exports.bank",
      "payroll:statutory.manage",
      "invoice:admin",
      "invoice:catalog.manage",
      "invoice:create",
      "invoice:void",
      "invoice:reports.view"
    ],
    "scopes": [
      "hrms:read",
      "hrms:write"
    ]
  }
}
```

---

## 4. Sub-System Implementation Guidelines

### 4.1 Sub-System 1: PulseHR Management Suite (`:8001`)

#### Existing Mechanism
- HRMS utilizes a string column `users.role` with values: `'Super Admin'`, `'HR Administrator'`, `'Department Manager'`, `'Employee'`.
- Route protection is governed by middleware `\App\Http\Middleware\EnsureRole` or helper methods `$user->isSuperAdmin()`, `$user->isHRAdmin()`.

#### Integration Directive
Update `CentraFlowSsoClientController::callback()` in `hrms`:

```php
// Extract verified role and permissions from CentraFlow
$subsystemRole = $profile['subsystem_roles']['hrms'] ?? 'Employee';
$permissions   = $profile['permissions'] ?? [];

$user = User::firstOrNew(['email' => $profile['email']]);
$user->name          = $profile['name'];
$user->role          = $subsystemRole;
$user->department    = $profile['department'];
$user->job_title     = $profile['job_title'];
$user->employee_code = $profile['employee_code'] ?? $profile['staff_id'];
$user->phone         = $profile['phone'] ?? null;
$user->save();

// Cache permissions in session for blade and policy checks
$request->session()->put('centraflow_permissions', $permissions);
```

#### Blade Directives & Gating
Create a helper or Blade directive in HRMS:
```php
// In AppServiceProvider or Blade directive
Blade::if('canAccess', function (string $permission) {
    $perms = session('centraflow_permissions', []);
    return in_array($permission, $perms, true) || auth()->user()?->role === 'Super Admin';
});
```

---

### 4.2 Sub-System 2: PayFlow MY Payroll Suite (`:8002`)

#### Existing Mechanism
- PayFlow MY utilizes an RBAC relationship: `users` &harr; `user_roles` &harr; `roles` (`super_admin`, `payroll_officer`, `finance_director`, `auditor`).
- Permission table: `permissions` (`payroll.calculate`, `payroll.approve`, `payroll.lock`, `exports.bank`, `statutory.manage`, etc.).
- Guard methods: `$user->hasRole('payroll_officer')`, `$user->hasPermission('payroll.calculate')`.

#### Integration Directive
Update `CentraFlowSsoClientController::callback()` in `payroll`:

```php
// Extract payroll specific role
$targetRoleName = $profile['subsystem_roles']['payroll'] ?? 'auditor';

$user = User::firstOrNew(['email' => $profile['email']]);
if (!$user->exists) {
    $user->name = $profile['name'];
    $user->password = bcrypt(Str::random(32));
    $user->save();
}

// Automatically bind the corresponding Role model in user_roles pivot
$roleModel = \App\Models\Role::where('name', $targetRoleName)->first();
if ($roleModel) {
    $user->roles()->sync([$roleModel->id]);
}

// Store central permissions in session
$request->session()->put('centraflow_permissions', $profile['permissions'] ?? []);
```

---

### 4.3 Sub-System 3: Clinic Invoicing System (`:8003`)

#### Existing Mechanism
- Clinic Invoicing uses `users.role` with values: `'admin'` (Doctor/Practice Lead) and `'receptionist'` (Cashier/Front-desk).
- Additional attributes: `staff_id` (`ADM-001`, `STF-001`), `status` (`active`, `suspended`).
- Method check: `$user->isAdmin()`.

#### Integration Directive
Update `CentraFlowSsoClientController::callback()` in `cis`:

```php
$targetRole = $profile['subsystem_roles']['clinic'] ?? 'receptionist';

$user = User::updateOrCreate(
    ['email' => $profile['email']],
    [
        'name'            => $profile['name'],
        'role'            => $targetRole,
        'staff_id'        => $profile['staff_id'] ?? 'STF-001',
        'phone'           => $profile['phone'] ?? null,
        'status'          => $profile['status'] ?? 'active',
        'centraflow_uuid' => $profile['uuid'],
        'password'        => bcrypt(Str::random(32)),
    ]
);

// Store central permissions in session
$request->session()->put('centraflow_permissions', $profile['permissions'] ?? []);
```

---

## 5. Summary of Benefits & Architecture Compliance

1. **Zero Discrepancy**: A role change in CentraFlow (e.g. promoting an employee to `hr_manager` or `finance_officer`) immediately takes effect across HRMS, Payroll, and Invoicing on their subsequent login.
2. **Central User Provisioning**: New staff accounts created in CentraFlow ID Management are automatically available for Single Sign-On across all 3 portals with zero manual duplicate database seeding.
3. **Strict Decoupling**: Sub-systems require no direct SQL connection to CentraFlow. All authorization parameters are securely signed and verified via OAuth 2.0 PKCE and the `/api/v1/me` REST contract.
