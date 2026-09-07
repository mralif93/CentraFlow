<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CentraFlowAuthAndScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_automatically_generates_uuid_on_creation(): void
    {
        $user = User::create([
            'name' => 'Test Employee',
            'email' => 'test.' . uniqid() . '@centraflow.local',
            'password' => 'password123',
            'role' => 'employee',
        ]);

        $this->assertNotEmpty($user->uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $user->uuid);
    }

    public function test_unauthenticated_request_to_me_endpoint_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_retrieve_identity_payload(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@centraflow.local'],
            ['name' => 'Super Admin', 'password' => 'password123', 'role' => 'superadmin']
        );

        Passport::actingAs($user, ['hrms:read', 'payroll:run']);

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'uuid' => $user->uuid,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
    }

    public function test_scope_enforcement_allows_authorized_scope(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'payroll@centraflow.local'],
            ['name' => 'Payroll Officer', 'password' => 'password123', 'role' => 'payroll_officer']
        );

        // Grant payroll:run scope
        Passport::actingAs($user, ['payroll:run']);

        $response = $this->getJson('/api/v1/payroll/batch-status');
        $response->assertStatus(200)
            ->assertJson(['message' => 'Authorized for Payroll run']);
    }

    public function test_scope_enforcement_denies_unauthorized_scope(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'hrmanager@centraflow.local'],
            ['name' => 'HR Manager', 'password' => 'password123', 'role' => 'hr_manager']
        );

        // Grant only HRMS scope
        Passport::actingAs($user, ['hrms:read']);

        // Attempt to call payroll endpoint
        $response = $this->getJson('/api/v1/payroll/batch-status');
        $response->assertStatus(403);
    }
}
