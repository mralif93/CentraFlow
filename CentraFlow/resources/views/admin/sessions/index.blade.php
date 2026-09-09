@extends('layouts.admin')

@section('title', 'Active Sessions & Security Control — CentraFlow Admin')

@section('content')
<div x-cloak x-data="{ tab: 'web-sessions' }" class="space-y-8 animate__animated animate__fadeIn">

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
        title="Active Sessions Governance" 
        subtitle="Monitor live browser sessions, inspect active devices, and terminate unauthorized web sessions or OAuth grants."
        icon="bx-broadcast"
        badge="Session Control"
        badgeVariant="emerald"
    >
    </x-page-header>

    <!-- Metric KPI Cards (HRMS x-stat-card) -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <x-stat-card 
            title="Active Web Sessions" 
            :value="$activeSessions->count()" 
            icon="bx-laptop" 
            color="emerald" 
            change="Real-time Online"
            changeType="positive"
        />
        <x-stat-card 
            title="Active OAuth Grants" 
            :value="$oauthTokens->count()" 
            icon="bx-key" 
            color="purple" 
            change="SSO / API Grants"
            changeType="neutral"
        />
        <x-stat-card 
            title="OAuth Applications" 
            :value="$oauthClients->count()" 
            icon="bx-layer" 
            color="blue" 
            change="Registered Clients"
            changeType="neutral"
        />
        <x-stat-card 
            title="Identity Directory" 
            :value="$userCount" 
            icon="bx-user-check" 
            color="indigo" 
            change="Enterprise Users"
            changeType="positive"
        />
    </div>

    <!-- Navigation Tabs for Sessions View -->
    <div class="border-b border-slate-200 dark:border-slate-800 flex space-x-8 text-sm">
        <button type="button" @click="tab = 'web-sessions'" :class="tab === 'web-sessions' ? 'text-emerald-600 dark:text-emerald-400 border-emerald-600 dark:border-emerald-400 border-b-2 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2 cursor-pointer">
            <i class="bx bx-laptop text-base"></i>
            <span>Active Web Sessions</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">{{ $activeSessions->count() }}</span>
        </button>
        <button type="button" @click="tab = 'oauth-tokens'" :class="tab === 'oauth-tokens' ? 'text-emerald-600 dark:text-emerald-400 border-emerald-600 dark:border-emerald-400 border-b-2 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300'" class="pb-3 transition-colors flex items-center space-x-2 cursor-pointer">
            <i class="bx bx-shield-quarter text-base"></i>
            <span>Issued OAuth Grants</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">{{ $oauthTokens->count() }}</span>
        </button>
    </div>

    <!-- Tab 1: Active Web Sessions -->
    <div x-show="tab === 'web-sessions'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bx bx-broadcast text-emerald-600 dark:text-emerald-400 text-lg"></i>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Active Browser &amp; Web Sessions</h2>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $activeSessions->count() }} connected clients</span>
        </div>

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
                                    <x-badge variant="emerald" size="sm" :dot="true" class="ml-1.5">
                                        CURRENT
                                    </x-badge>
                                @endif
                            </td>
                            <td class="p-3.5 whitespace-nowrap text-right">
                                <form method="POST" action="{{ route('admin.sessions.revoke', $s->session_id) }}">
                                    @csrf
                                    <x-button type="submit" variant="danger" size="xs" icon="bx bx-power-off">
                                        Kill Session
                                    </x-button>
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

    <!-- Tab 2: Issued OAuth Grants -->
    <div x-show="tab === 'oauth-tokens'" class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="bx bx-shield-quarter text-purple-600 dark:text-purple-400 text-lg"></i>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Active Single Sign-On (SSO) &amp; OAuth Grants</h2>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $oauthTokens->count() }} active grants</span>
        </div>

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
                                <form method="POST" action="{{ route('admin.sessions.revoke-oauth-token', $t->id) }}">
                                    @csrf
                                    <x-button type="submit" variant="danger" size="xs" icon="bx bx-key">
                                        Revoke Token
                                    </x-button>
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

</div>
@endsection
