@extends('layouts.public')

@section('title', 'Sign In — CentraFlow SSO')

@section('content')
<div class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white/90 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl p-8 backdrop-blur transition-colors">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex h-12 w-12 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 items-center justify-center shadow-lg shadow-indigo-500/25 mb-3 text-white">
                <i class="bx bx-hive text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">CentraFlow SSO</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">One credential for HRMS, Payroll &amp; Clinic Invoicing</p>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="mb-6 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-2">
                <i class="bx bx-error-circle text-lg shrink-0"></i>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            @if(request('return_to'))
                <input type="hidden" name="return_to" value="{{ request('return_to') }}">
            @endif

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bx bx-envelope text-base"></i>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email', 'admin@centraflow.local') }}" required autofocus
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition-all"
                        placeholder="name@company.com">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Password</label>
                    <span class="text-[11px] text-slate-400 font-mono">Demo: password123</span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bx bx-lock-alt text-base"></i>
                    </div>
                    <input id="password" type="password" name="password" value="password123" required
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none transition-all">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center text-slate-600 dark:text-slate-400 select-none cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-100 dark:bg-slate-950 border-slate-300 dark:border-slate-800 text-indigo-600 focus:ring-0 mr-2">
                    Remember session
                </label>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-bold text-white text-sm shadow-lg shadow-indigo-600/30 border border-indigo-500/30 transition-all mt-2 cursor-pointer">
                <i class="bx bx-log-in text-lg"></i>
                <span>Sign In to CentraFlow</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800/80 text-center text-xs text-slate-500 dark:text-slate-400">
            Need an account? 
            <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-medium ml-1">Register here</a>
        </div>

    </div>
</div>
@endsection
