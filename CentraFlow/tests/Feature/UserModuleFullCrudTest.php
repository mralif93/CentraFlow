<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModuleFullCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Root Superadmin',
            'email' => 'superadmin@centraflow.local',
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: SHOW (Index view listing all users with details)
     */
    public function test_admin_can_show_user_list_with_all_user_details(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@centraflow.local',
            'staff_id' => 'STF-007',
            'phone' => '+60111222333',
            'department' => 'Operations',
            'job_title' => 'Chief Coordinator',
            'role' => 'hr_manager',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.id-management'));

        $response->assertStatus(200);
        $response->assertSee('Alice Johnson');
        $response->assertSee('alice@centraflow.local');
        $response->assertSee('STF-007');
        $response->assertSee('+60111222333');
        $response->assertSee('Operations');
        $response->assertSee('Chief Coordinator');
    }

    /**
     * Test 2: CREATE (Store a new user with full details & sub-system roles)
     */
    public function test_admin_can_create_user_with_full_credentials(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.id-management.store-user'), [
            'name' => 'Bob Builder',
            'email' => 'bob@centraflow.local',
            'staff_id' => 'STF-1002',
            'phone' => '+60199887766',
            'department' => 'Engineering',
            'job_title' => 'Senior Developer',
            'role' => 'payroll_officer',
            'password' => 'BobPass2026!',
            'status' => 'active',
            'hrms_access' => '1',
            'hrms_role' => 'Department Manager',
            'payroll_access' => '1',
            'payroll_role' => 'payroll_officer',
            'clinic_access' => '1',
            'clinic_role' => 'receptionist',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Bob Builder',
            'email' => 'bob@centraflow.local',
            'staff_id' => 'STF-1002',
            'phone' => '+60199887766',
            'department' => 'Engineering',
            'job_title' => 'Senior Developer',
            'role' => 'payroll_officer',
            'status' => 'active',
            'hrms_access' => 1,
            'hrms_role' => 'Department Manager',
            'payroll_access' => 1,
            'payroll_role' => 'payroll_officer',
            'clinic_access' => 1,
            'clinic_role' => 'receptionist',
        ]);
    }

    /**
     * Test 3: EDIT (Update existing user profile and clearances)
     */
    public function test_admin_can_edit_and_update_existing_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Charlie Chaplin',
            'email' => 'charlie@centraflow.local',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.id-management.update-user', $user), [
            'name' => 'Sir Charles Spencer Chaplin',
            'role' => 'finance_officer',
            'staff_id' => 'STF-9999',
            'phone' => '+60122334455',
            'department' => 'Finance & Strategy',
            'job_title' => 'Senior Director of Finance',
            'status' => 'suspended',
            'hrms_access' => '0',
            'payroll_access' => '1',
            'payroll_role' => 'finance_director',
            'clinic_access' => '1',
            'clinic_role' => 'accountant',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Sir Charles Spencer Chaplin',
            'role' => 'finance_officer',
            'staff_id' => 'STF-9999',
            'phone' => '+60122334455',
            'department' => 'Finance & Strategy',
            'job_title' => 'Senior Director of Finance',
            'status' => 'suspended',
            'hrms_access' => 0,
            'payroll_access' => 1,
            'payroll_role' => 'finance_director',
            'clinic_access' => 1,
            'clinic_role' => 'accountant',
        ]);
    }

    /**
     * Test 4: DELETE (Admin can delete a user account)
     */
    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Temporary Staff',
            'email' => 'temp@centraflow.local',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.id-management.destroy-user', $user));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test 5: DELETE GUARD (Admin cannot delete themselves)
     */
    public function test_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.id-management.destroy-user', $this->admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }
}
