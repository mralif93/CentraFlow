@extends('layouts.admin')

@section('title', 'Identities & Session Control — CentraFlow Admin')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-cloak x-data="{ 
    tab: 'identities', 
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
        phone: '',
        department: '',
        job_title: '',
        role: 'employee',
        status: 'active',
        hrms_access: true,
        hrms_role: '',
        payroll_access: true,
        payroll_role: '',
        clinic_access: true,
        clinic_role: '',
        updateUrl: ''
    },
    openEdit(user, access) {
        this.selectedUser = user;
        this.selectedAccess = access || { hrms: {}, payroll: {}, clinic: {} };
        this.editForm = {
            id: user.id,
            name: user.name || '',
            email: user.email || '',
            staff_id: user.staff_id || '',
            phone: user.phone || '',
            department: user.department || '',
            job_title: user.job_title || user.designation || '',
            role: user.role || 'employee',
            status: user.status || 'active',
            hrms_access: access && access.hrms ? access.hrms.allowed : true,
            hrms_role: user.hrms_role || '',
            payroll_access: access && access.payroll ? access.payroll.allowed : true,
            payroll_role: user.payroll_role || '',
            clinic_access: access && access.clinic ? access.clinic.allowed : true,
            clinic_role: user.clinic_role || '',
            updateUrl: '/admin/id-management/users/' + user.id
        };
        this.showEditModal = true;
    },
    openDetail(user, access) {
        this.selectedUser = user;
        this.selectedAccess = access || { hrms: {}, payroll: {}, clinic: {} };
        this.showDetailModal = true;
    }
}" class="space-y-8 animate__animated animate__fadeIn">

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
        title="Identity &amp; Session Governance" 
        subtitle="Manage master user profiles, inspect live browser sessions, and revoke OAuth access tokens."
        icon="bx-id-card"
        badge="Unified Directory"
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
                Create Master Identity
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Metric KPI Cards (HRMS x-stat-card) -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total Master Identities" 
            :value="$users->total()" 
            icon="bx-group" 
            color="indigo" 
            change="Enterprise Users"
            changeType="positive"
        />
        <x-stat-card 
            title="Active Web Sessions" 
            :value="$activeSessions->count()" 
            icon="bx-broadcast" 
            color="emerald" 
            change="Real-time Sessions"
            changeType="positive"
        />
        <x-stat-card 
            title="Active Microservices" 
            value="3 Portals" 
            icon="bx-layer" 
            color="purple" 
            change="HRMS, Payroll, CIS"
            changeType="neutral"
        />
        <x-stat-card 
            title="Active OAuth Tokens" 
            :value="$oauthTokens->count()" 
            icon="bx-key" 
            color="blue" 
            change="SSO / API Grants"
            changeType="neutral"
        />
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-800 flex space-x-8 text-sm">
        <button type="button" @click="tab = 'identities'" :class="tab === 'identities' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400 border-b-2 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2 cursor-pointer">
            <i class="bx bx-user-pin text-base"></i>
            <span>Master Identities</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $users->total() }}</span>
        </button>
        <button type="button" @click="tab = 'sessions'" :class="tab === 'sessions' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400 border-b-2 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2 cursor-pointer">
            <i class="bx bx-laptop text-base"></i>
            <span>Active Web Sessions</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">{{ $activeSessions->count() }}</span>
        </button>
        <button type="button" @click="tab = 'tokens'" :class="tab === 'tokens' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400 border-b-2 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2 cursor-pointer">
            <i class="bx bx-shield-quarter text-base"></i>
            <span>Issued OAuth Grants</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">{{ $oauthTokens->count() }}</span>
        </button>
    </div>

    <!-- Tab 1: Master Identities -->
    <div x-show="tab === 'identities'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
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
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $user->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                                    {{ $user->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-slate-400 text-[11px]">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-right space-x-1">
                                <!-- Show / View Details -->
                                <button 
                                    type="button" 
                                    @click="openDetail({{ json_encode($user) }}, {{ json_encode($access) }})"
                                    title="View Master Profile"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-slate-800 transition cursor-pointer"
                                >
                                    <i class="bx bx-show text-base"></i>
                                </button>

                                <!-- Edit Identity -->
                                <button 
                                    type="button" 
                                    @click="openEdit({{ json_encode($user) }}, {{ json_encode($access) }})"
                                    title="Edit Identity &amp; Clearances"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-slate-800 transition cursor-pointer"
                                >
                                    <i class="bx bx-pencil text-base"></i>
                                </button>

                                <!-- Revoke Sessions -->
                                <form method="POST" action="{{ route('admin.id-management.revoke-user-sessions', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" title="Kill all active sessions &amp; tokens" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-slate-800 transition cursor-pointer">
                                        <i class="bx bx-power-off text-base"></i>
                                    </button>
                                </form>

                                <!-- Delete Identity -->
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.id-management.destroy-user', $user->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete master identity \'{{ $user->name }}\'?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete User" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-slate-800 transition cursor-pointer">
                                            <i class="bx bx-trash text-base"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <i class="bx bx-user-x text-3xl mb-1 block"></i>
                                No master identities found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Tab 2: Active Web Sessions -->
    <div x-show="tab === 'sessions'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3.5 whitespace-nowrap">User</th>
                        <th class="p-3.5 whitespace-nowrap">IP Address</th>
                        <th class="p-3.5 whitespace-nowrap">Browser / Device</th>
                        <th class="p-3.5 whitespace-nowrap">Last Activity</th>
                        <th class="p-3.5 text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-sans">
                    @forelse ($activeSessions as $s)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition {{ $s->session_id === $currentSessionId ? 'bg-indigo-50/50 dark:bg-indigo-500/5' : '' }}">
                            <td class="p-3.5 whitespace-nowrap">
                                @if ($s->user_name)
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $s->user_name }}</div>
                                    <div class="text-slate-400 text-[10px]">{{ $s->user_email }}</div>
                                @else
                                    <span class="text-slate-400 italic">Unauthenticated Guest</span>
                                @endif
                            </td>
                            <td class="p-3.5 whitespace-nowrap font-mono text-slate-600 dark:text-slate-400">
                                {{ $s->ip_address }}
                            </td>
                            <td class="p-3.5 max-w-xs truncate text-slate-500 dark:text-slate-400" title="{{ $s->user_agent }}">
                                {{ $s->user_agent }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-slate-400">
                                {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}
                                @if ($s->session_id === $currentSessionId)
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Current</span>
                                @endif
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-right">
                                <form method="POST" action="{{ route('admin.id-management.revoke-session', $s->session_id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-bold text-[10px] transition cursor-pointer">
                                        Kill Session
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">
                                No active web sessions recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Issued OAuth Grants -->
    <div x-show="tab === 'tokens'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[700px]">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-3.5 whitespace-nowrap">Token ID</th>
                        <th class="p-3.5 whitespace-nowrap">Client Application</th>
                        <th class="p-3.5 whitespace-nowrap">Granted Scopes</th>
                        <th class="p-3.5 whitespace-nowrap">Created At</th>
                        <th class="p-3.5 whitespace-nowrap">Expires At</th>
                        <th class="p-3.5 text-right whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-sans">
                    @forelse ($oauthTokens as $t)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="p-3.5 whitespace-nowrap font-mono text-[10px] text-slate-500">
                                {{ substr($t->id, 0, 14) }}...
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $t->client->name ?? 'Internal App' }}</div>
                            </td>
                            <td class="p-3.5 whitespace-nowrap">
                                @if (is_array($t->scopes) && count($t->scopes) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($t->scopes as $sc)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">{{ $sc }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">Universal Scope</span>
                                @endif
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-slate-400">
                                {{ $t->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-slate-400">
                                {{ $t->expires_at ? $t->expires_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-right">
                                <form method="POST" action="{{ route('admin.id-management.revoke-oauth-token', $t->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-bold text-[10px] transition cursor-pointer">
                                        Revoke Token
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                No active OAuth grants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL: CREATE MASTER IDENTITY ================= -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div @click.away="showCreateModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-xl shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base font-bold">
                        <i class="bx bx-user-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Create Master Staff Identity</h3>
                        <p class="text-[11px] text-slate-400">Provision unified identity &amp; microservice clearance.</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <i class="bx bx-x text-xl"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.id-management.store-user') }}" class="space-y-4">
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
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Staff ID / Code</label>
                            <input type="text" name="staff_id" placeholder="STF-001" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                            <input type="text" name="phone" placeholder="+60123456789" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                            <input type="text" name="department" placeholder="Engineering, HR..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Job Title</label>
                            <input type="text" name="job_title" placeholder="Lead Architect..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
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
                            <input type="password" name="password" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
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
                                <input type="checkbox" name="hrms_access" value="1" checked id="create_hrms_access" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="create_hrms_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
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

                        <!-- Payroll Clearance -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center space-x-2.5">
                                <input type="checkbox" name="payroll_access" value="1" checked id="create_payroll_access" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="create_payroll_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
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
                                <input type="checkbox" name="clinic_access" value="1" checked id="create_clinic_access" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="create_clinic_access" class="text-xs font-bold text-slate-800 dark:text-slate-200">
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
                        Save Identity
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: EDIT MASTER IDENTITY ================= -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-xl shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base font-bold">
                        <i class="bx bx-pencil"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Edit Master Staff Identity</h3>
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
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Staff ID / Code</label>
                            <input type="text" name="staff_id" x-model="editForm.staff_id" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                            <input type="text" name="phone" x-model="editForm.phone" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                            <input type="text" name="department" x-model="editForm.department" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Job Title</label>
                            <input type="text" name="job_title" x-model="editForm.job_title" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
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
                        Update Identity
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: VIEW MASTER IDENTITY PROFILE ================= -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div @click.away="showDetailModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-lg shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base font-bold">
                        <i class="bx bx-user-pin"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white" x-text="selectedUser.name"></h3>
                        <p class="text-[11px] text-slate-400 font-mono" x-text="selectedUser.email"></p>
                    </div>
                </div>
                <button type="button" @click="showDetailModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                    <i class="bx bx-x text-xl"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/80">
                    <div>
                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Staff Identifier</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedUser.staff_id || 'Not Assigned'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Phone Contact</span>
                        <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.phone || '—'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Department</span>
                        <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.department || 'General'"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] block font-bold uppercase">Designation / Title</span>
                        <span class="text-slate-800 dark:text-slate-200" x-text="selectedUser.job_title || selectedUser.designation || 'Staff'"></span>
                    </div>
                </div>

                <div class="space-y-2 pt-2">
                    <span class="text-slate-400 text-[10px] font-bold uppercase block tracking-wider">Sub-System Clearance &amp; Local Roles</span>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                            <span class="font-bold text-slate-700 dark:text-slate-300">HRMS Portal (:8001)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold" 
                                :class="selectedAccess && selectedAccess.hrms && selectedAccess.hrms.allowed ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'" 
                                x-text="selectedAccess && selectedAccess.hrms && selectedAccess.hrms.allowed ? (selectedAccess.hrms.role || 'Allowed') : 'Access Denied'">
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Payroll Suite (:8002)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold" 
                                :class="selectedAccess && selectedAccess.payroll && selectedAccess.payroll.allowed ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'" 
                                x-text="selectedAccess && selectedAccess.payroll && selectedAccess.payroll.allowed ? (selectedAccess.payroll.role || 'Allowed') : 'Access Denied'">
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Clinic Invoicing (:8003)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold" 
                                :class="selectedAccess && selectedAccess.clinic && selectedAccess.clinic.allowed ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'" 
                                x-text="selectedAccess && selectedAccess.clinic && selectedAccess.clinic.allowed ? (selectedAccess.clinic.role || 'Allowed') : 'Access Denied'">
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
@endsection
