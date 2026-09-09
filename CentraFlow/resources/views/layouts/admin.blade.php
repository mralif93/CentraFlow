<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Console - CentraFlow')</title>

    <!-- Theme Initialization to prevent FOUC -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles / Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full bg-slate-100/80 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-indigo-600 selection:text-white transition-colors duration-300 flex overflow-hidden">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebar-backdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300"></div>

    <!-- SIDEBAR NAVIGATION (Executive CentraFlow Design) -->
    <aside id="admin-sidebar" class="fixed lg:static inset-y-0 left-0 -translate-x-full lg:translate-x-0 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0 z-40 lg:z-auto transition-transform duration-300 ease-in-out">
        
        <!-- Sidebar Brand Logo & Mobile Close Button -->
        <div class="h-16 sm:h-20 flex items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800/80">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-600/30 border border-indigo-400/30 group-hover:scale-105 transition-transform">
                    <i class="bx bx-hive text-xl font-bold"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight">
                        Centra<span class="text-indigo-600 dark:text-indigo-400">Flow</span>
                    </span>
                    <span class="block text-[9px] uppercase font-bold tracking-wider text-slate-400">
                        Admin Console
                    </span>
                </div>
            </a>

            <!-- Mobile Close Sidebar Button -->
            <button
                type="button"
                onclick="toggleMobileSidebar()"
                class="lg:hidden p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
                <i class="bx bx-x text-2xl"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu Links -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
            <!-- Group 1: Central Governance -->
            <div>
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Central Governance</p>
                <div class="space-y-1">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="bx bxs-dashboard text-lg {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-indigo-500' }}"></i>
                        <span>System Overview</span>
                    </a>
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="bx bx-user-pin text-lg {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-purple-500' }}"></i>
                        <span>Users</span>
                    </a>
                    <a
                        href="{{ route('admin.sessions.index') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.sessions.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="bx bx-broadcast text-lg {{ request()->routeIs('admin.sessions.*') ? 'text-white' : 'text-emerald-500' }}"></i>
                        <span>Sessions</span>
                    </a>
                    <a
                        href="{{ route('admin.audit-logs.index') }}"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.audit-logs.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        <i class="bx bx-history text-lg {{ request()->routeIs('admin.audit-logs.*') ? 'text-white' : 'text-amber-500' }}"></i>
                        <span>Audit Trail</span>
                    </a>
                </div>
            </div>

            <!-- Group 2: Federated Modules & External Portals -->
            <div>
                <div class="flex items-center justify-between px-3 mb-2">
                    <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Federated Modules</p>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold font-mono bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">3 Live</span>
                </div>
                <div class="space-y-1">
                    <!-- HRMS Module -->
                    <a href="http://localhost:8001/login" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-blue-600 dark:hover:text-blue-400 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                                <i class="bx bx-user-pin"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block group-hover:text-blue-600">HRMS Portal</span>
                                <span class="text-[10px] text-slate-400 font-mono">PulseHR (:8001)</span>
                            </div>
                        </div>
                        <i class="bx bx-link-external text-xs text-slate-400 group-hover:text-blue-600"></i>
                    </a>

                    <!-- Payroll Module -->
                    <a href="http://localhost:8002/login" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm">
                                <i class="bx bx-wallet"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block group-hover:text-emerald-600">Payroll Suite</span>
                                <span class="text-[10px] text-slate-400 font-mono">PayFlow MY (:8002)</span>
                            </div>
                        </div>
                        <i class="bx bx-link-external text-xs text-slate-400 group-hover:text-emerald-600"></i>
                    </a>

                    <!-- Invoicing Module -->
                    <a href="http://localhost:8003/login" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-purple-600 dark:hover:text-purple-400 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm">
                                <i class="bx bx-receipt"></i>
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block group-hover:text-purple-600">Clinic Invoicing</span>
                                <span class="text-[10px] text-slate-400 font-mono">MediBill CIS (:8003)</span>
                            </div>
                        </div>
                        <i class="bx bx-link-external text-xs text-slate-400 group-hover:text-purple-600"></i>
                    </a>
                </div>
            </div>

            <!-- Group 3: Platform Launchpad -->
            <div>
                <p class="px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">Shortcuts</p>
                <div class="space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white transition-all">
                        <i class="bx bx-grid-alt text-lg text-slate-400"></i>
                        <span>Public Launchpad</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sidebar Station Footer -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/30">
            <div class="flex items-center justify-between px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Central Hub Online</span>
                </div>
                <span class="text-[9px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">:8004</span>
            </div>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- Top Header Bar (HRMS Style) -->
        <header class="h-16 sm:h-20 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-4 sm:px-8 flex items-center justify-between shrink-0 z-30">
            <!-- Left Info / Mobile Toggle -->
            <div class="flex items-center gap-3 sm:gap-4">
                <button
                    type="button"
                    onclick="toggleMobileSidebar()"
                    class="lg:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    aria-label="Toggle Sidebar"
                >
                    <i class="bx bx-menu text-2xl"></i>
                </button>

                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-tight">
                        CentraFlow Platform
                    </h2>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono hidden sm:inline-block">
                        Enterprise Unified Directory &bull; Multi-Portal Orchestration
                    </span>
                </div>
            </div>

            <!-- Top Header Actions -->
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Theme Toggle Button -->
                <x-theme-toggle />

                <!-- User Dropdown Menu -->
                <div class="relative" id="user-menu-container">
                    <button
                        type="button"
                        id="user-menu-button"
                        onclick="document.getElementById('user-dropdown').classList.toggle('hidden')"
                        class="flex items-center gap-2.5 p-1.5 sm:px-2.5 sm:py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700/80 transition focus:outline-none cursor-pointer"
                    >
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-xs font-bold text-slate-800 dark:text-white leading-tight truncate max-w-[130px]">
                                {{ auth()->user()->name ?? 'Administrator' }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1">
                                <span>{{ auth()->user()->staff_id ?? 'ADM-001' }}</span>
                                <span>&bull;</span>
                                <span class="capitalize text-indigo-600 dark:text-indigo-400 font-semibold">{{ str_replace('_', ' ', auth()->user()->role ?? 'superadmin') }}</span>
                            </div>
                        </div>
                        <i class="bx bx-chevron-down text-slate-400 text-base"></i>
                    </button>

                    <!-- Dropdown Modal Popup -->
                    <div
                        id="user-dropdown"
                        class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-400/20 dark:shadow-black/60 py-2 z-50 animate__animated animate__fadeIn animate__faster text-xs"
                    >
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                            <p class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ auth()->user()->email ?? 'admin@centraflow.local' }}</p>
                            <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-400/30">
                                <i class="bx bx-shield-quarter"></i>
                                <span>{{ str_replace('_', ' ', auth()->user()->role ?? 'superadmin') }}</span>
                            </div>
                        </div>

                        <div class="py-1.5 px-2 space-y-0.5">
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bxs-dashboard text-base text-slate-400"></i>
                                <span>System Overview</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bx-user-pin text-base text-slate-400"></i>
                                <span>Users Directory</span>
                            </a>
                            <a href="{{ route('admin.sessions.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bx-broadcast text-base text-slate-400"></i>
                                <span>Active Sessions</span>
                            </a>
                            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bx-history text-base text-slate-400"></i>
                                <span>Activity Audit Trail</span>
                            </a>
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bx-grid-alt text-base text-slate-400"></i>
                                <span>Operations Launchpad</span>
                            </a>
                        </div>

                        <div class="pt-1.5 px-2 border-t border-slate-100 dark:border-slate-800">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 font-semibold transition cursor-pointer">
                                    <i class="bx bx-log-out text-base"></i>
                                    <span>Sign Out Console</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body Scroll Area with Responsive Footer -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-between">
            <div class="flex-1">
                @yield('content')
            </div>

            <!-- Admin Internal Footer -->
            <footer class="mt-8 pt-4 border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400 text-center sm:text-left">
                <div class="flex items-center gap-1.5 justify-center sm:justify-start">
                    <span class="font-bold text-slate-600 dark:text-slate-300">CentraFlow Hub Console</span>
                    <span>&bull;</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Enterprise Unified Architecture</span>
                </div>
                <div>
                    <span>Federated Systems: </span>
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">HRMS &bull; Payroll &bull; Invoicing</span>
                </div>
            </footer>
        </main>
    </div>

    <!-- Global Modal Portal Insertion Slot -->
    @stack('modals')

    @stack('scripts')

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        // Close User Dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const container = document.getElementById('user-menu-container');
            const dropdown = document.getElementById('user-dropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
