# CentraFlow — Enterprise Federated Identity Hub & Event Orchestrator

<p align="center">
  <a href="https://mralif93.github.io/CentraFlow/docs/">
    <img src="https://img.shields.io/badge/Live_Showcase-GitHub_Pages-6366f1?style=for-the-badge&logo=github&logoColor=white" alt="Live Showcase on GitHub Pages">
  </a>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Passport-v13_OAuth_2.0-8957e5?style=for-the-badge&logo=passport&logoColor=white" alt="Laravel Passport v13">
  <img src="https://img.shields.io/badge/Redis-Event_Mesh-DC382D?style=for-the-badge&logo=redis&logoColor=white" alt="Redis Event Mesh">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Compliance-IEEE_830--1998-success?style=for-the-badge" alt="IEEE 830-1998">
  <img src="https://img.shields.io/badge/Tests-19_Passing-10B981?style=for-the-badge&logo=checkmarx" alt="Tests Passing">
</p>

---

## 🌐 Live Architecture Showcase & Interactive Developer Console
Experience the live interactive showcase and federated governance specifications deployed on GitHub Pages:  
👉 **[https://mralif93.github.io/CentraFlow/docs/](https://mralif93.github.io/CentraFlow/docs/)**

Includes interactive client-side sandboxes and tools:
- 🔐 **OAuth 2.0 PKCE & Authorization Code Flow Visualizer** (Simulate authorize &rarr; code &rarr; token exchange with granular scopes)
- 🛰️ **Microservice Real-Time Health & Scope Ping Simulator** (HRMS `:8001`, Payroll `:8002`, Clinic Invoicing `:8003`)
- 📡 **Redis Domain Event Mesh Inspector** (Live JSON preview of `LeaveApproved`, `ClaimApproved`, and `TimesheetApproved` topics)
- 👥 **Enterprise Unified Identity Directory Preview** (Multi-tenant profile syncing across federated sub-systems)
- 🛡️ **Centralized Single Sign-Out (SLO) & Cookie Isolation Sandbox** (Session teardown mechanics preventing localhost collisions)

---

## 📋 System Overview & Architecture

**CentraFlow** is the central **Enterprise Identity Provider (IdP)** and **Microservice Orchestrator** for a federated application mesh comprising **HRMS** (`:8001`), **Payroll** (`:8002`), and **Clinic Invoicing** (`:8003`).

Developed with Laravel 12 and Laravel Passport (RFC 6749 & RFC 7636 compliant), CentraFlow completely eliminates disparate SaaS silos and credential leaks by centralizing identity management, web session governance, and event-driven data propagation.

```text
+-----------------------------------------------------------------------------------+
|                        CentraFlow Central Identity Hub (:8004)                    |
|                OAuth 2.0 Authorization Server / Master User Registry              |
|                     Passport v13 PKCE & Authorization Code Grant                  |
+-------------------+-------------------------------+-------------------------------+
                    |                               |                               |
     OAuth 2.0 Flow |                OAuth 2.0 Flow |                OAuth 2.0 Flow |
     & Scope Grant  |                & Scope Grant  |                & Scope Grant  |
                    v                               v                               v
+-----------------------+       +-----------------------+       +-----------------------+
|  PulseHR Suite (:8001)|       |  PayFlow MY (:8002)   |       | Clinic Invoicing(:8003|
|                       |       |                       |       |                       |
| - Staff Master (PIM)  |       | - Statutory Deductions|       | - Patient Billing POS |
| - Geofenced Attendance|       | - Salary Computation  |       | - Catalog & Inventory |
| - Multi-Tier Leave    |       | - Direct PCB / Payslip|       | - ESC/POS & A4 Invoice|
+-----------------------+       +-----------------------+       +-----------------------+
            |                               ^                               ^
            | Redis Event: LeaveApproved    |                               |
            +-------------------------------+                               |
            | Redis Event: ClaimApproved                                    |
            +-------------------------------+                               |
            | Redis Event: TimesheetApproved                                |
            +---------------------------------------------------------------+
```

> [!IMPORTANT]
> **Strict Architectural Constraints (SRS Section 2.3 & 5.1)**:
> 1. **Zero Sanctum Mandate**: CentraFlow strictly enforces Laravel Passport v13 OAuth 2.0. `laravel/sanctum` is explicitly prohibited.
> 2. **Database Schema Isolation**: No cross-database SQL joins or cross-schema foreign keys. All inter-service communication is governed via tokenized REST verification (`/api/v1/me`) or asynchronous Redis domain event queues.
> 3. **Session Cookie Isolation**: Prevents `laravel_session` collisions across local microservices by isolating cookie names:
>    - CentraFlow: `centraflow_session`
>    - PulseHR: `pulsehr_session`
>    - PayFlow MY: `payroll_session`

---

## 🧩 Federated Microservice Ecosystem

| Sub-System | Port | OAuth Client ID | Authorized Scopes | Event Mesh Role | Repository |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **CentraFlow Core** | `:8004` | *Identity Provider* | Global Hub | Event Broker & Hub | [CentraFlow](https://github.com/mralif93/CentraFlow) |
| **PulseHR (HRMS)** | `:8001` | `9d12a101-0001-4000-8000-000000000001` | `hrms:read hrms:write` | **Publisher** (`LeaveApproved`, `ClaimApproved`) | [human-resources-management-system](https://github.com/mralif93/human-resources-management-system) |
| **PayFlow MY** | `:8002` | `9d12a101-0002-4000-8000-000000000002` | `payroll:run payroll:read` | **Ingestor** (`ClaimApproved`, `LeaveApproved`) | [payroll-management-system](https://github.com/mralif93/payroll-management-system) |
| **Clinic Invoicing** | `:8003` | `9d12a101-0003-4000-8000-000000000003` | `invoice:manage invoice:read` | **Ingestor** (`TimesheetApproved`) | [clinic-invoice-system](https://github.com/mralif93/clinic-invoice-system) |

---

## 📡 Redis Event Mesh Contracts

In strict compliance with SRS Section 4 & 5, sub-systems communicate domain states asynchronously using Redis pub/sub topics:

### 1. `LeaveApproved` (HRMS &rarr; Payroll)
Dispatched when unpaid or extended leave is authorized. Payroll consumes this to calculate statutory wage pro-rations.
```json
{
  "event_id": "0191c45a-8bc0-711e-b83c-1b772cf89091",
  "event_type": "LeaveApproved",
  "origin_module": "hrms",
  "timestamp": "2026-09-07T09:30:00Z",
  "payload": {
    "employee_uuid": "e30e1df5-2cfc-469b-9ffb-14b35e23da91",
    "leave_type": "unpaid",
    "days": 2.5,
    "start_date": "2026-09-15",
    "end_date": "2026-09-17"
  }
}
```

### 2. `ClaimApproved` (HRMS &rarr; Payroll)
Dispatched when employee expenses are approved by HR, automatically queuing non-taxable allowances in the draft payroll cycle.
```json
{
  "event_id": "0191c45a-8bc0-711e-b83c-1b772cf89092",
  "event_type": "ClaimApproved",
  "origin_module": "hrms",
  "timestamp": "2026-09-07T09:35:00Z",
  "payload": {
    "employee_uuid": "e30e1df5-2cfc-469b-9ffb-14b35e23da91",
    "claim_type": "medical",
    "amount": 185.50,
    "currency": "MYR"
  }
}
```

### 3. `TimesheetApproved` (HRMS &rarr; Clinic Invoicing)
Dispatched when clinical shifts or billable hours are signed off by department leads for patient billing.
```json
{
  "event_id": "0191c45a-8bc0-711e-b83c-1b772cf89093",
  "event_type": "TimesheetApproved",
  "origin_module": "hrms",
  "timestamp": "2026-09-07T09:40:00Z",
  "payload": {
    "practitioner_uuid": "e30e1df5-2cfc-469b-9ffb-14b35e23da91",
    "hours_billed": 8.0,
    "treatment_code": "CONSULT-GEN-01"
  }
}
```

---

## 🔐 Role Hierarchy & Unified Attribute Mapping

CentraFlow synchronizes identity profiles across all sub-systems via `/api/v1/me`:

| Attribute | HRMS (`:8001`) | Payroll (`:8002`) | Clinic Invoicing (`:8003`) | Description |
| :--- | :--- | :--- | :--- | :--- |
| `uuid` | Primary Key Reference | Employee Identity Ref | User Account Ref | RFC 4122 v4 Global UUID |
| `staff_id` | `employee_code` (`EMP-001`) | `staff_id` (`EMP-001`) | `staff_id` (`ADM-001` / `STF-001`) | Human-readable company badge code |
| `email` | User Auth Email | Staff Email | Frontdesk / Cashier Email | Corporate Single Sign-On identifier |
| `role` | `superadmin`, `hr_manager` | `payroll_officer`, `employee` | `doctor`, `receptionist` | Global access level mapping |
| `department` | Org Department | Cost Center Group | Clinic Branch Unit | Enterprise organizational node |
| `status` | `active`, `resigned`, `leave` | `active`, `inactive` | `active`, `suspended` | Universal operational state |

---

## 🚀 Quick Start (Local Setup)

```bash
# 1. Clone CentraFlow
git clone https://github.com/mralif93/CentraFlow.git
cd CentraFlow/CentraFlow

# 2. Install Dependencies
composer install
npm install

# 3. Environment Setup
cp .env.example .env
php artisan key:generate

# Generate Passport Encryption Keys
php artisan passport:keys --force

# 4. Run Migrations & System Seeders
# Automatically provisions OAuth clients, admin profiles, and sample users
php artisan migrate --seed

# 5. Build Frontend Assets
npm run build

# 6. Start CentraFlow Hub on Port 8004
php artisan serve --port=8004
```

Visit the CentraFlow Hub at: **`http://localhost:8004`**

### Default Administrative Credentials
- **Email:** `superadmin@centraflow.local`
- **Password:** `password`
- **Role:** `superadmin`
- **Console:** `http://localhost:8004/admin/dashboard`

---

## 🧪 Automated Testing

CentraFlow features comprehensive unit and feature test suites validating OAuth 2.0 authorization, PKCE verification, `/api/v1/me` scope protection, and session revocation:

```bash
php artisan test
```

```text
PASS  Tests\Feature\CentraFlowAuthAndScopeTest
✓ oauth server can issue token with hrms scopes
✓ oauth server can issue token with payroll scopes
✓ me endpoint returns unified enterprise user payload
✓ invalid token receives 401 unauthorized

PASS  Tests\Feature\CentraFlowAuthWebTest
✓ login screen can be rendered
✓ admin can authenticate via web
✓ admin can terminate database web sessions

PASS  Tests\Feature\CentraFlowIdManagementTest
✓ authenticated admin can view admin overview
✓ authenticated admin can view id management dashboard
✓ admin can create new master identity
✓ admin can terminate a web session

Tests:    19 passed (48 assertions)
Duration: 0.35s
```

---

## 📚 Technical Documentation Index

Detailed specifications, interface protocols, and integration guides:
1. 📄 **[Software Requirements Specification (SRS)](docs/software-requirement-specifications.md)** — Full IEEE 830-1998 standard specification.
2. 📄 **[Federated Session Management Guide (SSO & SLO)](docs/federated-session-management-guide.md)** — Comprehensive guidelines for Single Sign-In, Single Sign-Out, and session auto-revalidation.
3. 📄 **[Sub-System Staff & Access Control Specification](docs/subsystem-staff-management-spec.md)** — Architectural requirements and implementation code for HRMS, Payroll, and CIS.
4. 📄 **[Access Control & Permission Architecture Guide](docs/access-control-guide.md)** — Unified RBAC catalog, `/api/v1/me` permissions, and sub-system adoption directives.
5. 📄 **[Sub-System SSO Authentication Guide](docs/subsystem-auth-guide.md)** — Step-by-step OAuth 2.0 client implementation for sub-systems.
6. 📄 **[Integration Contracts & Event Schemas](docs/integration-contracts.md)** — Detailed JSON payloads and Redis channel topologies.
7. 📄 **[SSO Client Developer Guide](docs/sso-client-guide.md)** — Quick start for third-party microservice integration.

---

## 📄 License
This project is open-source under the [MIT License](LICENSE).
