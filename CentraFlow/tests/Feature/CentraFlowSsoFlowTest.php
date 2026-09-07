<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Tests\TestCase;

class CentraFlowSsoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sso_client_credentials_grant_issues_valid_bearer_token(): void
    {
        $client = Client::create([
            'id' => '9d12a101-0001-4000-8000-000000000001',
            'name' => 'HRMS Service Client',
            'secret' => 'hrms_secret_centraflow_2026',
            'redirect_uris' => ['http://localhost:8001/auth/callback'],
            'grant_types' => ['client_credentials'],
            'revoked' => false,
        ]);

        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => 'hrms_secret_centraflow_2026',
            'scope' => 'hrms:read',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token_type',
                'expires_in',
                'access_token',
            ]);
    }

    public function test_sso_authorization_screen_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $client = Client::create([
            'id' => '9d12a101-0002-4000-8000-000000000002',
            'name' => 'Payroll Service Client',
            'secret' => 'payroll_secret_centraflow_2026',
            'redirect_uris' => ['http://localhost:8002/auth/callback'],
            'grant_types' => ['authorization_code'],
            'revoked' => false,
        ]);

        $response = $this->actingAs($user)->get('/oauth/authorize?' . http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => 'http://localhost:8002/auth/callback',
            'response_type' => 'code',
            'scope' => 'payroll:run',
            'state' => 'random_state_string',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Payroll Service Client');
        $response->assertSee('Single Sign-On Authorization');
    }
}
