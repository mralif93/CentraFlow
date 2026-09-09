@extends('layouts.admin')

@section('title', 'Users Management — CentraFlow Admin')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-8 animate__animated animate__fadeIn" x-cloak x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDetailModal: false,
    selectedUser: {},
    selectedAccess: { hrms: {}, payroll: {}, clinic: {} },
    editForm: {
        id: '',
        name: '',
        email: '',
        staff_id: '',
        employee_code: '',
        phone: '',
        department: '',
        job_title: '',
        designation: '',
        role: 'employee',
        role_ids: [],
        status: 'active',
        hrms_access: true,
        hrms_role: '',
        payroll_access: true,
        payroll_role: '',
        clinic_access: true,
        clinic_role: '',
        updateUrl: ''
    },
    openEdit(detail) {
        const user = detail.user;
        const access = detail.access || { hrms: {}, payroll: {}, clinic: {} };
        this.selectedUser = user;
        this.selectedAccess = access;
        const userRoleIds = (user.roles || []).map(r => r.id);

        this.editForm = {
            id: user.id,
            name: user.name || '',
            email: user.email || '',
            staff_id: user.staff_id || user.employee_code || '',
            employee_code: user.employee_code || user.staff_id || '',
            phone: user.phone || '',
            department: user.department || '',
            job_title: user.job_title || user.designation || '',
            designation: user.designation || user.job_title || '',
            role: user.role || 'employee',
            role_ids: userRoleIds,
            status: user.status || 'active',
            hrms_access: access && access.hrms ? access.hrms.allowed : true,
            hrms_role: user.hrms_role || '',
            payroll_access: access && access.payroll ? access.payroll.allowed : true,
            payroll_role: user.payroll_role || '',
            clinic_access: access && access.clinic ? access.clinic.allowed : true,
            clinic_role: user.clinic_role || '',
            updateUrl: '/admin/users/' + user.id
        };
        this.showEditModal = true;
    },
    openDetail(detail) {
        this.selectedUser = detail.user;
        this.selectedAccess = detail.access || { hrms: {}, payroll: {}, clinic: {} };
        this.showDetailModal = true;
    }
}">

    <!-- Flash Notifications (HRMS Component) -->
    @if (session('success'))
        <x-alert type="success" :dismissible="true">
            {{ session('success') }}
        </x-alert>
    @endif

    @if (session('error'))
        <x-alert type="danger" :dismissible="true">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- Page Header (HRMS Component) -->
    <x-page-header 
        title="Users Management" 
        subtitle="Provision and administer master user identities, RBAC security roles, and sub-system clearances."
        icon="bx-user-pin"
        badge="Master Directory"
        badgeVariant="indigo"
    >
        <x-slot:actions>
            <x-button 
                type="button" 
                @click="showCreateModal = true"
                variant="primary" 
                size="md" 
                icon="bx bx-plus"
                class="shadow-md shadow-indigo-600/30 cursor-pointer"
            >
                Create User
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Metric KPI Cards (HRMS x-stat-card) -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total Users" 
            :value="$users->total()" 
            icon="bx-group" 
            color="indigo" 
            change="Enterprise Identities"
            changeType="positive"
        />
        <x-stat-card 
            title="Active Users" 
            :value="$users->where('status', 'active')->count()" 
            icon="bx-user-check" 
            color="emerald" 
            change="Current Page Active"
            changeType="positive"
        />
        <x-stat-card 
            title="Assigned Roles" 
            :value="$roles->count()" 
            icon="bx-shield-quarter" 
            color="purple" 
            change="System Roles"
            changeType="neutral"
        />
        <x-stat-card 
            title="Federated Portals" 
            value="3 Modules" 
            icon="bx-layer" 
            color="blue" 
            change="HRMS, Payroll, CIS"
            changeType="neutral"
        />
    </div>

    <!-- Master Users Table Panel -->
    <div class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bx bx-user-check text-indigo-600 dark:text-indigo-400 text-lg"></i>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Master Directory Users</h2>
            </div>
            <span class="text-xs text-slate-400 font-mono">Showing {{ $users->count() }} of {{ $users->total() }} accounts</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[800px]">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3.5 whitespace-nowrap">User Identity</th>
                        <th class="p-3.5 whitespace-nowrap">Staff ID &amp; Contact</th>
                        <th class="p-3.5 whitespace-nowrap">Master Persona</th>
                        <th class="p-3.5 whitespace-nowrap">Microservice Clearances</th>
                        <th class="p-3.5 whitespace-nowrap">Status</th>
                        <th class="p-3.5 whitespace-nowrap">Joined</th>
                        <th class="p-3.5 text-right whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-sans">
                    @forelse ($users as $user)
                        @php $access = $user->getSubsystemAccess(); @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="p-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 font-bold flex items-center justify-center text-xs shadow-xs">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $user->name }}</div>
                                        <div class="text-slate-400 dark:text-slate-500 font-mono text-[10px]">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $user->staff_id ?? $user->employee_code ?? '—' }}</div>
                                <div class="text-slate-400 text-[10px]">{{ $user->phone ?? '—' }}</div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <div>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase font-mono
                                        {{ $user->role === 'superadmin' ? 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' : '' }}
                                        {{ $user->role === 'hr_manager' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : '' }}
                                        {{ $user->role === 'payroll_officer' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $user->role === 'finance_officer' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : '' }}
                                        {{ $user->role === 'employee' ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : '' }}
                                    ">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </div>
                                <div class="text-slate-400 text-[10px] mt-0.5">
                                    {{ $user->department ?? 'General' }} &bull; {{ $user->job_title ?? $user->designation ?? 'Staff' }}
                                </div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold {{ $access['hrms']['allowed'] ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through' }}" title="{{ $access['hrms']['allowed'] ? 'HRMS: ' . $access['hrms']['role'] : 'HRMS: Blocked' }}">
                                        HRMS
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold {{ $access['payroll']['allowed'] ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through' }}" title="{{ $access['payroll']['allowed'] ? 'Payroll: ' . $access['payroll']['role'] : 'Payroll: Blocked' }}">
                                        Payroll
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold {{ $access['clinic']['allowed'] ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through' }}" title="{{ $access['clinic']['allowed'] ? 'Clinic: ' . $access['clinic']['role'] : 'Clinic: Blocked' }}">
                                        Clinic
                                    </span>
                                </div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <x-badge :variant="$user->status === 'active' ? 'emerald' : 'rose'" size="sm" :dot="true">
                                    {{ strtoupper($user->status ?? 'active') }}
                                </x-badge>
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-slate-400 text-[11px]">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-right space-x-1">
                                <!-- Show / View Details -->
                                <x-circle-action-button 
                                    icon="bx bx-show" 
                                    variant="indigo" 
                                    size="sm" 
                                    title="View User Details"
                                    @click="openDetail({ user: {{ json_encode($user) }}, access: {{ json_encode($access) }} })"
                                />

                                <!-- Edit Identity -->
                                <x-circle-action-button 
                                    icon="bx bx-pencil" 
                                    variant="purple" 
                                    size="sm" 
                                    title="Edit User &amp; Clearances"
                                    @click="openEdit({ user: {{ json_encode($user) }}, access: {{ json_encode($access) }} })"
                                />

                                <!-- Revoke Sessions with Confirmation Component -->
                                <x-circle-action-button 
                                    icon="bx bx-power-off" 
                                    variant="amber" 
                                    size="sm" 
                                    type="button"
                                    title="Kill all active sessions &amp; tokens" 
                                    onclick="openConfirmDialog({
                                        name: 'revoke-sessions',
                                        action: '{{ route('admin.users.revoke-sessions', $user->id) }}',
                                        method: 'POST',
                                        title: 'Revoke User Sessions?',
                                        message: 'Are you sure you want to terminate all active sessions and bearer tokens for {{ addslashes($user->name) }}?',
                                        confirmText: 'Revoke Sessions'
                                    })"
                                />

                                <!-- Delete User with Confirmation Component -->
                                @if ($user->id !== auth()->id())
                                    <x-circle-action-button 
                                        icon="bx bx-trash" 
                                        variant="rose" 
                                        size="sm" 
                                        type="button"
                                        title="Delete User" 
                                        onclick="openConfirmDialog({
                                            name: 'delete-user',
                                            action: '{{ route('admin.users.destroy', $user->id) }}',
                                            method: 'DELETE',
                                            title: 'Delete User Account?',
                                            message: 'Are you sure you want to delete user {{ addslashes($user->name) }} ({{ $user->email }})? All associated session credentials and clearances will be permanently erased.',
                                            confirmText: 'Delete User'
                                        })"
                                    />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <i class="bx bx-user-x text-3xl mb-1 block"></i>
                                No users found in directory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$users" />
    </div>

    <!-- ================= MODAL: CREATE USER ================= -->
    <template x-teleport="body">
        <div x-show="showCreateModal" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
            <!-- Full-screen backdrop with rich glassmorphic blur covering entire viewport, sidebar, and header -->
            <div 
                x-show="showCreateModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity" 
                @click="showCreateModal = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="showCreateModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-xl shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto z-10"
            >
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base font-bold">
                            <i class="bx bx-user-plus"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Create User</h3>
                            <p class="text-[11px] text-slate-400">Provision unified identity &amp; microservice clearance.</p>
                        </div>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Full Legal Name</label>
                                <input type="text" name="name" required placeholder="Johnathan Doe" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                                <input type="email" name="email" required placeholder="john@centraflow.local" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Staff ID / Employee Code</label>
                                <input type="text" name="employee_code" placeholder="STF-001 or EMP-001" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                                <input type="text" name="phone" placeholder="+60123456789" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                                <input type="text" name="department" placeholder="Engineering, HR, Finance..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Job Title / Designation</label>
                                <input type="text" name="designation" placeholder="Lead Architect, Specialist..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Master Persona</label>
                                <select name="role" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                    <option value="employee">Standard Employee</option>
                                    <option value="hr_manager">HR Administrator</option>
                                    <option value="payroll_officer">Payroll Officer</option>
                                    <option value="finance_officer">Finance Director</option>
                                    <option value="superadmin">Super Administrator</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Initial Password</label>
                                <input type="password" name="password" required placeholder="••••••••" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <!-- Role Assignments (RBAC) -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Enterprise RBAC Roles
                            </label>
                            <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/60 max-h-32 overflow-y-auto">
                                @foreach($roles ?? [] as $roleItem)
                                    <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer text-xs">
                                        <input type="checkbox" name="role_ids[]" value="{{ $roleItem->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-slate-800 dark:text-slate-200 text-[11px]">{{ $roleItem->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Sub-System Clearance Settings -->
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sub-System Clearance Settings</div>
                        
                        <div class="space-y-2.5">
                            <!-- HRMS Module Clearance -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="hrms_access" value="1" id="hrms_access" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="hrms_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        HRMS Module (:8001)
                                    </label>
                                </div>
                                <select name="hrms_role" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="">Default (Auto)</option>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="HR Administrator">HR Administrator</option>
                                    <option value="Department Manager">Department Manager</option>
                                    <option value="Employee">Employee</option>
                                </select>
                            </div>

                            <!-- Payroll Suite Clearance -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="payroll_access" value="1" id="payroll_access" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="payroll_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        Payroll Suite (:8002)
                                    </label>
                                </div>
                                <select name="payroll_role" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="">Default (Auto)</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="payroll_officer">Payroll Officer</option>
                                    <option value="finance_director">Finance Director</option>
                                    <option value="auditor">Auditor (View Only)</option>
                                </select>
                            </div>

                            <!-- Clinic Invoicing Clearance -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="clinic_access" value="1" id="clinic_access" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="clinic_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        Clinic Invoicing (:8003)
                                    </label>
                                </div>
                                <select name="clinic_role" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="">Default (Auto)</option>
                                    <option value="admin">Admin (Doctor / Lead)</option>
                                    <option value="doctor">Medical Doctor</option>
                                    <option value="accountant">Clinic Accountant</option>
                                    <option value="receptionist">Receptionist / Frontdesk</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 cursor-pointer transition">
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </template>

    <!-- ================= MODAL: EDIT USER ================= -->
    <template x-teleport="body">
    <div x-show="showEditModal" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Full-screen backdrop with rich glassmorphic blur covering entire viewport, sidebar, and header -->
        <div 
            x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity" 
            @click="showEditModal = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="showEditModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-xl shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto z-10"
            >
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base font-bold">
                            <i class="bx bx-pencil"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Edit User Profile</h3>
                            <p class="text-[11px] text-slate-400 font-mono" x-text="editForm.email"></p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>

                <form method="POST" :action="editForm.updateUrl" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Full Legal Name</label>
                                <input type="text" name="name" x-model="editForm.name" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" x-model="editForm.status" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="resigned">Resigned</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Staff ID / Employee Code</label>
                                <input type="text" name="employee_code" x-model="editForm.employee_code" placeholder="STF-001 or EMP-001" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                                <input type="text" name="phone" x-model="editForm.phone" placeholder="+60123456789" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                                <input type="text" name="department" x-model="editForm.department" placeholder="Engineering, HR, Finance..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Job Title / Designation</label>
                                <input type="text" name="designation" x-model="editForm.designation" placeholder="Lead Architect, Specialist..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Master Persona</label>
                            <select name="role" x-model="editForm.role" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="employee">Standard Employee</option>
                                <option value="hr_manager">HR Administrator</option>
                                <option value="payroll_officer">Payroll Officer</option>
                                <option value="finance_officer">Finance Director</option>
                                <option value="superadmin">Super Administrator</option>
                            </select>
                        </div>

                        <!-- Role Assignments (RBAC) -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Enterprise RBAC Roles
                            </label>
                            <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/60 max-h-32 overflow-y-auto">
                                @foreach($roles ?? [] as $roleItem)
                                    <label class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer text-xs">
                                        <input type="checkbox" name="role_ids[]" value="{{ $roleItem->id }}" :checked="editForm.role_ids && editForm.role_ids.includes({{ $roleItem->id }})" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="font-medium text-slate-800 dark:text-slate-200 text-[11px]">{{ $roleItem->display_name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Sub-System Clearance Controls -->
                    <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <div class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sub-System Clearance Settings</div>
                        
                        <div class="space-y-2.5">
                            <!-- HRMS Clearance -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="hrms_access" value="1" x-model="editForm.hrms_access" id="edit_hrms_access" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="edit_hrms_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        HRMS Module (:8001)
                                    </label>
                                </div>
                                <select name="hrms_role" x-model="editForm.hrms_role" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="">Default (Auto)</option>
                                    <option value="Super Admin">Super Admin</option>
                                    <option value="HR Administrator">HR Administrator</option>
                                    <option value="Department Manager">Department Manager</option>
                                    <option value="Employee">Employee</option>
                                </select>
                            </div>

                            <!-- Payroll Clearance -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="payroll_access" value="1" x-model="editForm.payroll_access" id="edit_payroll_access" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="edit_payroll_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        Payroll Suite (:8002)
                                    </label>
                                </div>
                                <select name="payroll_role" x-model="editForm.payroll_role" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="">Default (Auto)</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="payroll_officer">Payroll Officer</option>
                                    <option value="finance_director">Finance Director</option>
                                    <option value="auditor">Auditor (View Only)</option>
                                </select>
                            </div>

                            <!-- Clinic Invoicing Clearance -->
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="clinic_access" value="1" x-model="editForm.clinic_access" id="edit_clinic_access" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="edit_clinic_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                        Clinic Invoicing (:8003)
                                    </label>
                                </div>
                                <select name="clinic_role" x-model="editForm.clinic_role" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300">
                                    <option value="">Default (Auto)</option>
                                    <option value="admin">Admin (Doctor / Lead)</option>
                                    <option value="doctor">Medical Doctor</option>
                                    <option value="accountant">Clinic Accountant</option>
                                    <option value="receptionist">Receptionist / Frontdesk</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30 cursor-pointer transition">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </template>

    <!-- ================= MODAL: VIEW USER DETAILS ================= -->
    <template x-teleport="body">
    <div x-show="showDetailModal" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Full-screen backdrop with rich glassmorphic blur covering entire viewport, sidebar, and header -->
        <div 
            x-show="showDetailModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity" 
            @click="showDetailModal = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="showDetailModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-2xl shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto z-10"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg font-black shadow-xs">
                            <span x-text="(selectedUser.name || 'CF').substring(0, 2).toUpperCase()"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-black text-slate-900 dark:text-white" x-text="selectedUser.name"></h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase font-mono"
                                    :class="{
                                        'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': selectedUser.status === 'active',
                                        'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': selectedUser.status === 'suspended',
                                        'bg-slate-100 dark:bg-slate-800 text-slate-500': selectedUser.status !== 'active' && selectedUser.status !== 'suspended'
                                    }"
                                    x-text="selectedUser.status || 'active'">
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-mono" x-text="selectedUser.email"></p>
                        </div>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>

                <!-- Profile Content Grid -->
                <div class="space-y-4 text-xs">
                    <!-- Global Identity Credentials -->
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase block tracking-wider mb-2">Account Credentials</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/80">
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Staff / Employee Code</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedUser.employee_code || selectedUser.staff_id || 'Not Assigned'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Phone Contact</span>
                                <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.phone || '—'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Master Persona</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400 uppercase font-mono" x-text="selectedUser.role"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Department</span>
                                <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.department || 'General'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Designation / Title</span>
                                <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.designation || selectedUser.job_title || 'Staff'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Account Created</span>
                                <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.created_at ? new Date(selectedUser.created_at).toLocaleDateString() : '—'"></span>
                            </div>
                            <div class="col-span-2 sm:col-span-3 pt-2 border-t border-slate-200/50 dark:border-slate-800/50">
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Universal Identity UUID</span>
                                <span class="font-mono text-[11px] text-slate-600 dark:text-slate-400 select-all" x-text="selectedUser.uuid || 'N/A'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Enterprise Security Roles & Permissions -->
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase block tracking-wider mb-2">Assigned RBAC Security Roles</span>
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/80 space-y-2">
                            <template x-if="selectedUser.roles && selectedUser.roles.length > 0">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="r in selectedUser.roles" :key="r.id">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center gap-1">
                                            <i class="bx bx-badge-check"></i>
                                            <span x-text="r.display_name || r.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!selectedUser.roles || selectedUser.roles.length === 0">
                                <p class="text-slate-400 italic text-[11px]">No custom RBAC roles linked (Governed by Master Persona: <span class="font-mono font-bold" x-text="selectedUser.role"></span>).</p>
                            </template>
                        </div>
                    </div>

                    <!-- Security & Login Activity -->
                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase block tracking-wider mb-2">Security &amp; Login Activity</span>
                        <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/80">
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Last Login Timestamp</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200" x-text="selectedUser.last_login_at ? new Date(selectedUser.last_login_at).toLocaleString() : 'No recorded login yet'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block font-bold uppercase">Last Login IP Address</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200" x-text="selectedUser.last_login_ip || 'N/A'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Sub-System Clearance & Local Roles -->
                    <div class="space-y-2 pt-1">
                        <span class="text-slate-400 text-[10px] font-bold uppercase block tracking-wider">Federated Microservice Clearances</span>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-300 block">HRMS Module (:8001)</span>
                                    <span class="text-[10px] text-slate-400">Human Resources, Attendance &amp; Leave Approval</span>
                                </div>
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold" 
                                    :class="selectedAccess && selectedAccess.hrms && selectedAccess.hrms.allowed ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through'" 
                                    x-text="selectedAccess && selectedAccess.hrms && selectedAccess.hrms.allowed ? (selectedAccess.hrms.role || 'Authorized') : 'Access Denied'">
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-300 block">Payroll Suite (:8002)</span>
                                    <span class="text-[10px] text-slate-400">Salary Calculation, Statutory Contributions &amp; Bank Files</span>
                                </div>
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold" 
                                    :class="selectedAccess && selectedAccess.payroll && selectedAccess.payroll.allowed ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through'" 
                                    x-text="selectedAccess && selectedAccess.payroll && selectedAccess.payroll.allowed ? (selectedAccess.payroll.role || 'Authorized') : 'Access Denied'">
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-300 block">Clinic Invoicing (:8003)</span>
                                    <span class="text-[10px] text-slate-400">Panel Invoicing, Medical Claims &amp; Billing Records</span>
                                </div>
                                <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold" 
                                    :class="selectedAccess && selectedAccess.clinic && selectedAccess.clinic.allowed ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 line-through'" 
                                    x-text="selectedAccess && selectedAccess.clinic && selectedAccess.clinic.allowed ? (selectedAccess.clinic.role || 'Authorized') : 'Access Denied'">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>

</div>
@endsection

@push('modals')
<x-confirm-dialog 
    name="delete-user" 
    title="Delete User Account?" 
    message="This action cannot be undone. All associated session credentials and clearances will be permanently erased." 
    confirmText="Delete User" 
    variant="danger" 
/>

<x-confirm-dialog 
    name="revoke-sessions" 
    title="Revoke User Sessions?" 
    message="Are you sure you want to terminate all active sessions and tokens for this user?" 
    confirmText="Revoke Sessions" 
    variant="warning" 
/>
@endpush

