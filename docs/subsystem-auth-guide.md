# Sub-System Authentication Integration Manual (SSO with CentraFlow)

This document provides complete, step-by-step instructions for integrating **HRMS** (`:8001`), **Payroll** (`:8002`), and **Clinic Invoicing (CIS)** (`:8003`) with the central identity provider **CentraFlow** (`http://localhost:8004`).

---

## 1. Architecture Overview

CentraFlow acts as the **OAuth 2.0 Authorization Server** and **Master Identity Provider** using Laravel Passport (v13). The sub-systems act as **OAuth 2.0 Clients** using standard **Authorization Code Grant with State Verification**.

```
+-------------+               +---------------+               +-----------------+
| User / Web  |  1. Click SSO |   Sub-System  | 2. Redirect   |   CentraFlow    |
|   Browser   | ------------> | (HRMS/Pay/CIS)| ------------> | (Hub / Port 8004|
|             |               |               |               |                 |
|             |  3. Login & Consent Form                      |                 |
|             | <===========================================> | (Enter Password)|
|             |                                               |                 |
|             |  4. Redirect back with ?code=XYZ              |                 |
|             | ----------------------------> |               |                 |
|             |                               | 5. Token POST |                 |
|             |                               | ------------> | Validates Code  |
|             |                               | <------------ | Issues Token    |
|             |                               |               |                 |
|             |                               | 6. GET /me    |                 |
|             |                               | ------------> | Returns profile |
|             |                               | <------------ | {uuid, role...} |
|             |                               |               +-----------------+
|             |  7. Authenticated Dashboard   |
|             | <---------------------------- | (Auth::login)
+-------------+                               +---------------+
```

---

## 2. Pre-Registered Client Credentials

These credentials are pre-seeded into CentraFlow's database (`oauth_clients`):

| Project | Port | Client ID | Client Secret | Callback Redirect URI | Authorized Scopes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **HRMS** | `:8001` | `9d12a101-0001-4000-8000-000000000001` | `hrms_secret_centraflow_2026` | `http://localhost:8001/auth/callback` | `hrms:read hrms:write` |
| **Payroll** | `:8002` | `9d12a101-0002-4000-8000-000000000002` | `payroll_secret_centraflow_2026` | `http://localhost:8002/auth/callback` | `payroll:run payroll:read` |
| **Clinic Invoicing** | `:8003` | `9d12a101-0003-4000-8000-000000000003` | `invoice_secret_centraflow_2026` | `http://localhost:8003/auth/callback` | `invoice:manage invoice:read` |

---

## 3. Configuration Per Sub-System

Add these variables to each sub-system's local `.env` file:

### A. HRMS (`/Users/alif/Desktop/Project/Github/human-resources-management-system/hrms/.env`)
```env
# CentraFlow Central SSO Provider
CENTRAFLOW_HOST=http://localhost:8004
CENTRAFLOW_CLIENT_ID=9d12a101-0001-4000-8000-000000000001
CENTRAFLOW_CLIENT_SECRET=hrms_secret_centraflow_2026
CENTRAFLOW_REDIRECT_URI=http://localhost:8001/auth/callback
CENTRAFLOW_SCOPES="hrms:read hrms:write"
```

### B. Payroll (`/Users/alif/Desktop/Project/Github/payroll-management-system/.env`)
```env
# CentraFlow Central SSO Provider
CENTRAFLOW_HOST=http://localhost:8004
CENTRAFLOW_CLIENT_ID=9d12a101-0002-4000-8000-000000000002
CENTRAFLOW_CLIENT_SECRET=payroll_secret_centraflow_2026
CENTRAFLOW_REDIRECT_URI=http://localhost:8002/auth/callback
CENTRAFLOW_SCOPES="payroll:run payroll:read"
```

