@extends('layouts.admin')

@section('title', 'Platform Operations & System Overview — CentraFlow')

@section('content')
<div class="space-y-8 animate__animated animate__fadeIn">

    <!-- Page Header (HRMS Component Standard) -->
    <x-page-header 
        title="Operations &amp; Microservice Governance" 
        subtitle="Central orchestration of master identities, federated microservice portals, and active user sessions."
        icon="bx-grid-alt"
        badge="Enterprise Mesh"
        badgeVariant="indigo"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button 
                    href="{{ route('admin.users.index') }}" 
                    variant="primary" 
                    size="md" 
                    icon="bx bx-user-pin"
                    class="shadow-md shadow-indigo-600/30"
                >
                    Manage Users
                </x-button>
                <x-button 
                    href="{{ route('admin.sessions.index') }}" 
                    variant="secondary" 
                    size="md" 
                    icon="bx bx-broadcast"
                >
                    Sessions Control
                </x-button>
                <x-button 
                    href="{{ route('admin.audit-logs.index') }}" 
                    variant="secondary" 
                    size="md" 
                    icon="bx bx-history"
                >
                    Audit Trail
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Top Metric Overview Grid (HRMS Component Standard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.users.index') }}" class="block">
            <x-stat-card 
                title="Master Users" 
                :value="$userCount" 
                icon="bx-group" 
                color="indigo" 
                change="Unified Directory"
                changeType="positive"
                subtitle="HRMS, Payroll & CIS"
            />
        </a>

        <a href="{{ route('admin.sessions.index') }}" class="block">
            <x-stat-card 
                title="Active Sessions" 
                :value="$sessionCount" 
                icon="bx-broadcast" 
                color="emerald" 
                change="Online"
                changeType="positive"
                subtitle="Database session store"
            />
        </a>

        <x-stat-card 
            title="Federated Modules" 
            value="3 Active" 
            icon="bx-layer" 
            color="purple" 
            change="100% Operational"
            changeType="positive"
            subtitle="HRMS, Payroll & CIS"
        />

        <x-stat-card 
            title="OAuth Grants" 
            :value="$clientCount" 
            icon="bx-key" 
            color="blue" 
            change="Connected Clients"
            changeType="neutral"
            subtitle="Passport PKCE & Tokens"
        />
    </div>

    <!-- Live Sub-System Module Status Cards (Federated Health Check) -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bx bx-check-shield text-indigo-600 dark:text-indigo-400 text-xl"></i>
                    <span>Federated Ecosystem Modules</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Real-time status check and direct access to all connected platforms.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Health Check ({{ now()->format('H:i:s') }})</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach ($modules as $mod)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs dark:shadow-md transition-all hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-700 flex flex-col justify-between group">
                    <div>
                        <div class="flex items-start justify-between mb-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-{{ $mod['color'] }}-50 dark:bg-{{ $mod['color'] }}-500/10 text-{{ $mod['color'] }}-600 dark:text-{{ $mod['color'] }}-400 flex items-center justify-center text-2xl font-bold shadow-xs">
                                <i class="bx {{ $mod['icon'] }}"></i>
                            </div>
                            @if ($mod['online'])
                                <x-badge variant="emerald" size="sm" :dot="true">
                                    Online (200 OK)
                                </x-badge>
                            @else
                                <x-badge variant="rose" size="sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Offline</span>
                                </x-badge>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">{{ $mod['name'] }}</h3>
                            <span class="font-mono text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700">{{ $mod['port'] }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $mod['repo'] }}</p>

                        <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span class="font-medium">Direct Portal:</span>
                                <a href="{{ $mod['url'] }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline font-mono text-[11px]">{{ $mod['url'] }}</a>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span class="font-medium">Federation Role:</span>
                                <span class="font-mono font-semibold text-slate-700 dark:text-slate-200 text-[11px]">{{ $mod['event_role'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                <span class="font-medium">OAuth Scope:</span>
                                <code class="text-indigo-600 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-950/40 px-2 py-0.5 rounded text-[10px] font-mono border border-indigo-100 dark:border-indigo-800/50">{{ $mod['scope'] }}</code>
                            </div>
                        </div>

                        <!-- Direct Subsystem Admin Shortcuts -->
                        @if (!empty($mod['shortcuts']))
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">Deep System Shortcuts</p>
                                <div class="grid grid-cols-1 gap-1.5">
                                    @foreach ($mod['shortcuts'] as $sc)
                                        <a 
                                            href="{{ $sc['url'] }}" 
                                            target="_blank"
                                            class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-[11px] font-semibold text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all border border-slate-100 dark:border-slate-800 group/sc"
                                        >
                                            <span class="flex items-center gap-2 truncate">
                                                <i class="bx {{ $sc['icon'] }} text-slate-400 group-hover/sc:text-indigo-500"></i>
                                                <span class="truncate">{{ $sc['label'] }}</span>
                                            </span>
                                            <i class="bx bx-right-arrow-alt text-xs text-slate-400 group-hover/sc:translate-x-0.5 transition-transform"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800/80">
                        <x-button 
                            href="{{ $mod['url'] }}" 
                            target="_blank" 
                            variant="secondary" 
                            size="md" 
                            iconRight="bx bx-link-external"
                            class="w-full"
                        >
                            Launch {{ $mod['name'] }}
                        </x-button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Dual Grid: Active Master Identities & Registered OAuth Clients (HRMS Standard) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Master Identities Table with x-card -->
        <div class="lg:col-span-2">
            <x-card :noPadding="true">
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">Master Users Directory</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Active enterprise users with unified employee profiles across all modules.</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-1">
                            <span>View All ({{ $userCount }})</span>
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                </x-slot:header>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="text-[10px] uppercase tracking-wider text-slate-400 font-bold bg-slate-50/70 dark:bg-slate-950/40 border-b border-slate-100 dark:border-slate-800 select-none">
                            <tr>
                                <th class="py-3.5 px-5">User &bull; Staff ID</th>
                                <th class="py-3.5 px-4 hidden sm:table-cell">Department &bull; Role Title</th>
                                <th class="py-3.5 px-4">Global Role</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                            @foreach ($recentUsers as $u)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-3.5 px-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-extrabold flex items-center justify-center text-xs shrink-0 border border-indigo-200/50 dark:border-indigo-500/20">
                                                {{ strtoupper(substr($u->name, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="font-bold text-slate-900 dark:text-white block truncate">{{ $u->name }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">{{ $u->staff_id ?? 'EMP-000' }} &bull; {{ $u->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 hidden sm:table-cell">
                                        <div class="font-medium text-slate-800 dark:text-slate-200">{{ $u->department ?? 'General' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $u->job_title ?? $u->designation ?? 'Staff' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-badge 
                                            :variant="match($u->role) {
                                                'superadmin' => 'rose',
                                                'hr_manager' => 'indigo',
                                                'payroll_officer' => 'emerald',
                                                'finance_officer' => 'purple',
                                                default => 'slate',
                                            }" 
                                            size="sm"
                                        >
                                            {{ str_replace('_', ' ', $u->role) }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-badge variant="emerald" size="sm" :dot="true">
                                            Active
                                        </x-badge>
                                    </td>
                                    <td class="py-3.5 px-5 text-right">
                                        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline text-xs">Manage</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Right 1 Col: OAuth Clients & Live Audit Feed -->
        <div class="space-y-6">
            <!-- Recent Security Audit Trail Widget -->
            @if(isset($recentAuditLogs) && $recentAuditLogs->isNotEmpty())
                <x-card>
                    <x-slot:header>
                        <div class="flex items-center justify-between w-full">
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bx bx-history text-indigo-600 dark:text-indigo-400 text-sm"></i>
                                <span>Recent Audit Stream</span>
                            </h3>
                            <a href="{{ route('admin.audit-logs.index') }}" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                View All
                            </a>
                        </div>
                    </x-slot:header>

                    <div class="space-y-3">
                        @foreach($recentAuditLogs as $log)
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800 text-xs">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 truncate">
                                        {{ $log->event }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 shrink-0 font-mono">{{ $log->created_at->diffForHumans(null, true) }}</span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-[11px] leading-snug truncate">{{ $log->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif

            <!-- OAuth Registered Clients Widget with x-card -->
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between w-full">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">OAuth 2.0 Clients</h3>
                        <x-badge variant="indigo" size="sm">
                            {{ $clientCount }} Registered
                        </x-badge>
                    </div>
                </x-slot:header>

                <div class="space-y-3">
                    @foreach ($oauthClients as $client)
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>{{ $client->name }}</span>
                                </span>
                                <x-badge variant="emerald" size="sm">READY</x-badge>
                            </div>
                            <p class="text-slate-400 font-mono mt-1.5 text-[10px] truncate">{{ $client->id }}</p>
                            <p class="text-indigo-600 dark:text-indigo-400 font-mono mt-0.5 text-[10px] truncate">
                                {{ is_array($client->redirect_uris) ? implode(', ', $client->redirect_uris) : $client->redirect_uris }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Architecture Info Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 p-6 border border-indigo-800/40 shadow-xl shadow-indigo-950/30 text-white">
                <div class="relative z-10">
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-indigo-300 font-bold border border-white/10">
                            <i class="bx bx-fingerprint text-lg"></i>
                        </div>
                        <h4 class="font-extrabold text-sm">Unified Enterprise Mesh</h4>
                    </div>
                    <p class="text-xs text-indigo-100/80 leading-relaxed">
                        CentraFlow acts as the primary identity hub with central user management, cross-portal navigation, and RBAC synchronization across HRMS, Payroll, and Invoicing.
                    </p>
                    <div class="mt-4 pt-3.5 border-t border-indigo-800/50 flex items-center justify-between text-[11px] font-mono text-indigo-300">
                        <span>Central Hub</span>
                        <span>Multi-Portal Architecture</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
