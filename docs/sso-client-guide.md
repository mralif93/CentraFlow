# Sub-System SSO Client Integration Guide

This guide explains how each external project (**HRMS**, **Payroll**, and **Clinic Invoicing**) connects to **CentraFlow** (`http://localhost:8004` or `:8000`) for Single Sign-On (SSO).

---

## 1. Overview of the SSO Architecture

```
User Browser
    |
    | 1. Clicks "Sign in with CentraFlow"
    v
Sub-System (e.g. HRMS :8001)
    |
    | 2. Redirects to CentraFlow /oauth/authorize
    v
CentraFlow Hub (:8004)
    |
    | 3. User authenticates & approves scopes
    | 4. Redirects back with ?code=AUTH_CODE
    v
Sub-System (e.g. HRMS :8001)
    |
    | 5. Server-to-server POST to CentraFlow /oauth/token
    | 6. Exchanges code for Bearer Token
    | 7. Calls GET /api/v1/me to fetch user identity (user_uuid, role)
    v
Local Sub-System Session Created
```

---

## 2. Configuration for Each Sub-System

Add the following environment variables to each sub-system's `.env` file:

### A. HRMS (`.env` in `human-resources-management-system`)
```env
CENTRAFLOW_HOST=http://localhost:8004
CENTRAFLOW_CLIENT_ID=9d12a101-0001-4000-8000-000000000001
CENTRAFLOW_CLIENT_SECRET=hrms_secret_centraflow_2026
CENTRAFLOW_REDIRECT_URI=http://localhost:8001/auth/callback
CENTRAFLOW_SCOPES=hrms:read hrms:write
```

### B. Payroll (`.env` in `payroll-management-system`)
```env
CENTRAFLOW_HOST=http://localhost:8004
CENTRAFLOW_CLIENT_ID=9d12a101-0002-4000-8000-000000000002
CENTRAFLOW_CLIENT_SECRET=payroll_secret_centraflow_2026
CENTRAFLOW_REDIRECT_URI=http://localhost:8002/auth/callback
CENTRAFLOW_SCOPES=payroll:run payroll:read
```

### C. Clinic Invoicing (`.env` in `clinic-invoice-system`)
```env
CENTRAFLOW_HOST=http://localhost:8004
CENTRAFLOW_CLIENT_ID=9d12a101-0003-4000-8000-000000000003
CENTRAFLOW_CLIENT_SECRET=invoice_secret_centraflow_2026
CENTRAFLOW_REDIRECT_URI=http://localhost:8003/auth/callback
CENTRAFLOW_SCOPES=invoice:manage invoice:read
```

---

## 3. Sub-System Implementation (Drop-In Controller)

In each sub-system, implement these two routes:

```php
// routes/web.php in HRMS / Payroll / CIS
Route::get('/auth/redirect', [CentraFlowSsoClientController::class, 'redirect'])->name('sso.login');
Route::get('/auth/callback', [CentraFlowSsoClientController::class, 'callback']);
```

### Controller Implementation:

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CentraFlowSsoClientController extends Controller
{
    /**
     * Redirect the user to CentraFlow SSO authorization screen.
     */
    public function redirect(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        $query = http_build_query([
            'client_id' => env('CENTRAFLOW_CLIENT_ID'),
            'redirect_uri' => env('CENTRAFLOW_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => env('CENTRAFLOW_SCOPES'),
            'state' => $state,
        ]);

        return redirect(env('CENTRAFLOW_HOST') . '/oauth/authorize?' . $query);
    }

    /**
     * Handle the OAuth callback from CentraFlow.
     */
    public function callback(Request $request)
    {
        $state = $request->session()->pull('oauth_state');

        if (empty($state) || $state !== $request->query('state')) {
            abort(403, 'Invalid OAuth state.');
        }

        // Exchange authorization code for access token
        $response = Http::asForm()->post(env('CENTRAFLOW_HOST') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('CENTRAFLOW_CLIENT_ID'),
            'client_secret' => env('CENTRAFLOW_CLIENT_SECRET'),
            'redirect_uri' => env('CENTRAFLOW_REDIRECT_URI'),
            'code' => $request->query('code'),
        ]);

        if (! $response->successful()) {
            abort(401, 'Unable to exchange code with CentraFlow SSO.');
        }

        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];

        // Retrieve identity profile from CentraFlow master user directory
        $userResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get(env('CENTRAFLOW_HOST') . '/api/v1/me');

        if (! $userResponse->successful()) {
            abort(401, 'Failed to fetch user profile from CentraFlow.');
        }

        $profile = $userResponse->json('data');

        // Sync with local sub-system user table using master uuid
        $user = User::updateOrCreate(
            ['email' => $profile['email']],
            [
                'name' => $profile['name'],
                'uuid' => $profile['uuid'],
                // Store password hash as unguessable random string since auth is delegated to CentraFlow
                'password' => bcrypt(Str::random(32)),
            ]
        );

        Auth::login($user);

        // Store access token in session if needed for API calls
        $request->session()->put('centraflow_token', $accessToken);

        return redirect('/dashboard');
    }
}
```

---

## 4. Single Sign-Out (Global Logout)

When the user signs out from CentraFlow (`POST /logout`), their central session is invalidated.
Sub-systems can check session validity against `/api/v1/me` or redirect user to:

```
http://localhost:8004/login
```
