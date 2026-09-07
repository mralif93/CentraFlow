# CentraFlow Sub-System Integration Guide & Event Contracts

This guide documents how the three sub-systems (**HRMS**, **Payroll**, and **Clinic Invoicing**) integrate with **CentraFlow** under **Option A (Hub & Spoke Architecture)**.

---

## 1. System Topology & Ports

| System | Port | Role | GitHub Repository |
| :--- | :--- | :--- | :--- |
| **CentraFlow Core** | `:8000` | OAuth2 Provider (Passport), User Master (`user_uuid`), App Launcher | [CentraFlow](https://github.com/mralif93/CentraFlow) |
| **HRMS** | `:8001` | Employee master, leave requests, claims, billable time | [human-resources-management-system](https://github.com/mralif93/human-resources-management-system) |
| **Payroll** | `:8002` | Compensation engines, statutory deductions, payslips | [payroll-management-system](https://github.com/mralif93/payroll-management-system) |
| **Clinic Invoicing** | `:8003` | Clients/patients, items catalog, invoices, receivables | [clinic-invoice-system](https://github.com/mralif93/clinic-invoice-system) |

---

## 2. OAuth 2.0 Identity Flow

Each sub-system is registered in CentraFlow with a pre-configured OAuth client:

### Pre-Registered Client Credentials

- **HRMS Client:**
  - `client_id`: `9d12a101-0001-4000-8000-000000000001`
  - `client_secret`: `hrms_secret_centraflow_2026`
  - `redirect_uri`: `http://localhost:8001/auth/callback`

- **Payroll Client:**
  - `client_id`: `9d12a101-0002-4000-8000-000000000002`
  - `client_secret`: `payroll_secret_centraflow_2026`
  - `redirect_uri`: `http://localhost:8002/auth/callback`

- **Clinic Invoicing Client:**
  - `client_id`: `9d12a101-0003-4000-8000-000000000003`
  - `client_secret`: `invoice_secret_centraflow_2026`
  - `redirect_uri`: `http://localhost:8003/auth/callback`

### Verifying Identity & Scopes
Sub-systems can query CentraFlow to verify any incoming Bearer token:

```http
GET http://localhost:8000/api/v1/me
Authorization: Bearer <access_token>
```

**Response Payload:**
```json
{
  "status": "success",
  "data": {
    "uuid": "8f8b725c-8df0-4b95-a226-c2eb391f1ad7",
    "name": "Alex Payroll Officer",
    "email": "payroll@centraflow.local",
    "role": "payroll_officer",
    "scopes": [
      "payroll:run",
      "payroll:read"
    ]
  }
}
```

---

## 3. Redis Event Contracts & Data Synchronization

In accordance with SRS Section 4 & 5, direct cross-database foreign keys are prohibited. Sub-systems communicate via asynchronous Redis events.

### Event 1: `LeaveApproved` (HRMS $\rightarrow$ Payroll)
Emitted by HRMS whenever unpaid leave is approved. Payroll consumes this to calculate automatic salary deductions for the active payroll cycle.

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
    "end_date": "2026-09-17",
    "approved_by_uuid": "8f8b725c-8df0-4b95-a226-c2eb391f1ad7"
  }
}
```

### Event 2: `ClaimApproved` (HRMS $\rightarrow$ Payroll)
Emitted by HRMS when a staff medical or transport claim is approved. Payroll adds this as a non-taxable reimbursement allowance to the employee's draft payslip.

```json
{
  "event_id": "0191c45a-8bc0-711e-b83c-1b772cf89092",
  "event_type": "ClaimApproved",
  "origin_module": "hrms",
  "timestamp": "2026-09-07T09:35:00Z",
  "payload": {
    "employee_uuid": "e30e1df5-2cfc-469b-9ffb-14b35e23da91",
    "claim_id": "CLM-2026-0045",
    "category": "medical",
    "amount": 150.00,
    "currency": "MYR"
  }
}
```

### Event 3: `TimesheetApproved` (HRMS $\rightarrow$ Clinic Invoicing)
Emitted by HRMS when billable client/clinical hours are signed off. Clinic Invoicing ingests this to generate unbilled invoice line items.

```json
{
  "event_id": "0191c45a-8bc0-711e-b83c-1b772cf89093",
  "event_type": "TimesheetApproved",
  "origin_module": "hrms",
  "timestamp": "2026-09-07T09:40:00Z",
  "payload": {
    "client_id": "CLI-9012",
    "clinician_uuid": "e30e1df5-2cfc-469b-9ffb-14b35e23da91",
    "service_code": "CONSULT_SPECIALIST",
    "billable_hours": 3.0,
    "rate_per_hour": 250.00,
    "total_amount": 750.00
  }
}
```

---

## 4. Idempotency Enforcement Pattern

To satisfy requirement `FR-PAY-003`, every event consumer in Payroll and Invoicing must check the `event_id`:

```php
if (ProcessedEvent::where('event_id', $event['event_id'])->exists()) {
    // Event has already been applied; acknowledge without reprocessing
    return;
}

DB::transaction(function () use ($event) {
    // 1. Process adjustment (e.g. deduct unpaid leave)
    // 2. Record event ID
    ProcessedEvent::create([
        'event_id' => $event['event_id'],
        'event_type' => $event['event_type'],
        'processed_at' => now(),
    ]);
});
```
