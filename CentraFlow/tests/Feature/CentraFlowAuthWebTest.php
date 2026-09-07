<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CentraFlowAuthWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('CentraFlow SSO');
        $response->assertSee('Sign In to CentraFlow');
    }

    public function test_admin_user_redirects_to_admin_dashboard_after_login(): void
    {
        $admin = User::create([
            'name' => 'HR Manager',
            'email' => 'hr@centraflow.local',
            'password' => Hash::make('secret1234'),
            'role' => 'hr_manager',
        ]);

        $response = $this->post('/login', [
            'email' => 'hr@centraflow.local',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_employee_user_redirects_to_launcher_after_login(): void
    {
        $employee = User::create([
            'name' => 'General Employee',
            'email' => 'emp@centraflow.local',
            'password' => Hash::make('secret1234'),
            'role' => 'employee',
        ]);

        $response = $this->post('/login', [
            'email' => 'emp@centraflow.local',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($employee);
    }

    public function test_user_can_register_new_master_account(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Clinician',
            'email' => 'clinician@centraflow.local',
            'role' => 'employee',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'clinician@centraflow.local',
            'role' => 'employee',
        ]);
        $this->assertAuthenticated();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
