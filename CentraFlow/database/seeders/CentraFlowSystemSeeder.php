<?php

namespace Database\Seeders;

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
        // 1. Create Core Users with Unified Enterprise Attributes
        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@centraflow.local',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'staff_id' => 'ADM-001',
                'phone' => '+60123456780',
                'department' => 'Executive Office',
                'job_title' => 'Chief Technology Officer',
                'status' => 'active',
            ],
            [
                'name' => 'Sarah HR Manager',
                'email' => 'hrmanager@centraflow.local',
                'password' => Hash::make('password123'),
                'role' => 'hr_manager',
                'staff_id' => 'EMP-2026-0002',
                'phone' => '+60123456781',
                'department' => 'Human Resources',
                'job_title' => 'People Operations Lead',
                'status' => 'active',
            ],
            [
                'name' => 'Alex Payroll Officer',
                'email' => 'payroll@centraflow.local',
                'password' => Hash::make('password123'),
                'role' => 'payroll_officer',
                'staff_id' => 'SA-001',
                'phone' => '+60123456782',
                'department' => 'Finance & Payroll',
                'job_title' => 'Payroll Specialist',
                'status' => 'active',
            ],
            [
                'name' => 'Fiona Finance Officer',
                'email' => 'finance@centraflow.local',
                'password' => Hash::make('password123'),
                'role' => 'finance_officer',
                'staff_id' => 'FD-001',
                'phone' => '+60123456783',
                'department' => 'Finance & Billing',
                'job_title' => 'Billing Administrator',
                'status' => 'active',
            ],
            [
                'name' => 'John Employee',
                'email' => 'john.doe@centraflow.local',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'staff_id' => 'EMP-2026-0004',
                'phone' => '+60123456784',
                'department' => 'Product & Design',
                'job_title' => 'Senior UI/UX Designer',
                'status' => 'active',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }

        // 2. Register Sub-system OAuth Clients
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
