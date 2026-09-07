<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CentraFlowIdManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_id_management_dashboard(): void
    {
        $response = $this->get('/admin/id-management');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_view_admin_overview(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Operations &amp; Microservice Governance', false);
        $response->assertSee('Master Identity Directory');
        $response->assertSee('Active Web Sessions');
    }

    public function test_authenticated_admin_can_view_id_management_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($admin)->get('/admin/id-management');
        $response->assertStatus(200);
        $response->assertSee('Identity &amp; Session Governance', false);
        $response->assertSee('Master Identities');
        $response->assertSee('Active Web Sessions');
    }

    public function test_admin_can_create_new_master_identity(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($admin)->post('/admin/id-management/users', [
            'name' => 'Dr. Adam Specialist',
            'email' => 'adam.specialist@centraflow.local',
            'password' => 'SecurePass2026!',
            'role' => 'finance_officer',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'adam.specialist@centraflow.local',
            'role' => 'finance_officer',
        ]);
    }

    public function test_admin_can_terminate_a_web_session(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $sessionId = 'test_session_token_' . uniqid();
        DB::table('sessions')->insert([
            'id' => $sessionId,
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 Test Agent',
            'payload' => 'dummy_payload',
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($admin)->post("/admin/id-management/sessions/{$sessionId}/revoke");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('sessions', [
            'id' => $sessionId,
        ]);
    }

    public function test_admin_can_revoke_all_sessions_for_user(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $employee = User::factory()->create(['role' => 'employee']);

        DB::table('sessions')->insert([
            'id' => 'emp_sess_' . uniqid(),
            'user_id' => $employee->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Employee Device',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($admin)->post("/admin/id-management/users/{$employee->id}/revoke-sessions");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('sessions', [
            'user_id' => $employee->id,
        ]);
    }
}
