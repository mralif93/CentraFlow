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

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

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
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Bob Builder',
            'email' => 'bob@centraflow.local',
            'staff_id' => 'EMP-5555',
            'phone' => '+60199887766',
            'department' => 'Construction & Facilities',
            'job_title' => 'Project Lead',
            'role' => 'employee',
            'password' => 'BobCanFixIt2026!',
            'status' => 'active',
            'hrms_access' => '1',
            'hrms_role' => 'supervisor',
            'payroll_access' => '1',
            'payroll_role' => 'employee',
            'clinic_access' => '1',
            'clinic_role' => 'patient',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'bob@centraflow.local',
            'staff_id' => 'EMP-5555',
            'phone' => '+60199887766',
            'department' => 'Construction & Facilities',
            'job_title' => 'Project Lead',
            'status' => 'active',
            'hrms_access' => 1,
            'payroll_access' => 1,
            'clinic_access' => 1,
        ]);
    }

    /**
     * Test 3: EDIT & UPDATE (Update all user fields and clearances)
     */
    public function test_admin_can_edit_and_update_existing_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Charlie Chaplin',
            'email' => 'charlie@centraflow.local',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
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

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

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
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }
}
