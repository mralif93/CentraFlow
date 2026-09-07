@extends('layouts.admin')

@section('title', 'Identities & Session Control — CentraFlow Admin')

@section('content')
<div x-data="{ tab: 'identities', showCreateModal: false }" class="space-y-8">

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
            >
                Create Master Identity
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Metric KPI Cards (HRMS x-stat-card) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <x-stat-card 
            title="Total Master Identities" 
            :value="$users->total()" 
            icon="bx bx-group" 
            color="indigo" 
            change="Synchronized"
            changeType="increase"
            subtitle="Synced with HRMS, Payroll & CIS"
        />

        <x-stat-card 
            title="Active Web Sessions" 
            :value="$activeSessions->count()" 
            icon="bx bx-broadcast" 
            color="emerald" 
            change="Live Mesh"
            changeType="increase"
            subtitle="Stored in database session store"
        />

        <x-stat-card 
            title="Live OAuth SSO Tokens" 
            :value="$oauthTokens->count()" 
            icon="bx bx-key" 
            color="purple" 
            change="Secured"
            changeType="neutral"
            subtitle="Active microservice & user tokens"
        />
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 dark:border-slate-800 flex space-x-8 text-sm">
        <button @click="tab = 'identities'" :class="tab === 'identities' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400 border-b-2 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2">
            <span>Master Identities</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $users->total() }}</span>
        </button>
        <button @click="tab = 'sessions'" :class="tab === 'sessions' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400 border-b-2 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2">
            <span>Active Web Sessions</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">{{ $activeSessions->count() }}</span>
        </button>
        <button @click="tab = 'tokens'" :class="tab === 'tokens' ? 'text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400 border-b-2 font-semibold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2">
            <span>OAuth 2.0 Tokens</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">{{ $oauthTokens->count() }}</span>
        </button>
    </div>

    <!-- Tab 1: Master Identities -->
    <div x-show="tab === 'identities'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-900/90 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">User</th>
                        <th class="py-3.5 px-4">Staff ID / Contact</th>
                        <th class="py-3.5 px-4">Master UUID</th>
                        <th class="py-3.5 px-4">Role &amp; Department</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Joined</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                    @foreach ($users as $user)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white text-sm">{{ $user->name }}</div>
                                <div class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $user->email }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-semibold text-slate-800 dark:text-slate-200">{{ $user->staff_id ?? 'N/A' }}</div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px]">{{ $user->phone ?? '—' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-indigo-600 dark:text-indigo-300">
                                {{ $user->uuid }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div>
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold uppercase font-mono
                                        {{ $user->role === 'superadmin' ? 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' : '' }}
                                        {{ $user->role === 'hr_manager' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' : '' }}
                                        {{ $user->role === 'payroll_officer' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $user->role === 'finance_officer' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : '' }}
                                        {{ $user->role === 'employee' ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : '' }}
                                    ">
                                        {{ str_replace('_', ' ', $user->role) }}
                                    </span>
                                </div>
                                <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                                    {{ $user->department ?? 'General' }} &bull; {{ $user->job_title ?? 'Staff' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ $user->status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                                    {{ $user->status ?? 'active' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <form method="POST" action="{{ route('admin.id-management.revoke-user-sessions', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" title="Kill all active sessions & tokens" class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-medium transition-all">
                                        Revoke Sessions
                                    </button>
                                </form>

                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.id-management.destroy-user', $user->id) }}" class="inline" onsubmit="return confirm('Delete this master identity?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-medium transition-all">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Tab 2: Active Web Sessions -->
    <div x-show="tab === 'sessions'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-900/90 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">User</th>
                        <th class="py-3.5 px-4">IP Address</th>
                        <th class="py-3.5 px-4">Browser / Device</th>
                        <th class="py-3.5 px-4">Last Activity</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                    @forelse ($activeSessions as $s)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-900/40 transition-colors {{ $s->session_id === $currentSessionId ? 'bg-indigo-50/60 dark:bg-indigo-500/5' : '' }}">
                            <td class="py-3.5 px-4">
                                @if ($s->user_name)
                                    <div class="font-semibold text-slate-900 dark:text-white flex items-center space-x-2">
                                        <span>{{ $s->user_name }}</span>
                                        @if ($s->session_id === $currentSessionId)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 border border-indigo-500/30">Current Session</span>
                                        @endif
                                    </div>
                                    <div class="text-slate-500 dark:text-slate-400 font-mono text-[11px]">{{ $s->user_email }}</div>
                                @else
                                    <span class="text-slate-400 italic">Guest Session</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300">
                                {{ $s->ip_address ?? '127.0.0.1' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $s->user_agent }}">
                                {{ $s->user_agent ?? 'Unknown Agent' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 font-mono">
                                {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="{{ route('admin.id-management.revoke-session', $s->session_id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-medium transition-all">
                                        Kill Session
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No active sessions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: OAuth 2.0 Tokens -->
    <div x-show="tab === 'tokens'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm" style="display: none;">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-900/90 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Token ID / Client</th>
                        <th class="py-3.5 px-4">User ID</th>
                        <th class="py-3.5 px-4">Scopes Granted</th>
                        <th class="py-3.5 px-4">Expires</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                    @forelse ($oauthTokens as $token)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $token->client->name ?? 'OAuth Client' }}</div>
                                <div class="font-mono text-[10px] text-slate-400">{{ substr($token->id, 0, 16) }}...</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300">
                                {{ $token->user_id ?? 'Client Credentials' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($token->scopes as $sc)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-300 font-mono text-[10px] border border-slate-200 dark:border-slate-700">{{ $sc }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 font-mono">
                                {{ $token->expires_at ? $token->expires_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="{{ route('admin.id-management.revoke-oauth-token', $token->id) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/20 font-medium transition-all">
                                        Revoke Token
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No active OAuth tokens found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Identity Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-sm" style="display: none;">
        <div @click.away="showCreateModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create New Master Identity</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.id-management.store-user') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Full Name</label>
                        <input type="text" name="name" required class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Staff ID / Code</label>
                        <input type="text" name="staff_id" placeholder="EMP-2026-0001" class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="+60123456789" class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Department</label>
                        <input type="text" name="department" placeholder="Engineering, HR..." class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Job Title</label>
                        <input type="text" name="job_title" placeholder="Lead Architect..." class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Role Designation</label>
                        <select name="role" required class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                            <option value="employee">Employee</option>
                            <option value="hr_manager">HR Manager</option>
                            <option value="payroll_officer">Payroll Officer</option>
                            <option value="finance_officer">Finance Officer</option>
                            <option value="superadmin">Super Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Initial Password</label>
                        <input type="password" name="password" required class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-medium">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30">
                        Save Identity
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
