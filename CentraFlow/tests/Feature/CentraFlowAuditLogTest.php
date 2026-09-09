<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentraFlowAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'auditadmin@centraflow.local',
            'role' => 'superadmin',
        ]);
    }

    public function test_guest_cannot_access_audit_logs(): void
    {
        $response = $this->get(route('admin.audit-logs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_audit_logs_dashboard(): void
    {
        AuditLog::record('auth.login.success', 'Test user logged in.', 'auth', ['user' => 'admin']);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Activity Audit Trail');
        $response->assertSee('auth.login.success');
        $response->assertSee('Test user logged in.');
    }

    public function test_audit_logs_can_be_filtered_by_category_and_search(): void
    {
        AuditLog::record('auth.login.success', 'Admin logged in', 'auth');
        AuditLog::record('user.created', 'New staff member registered', 'user_management');

        // Filter by user_management category
        $responseCategory = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', ['category' => 'user_management']));
        $responseCategory->assertStatus(200);
        $responseCategory->assertSee('user.created');
        $responseCategory->assertDontSee('auth.login.success');

        // Search by keyword
        $responseSearch = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', ['search' => 'registered']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('New staff member registered');
    }

    public function test_audit_logs_can_be_exported_to_csv(): void
    {
        AuditLog::record('security.alert', 'High security event recorded', 'security', ['risk' => 'critical']);

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Log ID', $content);
        $this->assertStringContainsString('Timestamp', $content);
        $this->assertStringContainsString('Event', $content);
        $this->assertStringContainsString('Category', $content);
        $this->assertStringContainsString('Description', $content);
        $this->assertStringContainsString('security.alert', $content);
        $this->assertStringContainsString('High security event recorded', $content);
    }

    public function test_audit_log_is_recorded_when_user_identity_is_created_and_updated(): void
    {
        // 1. Create a user
        $responseCreate = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Alice Auditor',
            'email' => 'alice@centraflow.local',
            'password' => 'password123',
            'role' => 'hr_manager',
            'department' => 'People Operations',
            'hrms_access' => true,
        ]);

        $responseCreate->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.created',
            'category' => 'user_management',
        ]);

        $createdUser = User::where('email', 'alice@centraflow.local')->firstOrFail();

        // 2. Update user
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.users.update', $createdUser), [
            'name' => 'Alice Senior Auditor',
            'role' => 'hr_manager',
            'department' => 'Governance',
        ]);

        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.updated',
            'category' => 'user_management',
        ]);

        // 3. Delete user
        $responseDelete = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $createdUser));
        $responseDelete->assertRedirect();
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user.deleted',
            'category' => 'user_management',
        ]);
    }
}