### C. Clinic Invoice System (`/Users/alif/Desktop/Project/Github/clinic-invoice-system/cis/.env`)
```env
# CentraFlow Central SSO Provider
CENTRAFLOW_HOST=http://localhost:8004
CENTRAFLOW_CLIENT_ID=9d12a101-0003-4000-8000-000000000003
CENTRAFLOW_CLIENT_SECRET=invoice_secret_centraflow_2026
CENTRAFLOW_REDIRECT_URI=http://localhost:8003/auth/callback
CENTRAFLOW_SCOPES="invoice:manage invoice:read"
```

---

## 4. Implementation in Each Sub-System

Follow these 4 simple steps in each sub-system repository:

### Step 1: Add Routes (`routes/web.php`)

```php
use App\Http\Controllers\CentraFlowSsoClientController;

// Single Sign-On Routes
Route::get('/auth/centraflow', [CentraFlowSsoClientController::class, 'redirect'])->name('sso.login');
Route::get('/auth/callback', [CentraFlowSsoClientController::class, 'callback'])->name('sso.callback');
```

---

### Step 2: Create Controller (`app/Http/Controllers/CentraFlowSsoClientController.php`)

Create this controller in `app/Http/Controllers/CentraFlowSsoClientController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CentraFlowSsoClientController extends Controller
{
    /**
     * Redirect the user to CentraFlow SSO authorization dialog.
     */
    public function redirect(Request $request)
    {
        // 1. Generate CSRF state token
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        // 2. Build OAuth authorization query
        $query = http_build_query([
            'client_id'     => config('services.centraflow.client_id', env('CENTRAFLOW_CLIENT_ID')),
            'redirect_uri'  => config('services.centraflow.redirect_uri', env('CENTRAFLOW_REDIRECT_URI')),
            'response_type' => 'code',
            'scope'         => config('services.centraflow.scopes', env('CENTRAFLOW_SCOPES', '')),
            'state'         => $state,
        ]);

        $authUrl = rtrim(config('services.centraflow.host', env('CENTRAFLOW_HOST')), '/') . '/oauth/authorize?' . $query;

        return redirect()->away($authUrl);
    }

    /**
     * Handle the OAuth callback from CentraFlow.
     */
    public function callback(Request $request)
    {
        // 1. Verify CSRF state token
        $savedState = $request->session()->pull('oauth_state');
        if (empty($savedState) || $savedState !== $request->query('state')) {
            abort(403, 'Invalid or expired OAuth state token.');
        }

        if ($request->has('error')) {
            return redirect('/login')->withErrors(['oauth' => 'CentraFlow authorization was denied.']);
        }

        // 2. Exchange authorization code for access token
        $tokenResponse = Http::asForm()->post(rtrim(env('CENTRAFLOW_HOST'), '/') . '/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('CENTRAFLOW_CLIENT_ID'),
            'client_secret' => env('CENTRAFLOW_CLIENT_SECRET'),
            'redirect_uri'  => env('CENTRAFLOW_REDIRECT_URI'),
            'code'          => $request->query('code'),
        ]);

        if (! $tokenResponse->successful()) {
            return redirect('/login')->withErrors(['oauth' => 'Could not exchange code with CentraFlow: ' . $tokenResponse->body()]);
        }

        $tokenPayload = $tokenResponse->json();
        $accessToken = $tokenPayload['access_token'];

        // 3. Retrieve user profile from CentraFlow master directory
        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get(rtrim(env('CENTRAFLOW_HOST'), '/') . '/api/v1/me');

        if (! $userResponse->successful()) {
            return redirect('/login')->withErrors(['oauth' => 'Failed retrieving profile from CentraFlow.']);
        }

        $profile = $userResponse->json('data');

        // 4. Find or provision user in local sub-system database
        $user = User::updateOrCreate(
            ['email' => $profile['email']],
            [
                'name'     => $profile['name'],
                // Set unguessable password since auth is handled by CentraFlow
                'password' => bcrypt(Str::random(32)),
            ]
        );

        // Optional: Save CentraFlow UUID or role if column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'centraflow_uuid')) {
            $user->centraflow_uuid = $profile['uuid'];
            $user->save();
        }

        // 5. Authenticate user into local session
        Auth::login($user, true);

        // Store token in session if sub-system needs to call CentraFlow APIs
        $request->session()->put('centraflow_access_token', $accessToken);

        return redirect()->intended('/home');
    }
}
```

