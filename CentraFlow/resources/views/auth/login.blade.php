@extends('layouts.public')

@section('title', 'Sign In — CentraFlow SSO')

@section('content')
<div class="min-h-[calc(100vh-14rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
    <div class="w-full max-w-[460px] animate__animated animate__fadeInUp animate__faster">
        
        <!-- Card Container -->
        <div class="bg-white dark:bg-slate-900 rounded-[2.25rem] border border-slate-200/80 dark:border-slate-800 shadow-[0_25px_60px_-15px_rgba(99,102,241,0.18)] p-8 sm:p-10 transition-all text-left">
            
            <!-- Header Icon & Brand -->
            <div class="flex flex-col items-center text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-b from-indigo-500 via-indigo-600 to-purple-700 text-white shadow-[0_12px_24px_-4px_rgba(99,102,241,0.4)] mb-4">
                    <i class="bx bx-hive text-3xl"></i>
                </div>
                <h1 class="text-2xl sm:text-[1.65rem] font-extrabold text-slate-900 dark:text-white tracking-tight">CentraFlow SSO</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium max-w-[320px] leading-relaxed">
                    Unified identity &amp; SSO gateway for HRMS, Payroll &amp; Clinic Invoicing
                </p>
            </div>

            <!-- Dismissible Logout Alert Banner -->
            @if(session('status') || request()->has('logged_out'))
                <div id="logout-alert" class="mb-6 px-4 py-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between gap-3 animate__animated animate__fadeIn">
                    <div class="flex items-center gap-2.5 text-left">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ session('status') ?? 'You have been logged out securely.' }}</span>
                    </div>
                    <button type="button" onclick="document.getElementById('logout-alert').remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-200 transition p-0.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Validation Errors Summary -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50/80 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs animate__animated animate__fadeIn">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i class="bx bx-error-circle text-base shrink-0"></i>
                        <span>Authentication Failed</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                @if(request('return_to'))
                    <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                @endif

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Work Email Address
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-lg">
                            <i class="bx bx-envelope"></i>
                        </div>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            required
                            value="{{ old('email', 'admin@centraflow.local') }}"
                            autocomplete="email"
                            placeholder="name@centraflow.local"
                            class="block w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-900 dark:text-white placeholder:text-slate-400 transition-all outline-none"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <i class="bx bx-error-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Password
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline transition-colors">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-lg">
                            <i class="bx bx-lock-alt"></i>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            value="password"
                            autocomplete="current-password"
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                            class="block w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-900 dark:text-white placeholder:text-slate-400 transition-all outline-none"
                        >
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <i class="bx bx-error-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Remember credentials</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 border border-indigo-500/30 active:scale-[0.99] transition-all cursor-pointer"
                >
                    <i class="bx bx-log-in text-lg"></i>
                    <span>Sign In to CentraFlow</span>
                </button>
            </form>

            <!-- Quick Role Profiles (Click to prefill) - Exact HRMS Style -->
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3 text-center">
                    Quick Role Profiles (Click to prefill)
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillCreds('admin@centraflow.local', 'password')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer group">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                            <i class="bx bxs-badge-check text-indigo-600"></i> Superadmin
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono truncate">admin@centraflow.local</span>
                    </button>
                    <button type="button" onclick="fillCreds('hrmanager@centraflow.local', 'password')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer group">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1 group-hover:text-purple-600 dark:group-hover:text-purple-400">
                            <i class="bx bx-user-pin text-purple-600"></i> HR Manager
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono truncate">hrmanager@centraflow.local</span>
                    </button>
                    <button type="button" onclick="fillCreds('payroll@centraflow.local', 'password')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer group">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400">
                            <i class="bx bx-calculator text-emerald-600"></i> Payroll Officer
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono truncate">payroll@centraflow.local</span>
                    </button>
                    <button type="button" onclick="fillCreds('john.doe@centraflow.local', 'password')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer group">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1 group-hover:text-amber-600 dark:group-hover:text-amber-400">
                            <i class="bx bx-user text-amber-600"></i> Employee
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono truncate">john.doe@centraflow.local</span>
                    </button>
                </div>
            </div>

            <!-- Bottom Register Link -->
            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
                Need an enterprise account? 
                <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 font-bold ml-1 hover:underline">Register here</a>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillCreds(email, password = 'password') {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    if (emailInput) {
        emailInput.value = email;
        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (passwordInput) {
        passwordInput.value = password;
        passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
}
</script>
@endpush
