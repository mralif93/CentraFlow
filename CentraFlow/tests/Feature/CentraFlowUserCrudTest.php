<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentraFlowUserCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Controller',
            'email' => 'admin@centraflow.local',
            'role' => 'superadmin',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_user_management_screen(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Users Management');
        $response->assertSee($this->admin->email);
    }

    public function test_admin_can_create_user_with_roles_and_subsystem_clearances(): void
    {
        $role = Role::firstOrCreate(['name' => 'hr_manager'], [
            'display_name' => 'HR Administrator',
            'is_system' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Michael Scott',
            'email' => 'mscott@centraflow.local',
            'employee_code' => 'EMP-1001',
            'phone' => '+60123456789',
            'department' => 'Management',
            'designation' => 'Regional Manager',
            'role' => 'hr_manager',
            'role_ids' => [$role->id],
            'password' => 'ScrantonPass2026!',
            'status' => 'active',
            'hrms_access' => '1',
            'hrms_role' => 'HR Administrator',
            'payroll_access' => '1',
            'payroll_role' => 'payroll_officer',
            'clinic_access' => '0',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'mscott@centraflow.local',
            'employee_code' => 'EMP-1001',
            'designation' => 'Regional Manager',
            'role' => 'hr_manager',
            'status' => 'active',
            'hrms_access' => 1,
            'clinic_access' => 0,
        ]);

        $user = User::where('email', 'mscott@centraflow.local')->first();
        $this->assertTrue($user->roles()->where('name', 'hr_manager')->exists());
    }

    public function test_admin_can_update_user_profile_and_access(): void
    {
        $user = User::factory()->create([
            'name' => 'Dwight Schrute',
            'email' => 'dschrute@centraflow.local',
            'role' => 'employee',
            'status' => 'active',
        ]);

        $role = Role::firstOrCreate(['name' => 'payroll_officer'], [
            'display_name' => 'Payroll Officer',
            'is_system' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
            'name' => 'Dwight K. Schrute',
            'role' => 'payroll_officer',
            'employee_code' => 'EMP-999',
            'department' => 'Sales',
            'designation' => 'Assistant to Regional Manager',
            'status' => 'active',
            'role_ids' => [$role->id],
            'payroll_access' => '1',
            'payroll_role' => 'super_admin',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Dwight K. Schrute',
            'role' => 'payroll_officer',
            'employee_code' => 'EMP-999',
            'designation' => 'Assistant to Regional Manager',
        ]);

        $this->assertTrue($user->fresh()->roles()->where('name', 'payroll_officer')->exists());
    }

    public function test_admin_can_delete_user_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Toby Flenderson',
            'email' => 'tflenderson@centraflow.local',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }
}