---

### Step 3: Add Config (`config/services.php`)

Optionally register the credentials under `config/services.php`:

```php
'centraflow' => [
    'host'          => env('CENTRAFLOW_HOST', 'http://localhost:8004'),
    'client_id'     => env('CENTRAFLOW_CLIENT_ID'),
    'client_secret' => env('CENTRAFLOW_CLIENT_SECRET'),
    'redirect_uri'  => env('CENTRAFLOW_REDIRECT_URI'),
    'scopes'        => env('CENTRAFLOW_SCOPES'),
],
```

---

### Step 4: Add SSO Button on Sub-System Login Blade View

Add the "Sign in with CentraFlow" button in the login view of each sub-system (e.g. `resources/views/auth/login.blade.php`):

```html
<div class="mt-4">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Or continue with</span>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('sso.login') }}" 
           class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition duration-150">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Sign in with CentraFlow SSO
        </a>
    </div>
</div>
```

---

## 5. Testing the End-to-End Authentication Flow

1. Ensure **CentraFlow** is running:
   ```bash
   cd /Users/alif/Desktop/Project/Github/CentraFlow/CentraFlow
   php artisan serve --port=8004
   ```
2. Navigate to your sub-system's login page (e.g., `http://localhost:8001/login`).
3. Click **"Sign in with CentraFlow SSO"**.
4. You will be redirected to `http://localhost:8004/oauth/authorize`.
5. Enter CentraFlow credentials:
   - **Email:** `hr.manager@centraflow.local` (or `superadmin@centraflow.local`)
   - **Password:** `password`
6. CentraFlow prompts for scope authorization (or auto-approves for first-party clients).
7. You are redirected back to `http://localhost:8001/auth/callback` and seamlessly logged in!

---

## 6. Global Session Management & Token Revocation

When an administrator revokes access:
- **Central Token Revocation**: Go to CentraFlow (`http://localhost:8004/admin/id-management`) and click **"Revoke Access"** for any client token.
- **Session Termination**: Terminating a session in CentraFlow's Active Sessions table marks the session dead immediately.
- **Sub-system Verification**: Sub-systems can optionally verify token validity at any time by calling `GET http://localhost:8004/api/v1/me` using the stored bearer token.

---

## 7. Full Central Single Sign-Out (Option 2)

To ensure that signing out of a sub-system (such as **HRMS**) also clears the user's master session in **CentraFlow**, configure the sub-system's logout method to redirect through CentraFlow's central logout endpoint:

### HRMS Logout Implementation:
```php
// In HRMS (e.g. app/Http/Controllers/Auth/LoginController.php or AuthenticatedSessionController.php)

public function destroy(Request $request)
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Option 2: Full Central Single Sign-Out via CentraFlow
    $centraflowLogout = rtrim(env('CENTRAFLOW_HOST', 'http://localhost:8004'), '/') . '/logout';
    $postLogoutReturn = urlencode(url('/login'));

    return redirect()->away("{$centraflowLogout}?redirect_uri={$postLogoutReturn}");
}
```

### Flow Breakdown:
1. User clicks **"Log Out"** in HRMS (`:8001`).
2. HRMS clears its local session and cookies.
3. HRMS redirects the browser to `http://localhost:8004/logout?redirect_uri=http://localhost:8001/login`.
4. CentraFlow terminates the master session and revokes active cookie state.
5. CentraFlow immediately redirects the browser back to `http://localhost:8001/login`.
6. Both sessions are now 100% terminated simultaneously.

