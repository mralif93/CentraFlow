<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;

class CentraFlowSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Core RBAC Roles
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Administrator',
                'description' => 'Unrestricted access across identity and all enterprise modules',
                'is_system' => true,
            ],
            [
                'name' => 'hr_manager',
                'display_name' => 'HR Administrator',
                'description' => 'Full administration of employees, leaves, attendance, and recruitment',
                'is_system' => true,
            ],
            [
                'name' => 'payroll_officer',
                'display_name' => 'Payroll Officer',
                'description' => 'Payroll runs, calculations, approval, and statutory filings',
                'is_system' => true,
            ],
            [
                'name' => 'finance_officer',
                'display_name' => 'Finance Director',
                'description' => 'Banking, claims, financial statements, and clinical invoicing',
                'is_system' => true,
            ],
            [
                'name' => 'employee',
                'display_name' => 'Standard Employee',
                'description' => 'Self-service portal, payslips, leaves, and attendance',
                'is_system' => true,
            ],
        ];

        $roleModels = [];
        foreach ($roles as $r) {
            $roleModels[$r['name']] = Role::updateOrCreate(['name' => $r['name']], $r);
        }

        // 2. Create Core Users with Unified Enterprise Attributes & Attach Roles
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@centraflow.local',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'staff_id' => 'EMP-0001',
                'employee_code' => 'EMP-0001',
                'phone' => '+60123456780',
                'department' => 'Executive Office',
                'job_title' => 'Chief Technology Officer',
                'status' => 'active',
            ],
            [
                'name' => 'Sarah HR Manager',
                'email' => 'hrmanager@centraflow.local',
                'password' => Hash::make('password'),
                'role' => 'hr_manager',
                'staff_id' => 'EMP-0002',
                'employee_code' => 'EMP-0002',
                'phone' => '+60123456781',
                'department' => 'Human Resources',
                'job_title' => 'People Operations Lead',
                'status' => 'active',
            ],
            [
                'name' => 'Alex Payroll Officer',
                'email' => 'payroll@centraflow.local',
                'password' => Hash::make('password'),
                'role' => 'payroll_officer',
                'staff_id' => 'EMP-0003',
                'employee_code' => 'EMP-0003',
                'phone' => '+60123456782',
                'department' => 'Finance & Payroll',
                'job_title' => 'Payroll Specialist',
                'status' => 'active',
            ],
            [
                'name' => 'Fiona Finance Officer',
                'email' => 'finance@centraflow.local',
                'password' => Hash::make('password'),
                'role' => 'finance_officer',
                'staff_id' => 'EMP-0004',
                'employee_code' => 'EMP-0004',
                'phone' => '+60123456783',
                'department' => 'Finance & Billing',
                'job_title' => 'Billing Administrator',
                'status' => 'active',
            ],
            [
                'name' => 'John Employee',
                'email' => 'john.doe@centraflow.local',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'staff_id' => 'EMP-0005',
                'employee_code' => 'EMP-0005',
                'phone' => '+60123456784',
                'department' => 'Product & Design',
                'job_title' => 'Senior UI/UX Designer',
                'status' => 'active',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(['email' => $data['email']], $data);
            if (isset($roleModels[$data['role']])) {
                $user->roles()->syncWithoutDetaching([$roleModels[$data['role']]->id]);
            }
        }

        // 3. Register Sub-system OAuth Clients
        $clients = [
            [
                'id' => '9d12a101-0001-4000-8000-000000000001',
                'name' => 'HRMS Service Client',
                'secret' => 'hrms_secret_centraflow_2026',
                'redirect_uris' => ['http://localhost:8001/auth/callback'],
                'grant_types' => ['authorization_code', 'client_credentials'],
                'revoked' => false,
            ],
            [
                'id' => '9d12a101-0002-4000-8000-000000000002',
                'name' => 'Payroll Service Client',
                'secret' => 'payroll_secret_centraflow_2026',
                'redirect_uris' => ['http://localhost:8002/auth/callback'],
                'grant_types' => ['authorization_code', 'client_credentials'],
                'revoked' => false,
            ],
            [
                'id' => '9d12a101-0003-4000-8000-000000000003',
                'name' => 'Clinic Invoice Service Client',
                'secret' => 'invoice_secret_centraflow_2026',
                'redirect_uris' => ['http://localhost:8003/auth/callback'],
                'grant_types' => ['authorization_code', 'client_credentials'],
                'revoked' => false,
            ],
        ];

        foreach ($clients as $clientData) {
            Client::updateOrCreate(
                ['id' => $clientData['id']],
                $clientData
            );
        }
    }
}
