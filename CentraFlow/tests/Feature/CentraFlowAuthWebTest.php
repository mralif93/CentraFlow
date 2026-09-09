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

    public function test_authenticated_user_redirects_to_admin_dashboard_after_login(): void
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

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($employee);
    }

    public function test_dashboard_route_redirects_to_admin_dashboard(): void
    {
        $user = User::create([
            'name' => 'Sara Connor',
            'email' => 'sara@centraflow.local',
            'password' => Hash::make('secret1234'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/admin/dashboard');
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

    public function test_user_can_logout_with_federated_redirect_uri(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logout?redirect_uri=' . urlencode('http://localhost:8002/login'));

        $response->assertRedirect('http://localhost:8002/login');
        $this->assertGuest();
    }

    public function test_forgot_password_screen_renders_successfully(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Recover Password');
        $response->assertSee('Send Reset Instructions');
    }

    public function test_forgot_password_submission_dispatches_status(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@centraflow.local',
        ]);

        $response->assertSessionHas('status');
        $response->assertRedirect();
    }
}
