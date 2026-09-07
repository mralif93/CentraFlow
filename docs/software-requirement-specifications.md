# Software Requirements Specification (SRS)
## CentraFlow (HPI Core Platform)

- **Document Version:** 1.0.0
- **Standard Compliance:** IEEE 830-1998
- **Status:** Approved for Architecture & Prototyping

---

## 1. Introduction

### 1.1 Purpose
This document specifies the functional and non-functional requirements for the CentraFlow (HPI Core) platform. It serves as the contractual baseline for software engineering, quality assurance, database design, and API client integration.

### 1.2 Scope
CentraFlow is a centralized, multi-tenant enterprise resource hub developed in Laravel. The system unifies Human Resource Management (HRMS), Payroll processing, and Client Invoicing into a modular architecture. Authentication and authorization are governed centrally via Laravel Passport (OAuth 2.0 without Laravel Sanctum). Direct cross-module SQL joins are restricted; inter-module synchronization is driven via Redis event queues.

```
+-----------------------------------------------------------------------+
|                       CentraFlow Platform                             |
|                                                                       |
|  +------------------+  +------------------+  +---------------------+  |
|  |   Core & Auth    |  |   HRMS Module    |  |   Payroll Module    |  |
|  | - OAuth2 Server  |  | - Staff Master   |  | - Salary Engine     |  |
|  | - Identity/RBAC  |  | - Leave & Shifts |  | - Statutory Calc    |  |
|  | - Audit Trail    |  | - Claims Admin   |  | - Payslip Generator |  |
|  +------------------+  +------------------+  +---------------------+  |
|                                                                       |
|  +-----------------------------------------------------------------+  |
|  |                        Invoicing Module                         |  |
|  |  - Clients & Catalog    - Tax & Invoicing    - Receivables      |  |
|  +-----------------------------------------------------------------+  |
+-----------------------------------------------------------------------+
```

### 1.3 Definitions, Acronyms, and Abbreviations
- **SRS:** Software Requirements Specification
- **RBAC:** Role-Based Access Control
- **HPI:** HRMS, Payroll, and Invoicing
- **UUID:** Universally Unique Identifier (v4/v7)
- **Idempotency:** An operation that can be applied multiple times without altering the outcome beyond the initial execution

---

## 2. Overall Description

### 2.1 Product Perspective
CentraFlow acts as the core administrative system for enterprise operations. It exposes a unified API and single-page dashboard shell, eliminating disparate SaaS silos for HR, payroll calculation, and customer billing.

### 2.2 User Classes and Characteristics
- **Super Administrator:** Manages system configurations, global OAuth clients, and company profiles.
- **HR Manager:** Manages employees, departments, leave policies, and expense claim approvals.
- **Payroll Officer:** Manages compensation packages, runs monthly payroll batches, and generates disbursement records.
- **Finance Officer:** Manages invoicing, client accounts, payment receipts, and tax records.
- **Employee:** Accesses self-service portals to apply for leave, submit claims, view payslips, and log hours.

### 2.3 General Constraints
- **Authentication Engine:** Exclusively Laravel Passport. Laravel Sanctum must not be loaded or executed.
- **Database Isolation:** PostgreSQL with schema separation (`core.*`, `hrms.*`, `payroll.*`, `inv.*`). Cross-schema foreign keys are prohibited.
- **Communication Coupling:** Sub-systems must interact exclusively via queued domain events using Redis or well-defined internal contract interfaces.

---

## 3. External Interface Requirements

### 3.1 User Interfaces
- **Web UI:** Responsive web dashboard built with Tailwind CSS and Alpine.js.
- **Navigation:** Persistent module launcher allowing users to switch between HRMS, Payroll, and Invoicing according to authorized OAuth scopes.

### 3.2 Software & Protocol Interfaces
- **OAuth 2.0 Provider:** Implements RFC 6749 and RFC 7636 (Authorization Code Grant with PKCE, Personal Access Tokens, and Client Credentials).
- **Storage Engine:** S3/GCS-compatible object storage for payslip and invoice PDF artifacts.
- **Cache & Message Broker:** Redis >= 7.0 for session locks, caching, and queue handling.

---

## 4. System Features & Functional Requirements

### 4.1 Module: Core & Identity (Auth Hub)
- **FR-CORE-001 (OAuth Server):** The system shall issue OAuth 2.0 Bearer tokens using Laravel Passport for authenticated users and client applications.
- **FR-CORE-002 (Token Scoping):** The system shall enforce scopes at the route middleware layer:
  - `hrms:read`, `hrms:write`
  - `payroll:run`, `payroll:read`
  - `invoice:manage`, `invoice:read`
