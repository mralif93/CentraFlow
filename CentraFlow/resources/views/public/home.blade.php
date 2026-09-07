@extends('layouts.public')

@section('title', 'CentraFlow — Enterprise Operations Launcher')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Hero Header -->
    <div class="mb-12 text-center max-w-3xl mx-auto">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-semibold mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Federated Architecture &bull; SSO Online</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-4">
            Unified Enterprise Operations Launcher
        </h1>
        <p class="text-slate-600 dark:text-slate-400 text-base sm:text-lg leading-relaxed">
            Centralized hub orchestrating <strong class="text-slate-900 dark:text-slate-200">HRMS</strong>, <strong class="text-slate-900 dark:text-slate-200">Payroll</strong>, and <strong class="text-slate-900 dark:text-slate-200">Clinic Invoicing</strong> with single-sign-on identity and asynchronous Redis event queues.
        </p>
    </div>

    <!-- 3 Sub-System Portals Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        
        <!-- Module 1: HRMS -->
        <div class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:border-blue-500/40 hover:-translate-y-1 relative group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="bx bxs-user-badge text-2xl"></i>
                    </div>
                    <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Port :8001</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">HRMS Module</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm mb-6 leading-relaxed">
                    Employee master records, organizational hierarchy, shift tracking, leave approvals, and expense claim validation.
                </p>
                <div class="space-y-2 mb-6">
                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                        <span>OAuth Scope:</span>
                        <code class="text-indigo-600 dark:text-indigo-300 bg-slate-100 dark:bg-slate-950 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-800 font-mono">hrms:read</code>
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                        <span>Event Publisher:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-mono">LeaveApproved</span>
                    </div>
                </div>
            </div>
            <a href="http://localhost:8001/login" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 font-semibold text-white text-sm shadow-md shadow-blue-600/25 transition-all">
                <span>Launch HRMS Portal</span>
                <i class="bx bx-link-external text-base"></i>
            </a>
        </div>

        <!-- Module 2: Payroll -->
        <div class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:border-emerald-500/40 hover:-translate-y-1 relative group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i class="bx bx-wallet text-2xl"></i>
                    </div>
                    <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Port :8002</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Payroll Module</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm mb-6 leading-relaxed">
                    Salary calculation engine, automatic unpaid leave deductions, statutory compliance (EPF/SOCSO/PCB), and PDF payslip generation.
                </p>
                <div class="space-y-2 mb-6">
                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                        <span>OAuth Scope:</span>
                        <code class="text-emerald-600 dark:text-emerald-300 bg-slate-100 dark:bg-slate-950 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-800 font-mono">payroll:run</code>
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                        <span>Event Ingestor:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-mono">ClaimApproved</span>
                    </div>
                </div>
            </div>
            <a href="http://localhost:8002/login" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-semibold text-white text-sm shadow-md shadow-emerald-600/25 transition-all">
                <span>Launch Payroll Portal</span>
                <i class="bx bx-link-external text-base"></i>
            </a>
        </div>

        <!-- Module 3: Invoice -->
        <div class="glass-panel bg-white/80 dark:bg-slate-900/75 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:border-purple-500/40 hover:-translate-y-1 relative group flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i class="bx bx-receipt text-2xl"></i>
                    </div>
                    <span class="text-xs font-mono px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Port :8003</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Clinic Invoicing</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm mb-6 leading-relaxed">
                    Patient and client billing accounts, treatment and consultation catalogs, tax invoices, and accounts receivable tracking.
                </p>
                <div class="space-y-2 mb-6">
                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                        <span>OAuth Scope:</span>
                        <code class="text-purple-600 dark:text-purple-300 bg-slate-100 dark:bg-slate-950 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-800 font-mono">invoice:manage</code>
                    </div>
                    <div class="text-xs text-slate-600 dark:text-slate-400 flex items-center justify-between">
                        <span>Event Ingestor:</span>
                        <span class="text-purple-600 dark:text-purple-400 font-mono">TimesheetApproved</span>
                    </div>
                </div>
            </div>
            <a href="http://localhost:8003/login" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-purple-600 hover:bg-purple-500 font-semibold text-white text-sm shadow-md shadow-purple-600/25 transition-all">
                <span>Launch Invoicing Portal</span>
                <i class="bx bx-link-external text-base"></i>
            </a>
        </div>

    </div>

    <!-- Administrative Quick Access Strip -->
    @auth
        <div class="glass-panel bg-white/80 dark:bg-slate-900/75 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border border-indigo-500/20 shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <i class="bx bx-shield-quarter text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Centralized ID &amp; Session Governance</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Control active web sessions, inspect OAuth 2.0 tokens, and provision master users.</p>
                </div>
            </div>
            <a href="{{ route('admin.id-management') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition-all shadow-md shadow-indigo-600/25 whitespace-nowrap flex items-center gap-1.5">
                <span>Open ID Dashboard</span>
                <i class="bx bx-right-arrow-alt text-base"></i>
            </a>
        </div>
    @endauth

</div>
@endsection
