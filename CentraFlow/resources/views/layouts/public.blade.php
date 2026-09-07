<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CentraFlow — Enterprise Operations Hub')</title>

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

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-indigo-600 selection:text-white min-h-screen flex flex-col justify-between relative overflow-x-hidden transition-colors duration-300">

    <!-- Background Decorative Glow -->
    <div class="absolute inset-0 glow-radial pointer-events-none -z-10"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[400px] bg-gradient-to-tr from-indigo-500/15 via-purple-500/10 to-indigo-700/15 blur-[130px] rounded-full pointer-events-none -z-10"></div>

    <!-- Navigation Header -->
    <header class="w-full border-b border-slate-200 dark:border-slate-800/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3.5 group min-w-0">
                <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 flex items-center justify-center text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 group-hover:scale-105 transition-transform shrink-0">
                    <i class="bx bx-hive text-xl sm:text-2xl font-bold"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 dark:from-white via-indigo-950 dark:via-indigo-100 to-indigo-600 dark:to-indigo-300 bg-clip-text text-transparent truncate block">
                        Centra<span class="text-indigo-600 dark:text-indigo-400">Flow</span>
                    </span>
                    <span class="hidden sm:inline-block px-2 py-0.5 text-[9px] font-bold tracking-wider uppercase bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-400/30">
                        Operations Hub &bull; SSO Gateway
                    </span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600 dark:text-slate-300">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors {{ request()->routeIs('home') ? 'text-indigo-600 dark:text-indigo-400 font-bold' : '' }}">Launcher</a>
                <a href="http://localhost:8001/auth/centraflow" target="_blank" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-1">
                    <span>HRMS</span>
                    <span class="px-1.5 py-0.2 text-[9px] font-mono rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">:8001</span>
                </a>
                <a href="http://localhost:8002/auth/centraflow" target="_blank" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1">
                    <span>Payroll</span>
                    <span class="px-1.5 py-0.2 text-[9px] font-mono rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">:8002</span>
                </a>
                <a href="http://localhost:8003/auth/centraflow" target="_blank" class="hover:text-purple-600 dark:hover:text-purple-400 transition-colors flex items-center gap-1">
                    <span>Invoicing</span>
                    <span class="px-1.5 py-0.2 text-[9px] font-mono rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">:8003</span>
                </a>
            </nav>

            <!-- Header Quick Actions: Theme Toggle & Auth Buttons -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- HRMS Standard Theme Toggle Component -->
                <x-theme-toggle />

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 transition-all">
                        <i class="bx bxs-dashboard text-base sm:text-lg"></i>
                        <span>Admin Console</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-2 sm:px-3 sm:py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 transition-all border border-slate-200 dark:border-slate-700">
                            <i class="bx bx-log-out text-base sm:hidden"></i>
                            <span class="hidden sm:inline">Sign Out</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 transition-all">
                        <i class="bx bx-shield-quarter text-base sm:text-lg"></i>
                        <span>Sign In</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-grow">
        @if (session('status'))
            <div class="max-w-md mx-auto mt-6 px-4">
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm animate__animated animate__fadeInDown">
                    <i class="bx bx-check-circle text-xl text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-md mx-auto mt-6 px-4">
                <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm animate__animated animate__fadeInDown">
                    <i class="bx bx-error-circle text-xl text-rose-600 dark:text-rose-400 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer (HRMS Style) -->
    <footer class="border-t border-slate-200 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/90 py-6 sm:py-8 text-xs text-slate-500 dark:text-slate-400 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                <div class="w-6 h-6 rounded-md bg-indigo-600 flex items-center justify-center text-white text-sm shrink-0">
                    <i class="bx bx-hive"></i>
                </div>
                <span class="font-bold text-slate-900 dark:text-white">CentraFlow Hub</span>
                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-[11px] sm:text-xs">Federated SSO &bull; OAuth 2.0 &bull; Event Orchestration</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs">
                <a href="http://localhost:8001/login" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 font-semibold">
                    <span>HRMS (:8001)</span>
                    <i class="bx bx-link-external text-xs"></i>
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <a href="http://localhost:8002/login" target="_blank" class="text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-semibold">
                    <span>Payroll (:8002)</span>
                    <i class="bx bx-link-external text-xs"></i>
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <a href="http://localhost:8003/login" target="_blank" class="text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1 font-semibold">
                    <span>Invoicing (:8003)</span>
                    <i class="bx bx-link-external text-xs"></i>
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-slate-400 dark:text-slate-500 font-mono text-[11px] sm:text-xs">&copy; {{ date('Y') }} CentraFlow. All rights reserved.</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
