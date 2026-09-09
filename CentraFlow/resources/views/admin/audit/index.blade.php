@extends('layouts.admin')

@section('title', 'Activity Audit Trail — CentraFlow Admin')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn" x-cloak x-data="{
    inspectModalOpen: false,
    exportModalOpen: false,
    inspectedLog: {
        event: '',
        category: '',
        description: '',
        actor: '',
        ip: '',
        timestamp: '',
        payload: null
    },
    openInspect(data) {
        this.inspectedLog = data;
        this.inspectModalOpen = true;
    }
}">

    <!-- Standard Page Header Banner -->
    <x-page-header
        title="Activity Audit Trail"
        subtitle="Chronological ledger of security, login events, master user modifications, and administrative governance"
        icon="bx-history"
        badge="Audit Ledger"
        badgeVariant="emerald"
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button
                    type="button"
                    variant="secondary"
                    size="md"
                    icon="bx bx-download"
                    @click="exportModalOpen = true"
                    class="cursor-pointer"
                >
                    Export CSV
                </x-button>

                <x-button
                    type="button"
                    variant="secondary"
                    size="md"
                    icon="bx bx-refresh"
                    onclick="window.location.reload()"
                    class="cursor-pointer"
                >
                    Refresh Log
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- High-Impact KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Audit Events"
            :value="$totalLogs"
            icon="bx-history"
            color="indigo"
            subtitle="Recorded ledger entries"
        />

        <x-stat-card
            title="Security &amp; Auth"
            :value="$authEvents"
            icon="bx-shield-quarter"
            color="blue"
            subtitle="Login and session challenges"
        />

        <x-stat-card
            title="User Management"
            :value="$userMgmtEvents"
            icon="bx-user-pin"
            color="emerald"
            subtitle="Profile & clearance updates"
        />

        <x-stat-card
            title="Session &amp; OAuth"
            :value="$sessionEvents"
            icon="bx-broadcast"
            color="purple"
            subtitle="Token revocations & sessions"
        />
    </div>

    <!-- Search & Category Filters -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 text-xs font-bold gap-4 flex-wrap pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $categories = [
                    '' => 'All Categories',
                    'auth' => 'Security & Auth',
                    'user_management' => 'User Management',
                    'session' => 'Web Sessions',
                    'oauth' => 'OAuth Tokens',
                    'security' => 'Security Events',
                ];
            @endphp

            @foreach($categories as $catKey => $catLabel)
                <a
                    href="{{ route('admin.audit-logs.index', ['category' => $catKey, 'search' => $search]) }}"
                    class="px-3 py-1.5 rounded-xl border text-xs font-bold transition {{ (string)$category === (string)$catKey ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}"
                >
                    {{ $catLabel }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2">
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search logs, IP, action..."
                    class="w-64 h-9 pl-8 pr-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <i class="bx bx-search absolute left-2.5 top-2.5 text-slate-400 text-sm pointer-events-none"></i>
            </div>
            <x-button type="submit" variant="secondary" size="xs">Search</x-button>
        </form>
    </div>

    <!-- Audit Log Records Table -->
    <div class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[750px]">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Timestamp &amp; Actor</th>
                        <th class="py-3.5 px-4">Event Category</th>
                        <th class="py-3.5 px-4">Action Summary</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Client IP / Device</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            {{ $log->user?->name ?? 'System Console' }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-mono">
                                        {{ $log->created_at->format('d M Y, h:i:s A') }}
                                    </p>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $catVariant = match($log->category) {
                                        'auth' => 'sky',
                                        'user_management' => 'emerald',
                                        'session' => 'purple',
                                        'oauth' => 'indigo',
                                        'security' => 'rose',
                                        default => 'slate',
                                    };
                                @endphp
                                <x-badge :variant="$catVariant" size="sm">
                                    {{ strtoupper(str_replace('_', ' ', $log->category)) }}
                                </x-badge>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-900 dark:text-white block max-w-sm sm:max-w-md truncate">
                                    {{ $log->description }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono block truncate max-w-xs sm:max-w-sm">
                                    Event: {{ $log->event }}
                                </span>

                                <div class="mt-1 block md:hidden text-[10px] text-slate-400 font-mono">
                                    IP: {{ $log->ip_address ?? '127.0.0.1' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 hidden md:table-cell font-mono text-[11px] whitespace-nowrap">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                <span class="text-[10px] text-slate-400 truncate max-w-[180px] block" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent ?? 'Internal Agent' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <x-button
                                    type="button"
                                    variant="secondary"
                                    size="xs"
                                    icon="bx bx-code-alt"
                                    class="cursor-pointer"
                                    @click="openInspect({{ json_encode([
                                        'event' => $log->event,
                                        'category' => $log->category,
                                        'description' => $log->description,
                                        'actor' => $log->user?->name ?? 'System Console',
                                        'ip' => $log->ip_address ?? '127.0.0.1',
                                        'timestamp' => $log->created_at->format('Y-m-d H:i:s T'),
                                        'payload' => $log->payload,
                                    ]) }})"
                                >
                                    Inspect
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="bx bx-history text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                No activity audit log entries matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$logs" />
    </div>

    <!-- Modal: Inspect Audit Log Payload -->
    <template x-teleport="body">
    <div x-show="inspectModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        <div 
            x-show="inspectModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity" 
            @click="inspectModalOpen = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="inspectModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-xl shadow-2xl space-y-5 z-10"
            >
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base font-bold">
                            <i class="bx bx-code-alt"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">Audit Entry Event Inspection</h3>
                            <p class="text-[11px] text-slate-400 font-mono" x-text="inspectedLog.event"></p>
                        </div>
                    </div>
                    <button type="button" @click="inspectModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-slate-50 dark:bg-slate-950/60 rounded-xl space-y-1.5 border border-slate-100 dark:border-slate-800">
                        <p class="font-bold text-slate-900 dark:text-white" x-text="inspectedLog.description"></p>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400 font-mono">
                            <span>Actor: <strong class="text-indigo-600 dark:text-indigo-400" x-text="inspectedLog.actor"></strong></span>
                            <span>&bull; IP: <strong class="text-slate-700 dark:text-slate-300" x-text="inspectedLog.ip"></strong></span>
                            <span>&bull; <span x-text="inspectedLog.timestamp"></span></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 text-[10px] font-bold uppercase block tracking-wider mb-1.5">Context Payload (JSON)</span>
                        <pre class="bg-slate-950 text-emerald-400 p-3.5 rounded-xl font-mono text-[11px] overflow-x-auto max-h-60 border border-slate-800 select-all" x-text="inspectedLog.payload ? JSON.stringify(inspectedLog.payload, null, 2) : 'No payload metadata recorded for this action.'"></pre>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="inspectModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>

    <!-- Modal: Confirm Export -->
    <template x-teleport="body">
    <div x-show="exportModalOpen" class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
        <div 
            x-show="exportModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity" 
            @click="exportModalOpen = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="exportModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-4 z-10"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold">
                        <i class="bx bx-download"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white">Export Audit Trail (CSV)</h3>
                        <p class="text-[11px] text-slate-400">Download formatted compliance CSV file.</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Are you sure you want to stream all audit log records matching the active filters to a CSV file?
                </p>

                <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="exportModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer transition">
                        Cancel
                    </button>
                    <a
                        href="{{ route('admin.audit-logs.export', ['category' => $category, 'search' => $search]) }}"
                        @click="exportModalOpen = false"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 cursor-pointer transition inline-flex items-center gap-1.5"
                    >
                        <i class="bx bx-download"></i>
                        <span>Download CSV</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </template>

</div>
@endsection