- **FR-CORE-003 (Audit Trail):** Every state-changing HTTP request (`POST`, `PUT`, `PATCH`, `DELETE`) must record an immutable audit log containing user ID, IP address, timestamp, prior state, and new payload.
- **FR-CORE-004 (Master Identity):** The core module shall maintain the authoritative identity record (`user_uuid`) referenced by all other domain modules.

### 4.2 Module: HRMS (Human Resource Management)
- **FR-HRMS-001 (Employee Lifecycle):** The system shall support employee onboarding, assignment of job titles, reporting hierarchies, and status transitions (probation, active, terminated).
- **FR-HRMS-002 (Leave Administration):** The system shall calculate leave entitlement, allow staff to request leave, and route requests to designated reporting managers.
- **FR-HRMS-003 (Leave Finalization Event):** Upon approval of unpaid leave, the system shall emit an asynchronous `LeaveApproved` event to the Redis message bus containing employee UUID, dates, and total unpaid days.
- **FR-HRMS-004 (Expense Claims):** The system shall process employee claim receipts and emit a `ClaimApproved` event once approved by finance/management.
- **FR-HRMS-005 (Billable Timesheets):** For billable staff, the system shall track hours worked per client project and emit a `TimesheetApproved` event upon manager sign-off.

### 4.3 Module: Payroll Processing
- **FR-PAY-001 (Compensation Setup):** The system shall maintain salary templates, allowances, and recurring deductions tied to `employee_uuid`.
- **FR-PAY-002 (Event Ingestion):** The module shall asynchronously listen for `LeaveApproved` and `ClaimApproved` events to automatically calculate unpaid leave deductions and claim reimbursements within the active pay period draft.
- **FR-PAY-003 (Idempotent Event Handling):** Ingestion of domain events must verify unique event IDs to prevent duplicate adjustments if a job is retried by Redis workers.
- **FR-PAY-004 (Batch Computation):** The system shall calculate monthly payroll runs in batches using high-precision decimal operations (`ext-bcmath`), calculating gross pay, employee/employer statutory contributions, and net pay.
- **FR-PAY-005 (Payslip Generation):** The system shall render standardized PDF payslips via background workers and deliver download links to the employee self-service portal.

### 4.4 Module: Invoicing & Accounts Receivable
- **FR-INV-001 (Client Directory):** The system shall manage client profiles, tax registration numbers, and default payment terms.
- **FR-INV-002 (Timesheet Itemization):** The module shall listen for `TimesheetApproved` events from HRMS and automatically generate unbilled line items tagged to the specified client.
- **FR-INV-003 (Invoice Generation):** The system shall generate numbered sales invoices with sub-totals, applicable tax percentages, and localized currency formatting.
- **FR-INV-004 (Payment Reconciliation):** The system shall record full and partial invoice payments, tracking remaining balances and overdue aging buckets (30/60/90+ days).

---

## 5. Non-Functional Requirements

### 5.1 Performance Requirements
- **API Latency:** 95% of non-computational API endpoints (`GET /api/*`) must respond within $\le$ 200 ms under a concurrent load of 500 requests per second.
- **Batch Execution:** The payroll engine must calculate and compile batches for 1,000 employees in $\le$ 45 seconds.

### 5.2 Security Requirements
- **Token Invalidation:** Revoking a Passport token via `/oauth/tokens` must take effect immediately across all modules.
- **Data Encryption:** Employee bank accounts, identification numbers, and base salaries must be encrypted at rest using AES-256-GCM.
- **Isolation Enforcement:** No database user used by application code shall be granted cross-schema execution rights that bypass model access controls.

### 5.3 Reliability & Fault Tolerance
- **Message Broker Fallback:** In the event of a Redis connectivity drop, queued events must remain buffered or fail gracefully with automatic retries (maximum 5 attempts with exponential backoff).
- **Audit Immutability:** Audit records must not permit update or delete actions by any administrative role via application routes.

---

## 6. Verification & Traceability Matrix

| Requirement ID | Verification Method | Acceptance Criteria |
| :--- | :--- | :--- |
| **FR-CORE-001** | Integration Test | Valid OAuth client receives RFC-compliant Bearer token; rejects invalid secrets. |
| **FR-HRMS-003** | Unit / Event Test | Approving unpaid leave verifies `LeaveApproved` reaches the Redis queue with correct payload. |
| **FR-PAY-003** | Stress / Re-play Test | Processing the identical `LeaveApproved` event twice yields zero duplicate deductions in draft payroll. |
| **FR-INV-003** | Functional Test | Invoice tax calculations and total line items match line-by-line decimal sums without floating-point errors. |