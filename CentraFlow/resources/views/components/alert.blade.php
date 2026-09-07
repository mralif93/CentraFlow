@props([
    'type' => 'info', // success, error, warning, info
    'variant' => null, // alias for type or backwards compatibility
    'title' => null,
    'icon' => null,
    'dismissible' => true,
])

@php
    $resolvedType = $variant ?? $type;

    $types = [
        'success' => [
            'bg' => 'bg-emerald-50/80 dark:bg-emerald-950/40',
            'border' => 'border-emerald-200/90 dark:border-emerald-900/60',
            'text' => 'text-emerald-900 dark:text-emerald-200',
            'desc' => 'text-emerald-700 dark:text-emerald-300/90',
            'icon' => 'bx-check-circle text-emerald-600 dark:text-emerald-400',
            'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/50',
        ],
        'error' => [
            'bg' => 'bg-rose-50/80 dark:bg-rose-950/40',
            'border' => 'border-rose-200/90 dark:border-rose-900/60',
            'text' => 'text-rose-900 dark:text-rose-200',
            'desc' => 'text-rose-700 dark:text-rose-300/90',
            'icon' => 'bx-error-circle text-rose-600 dark:text-rose-400',
            'iconBg' => 'bg-rose-100 dark:bg-rose-900/50',
        ],
        'danger' => [
            'bg' => 'bg-rose-50/80 dark:bg-rose-950/40',
            'border' => 'border-rose-200/90 dark:border-rose-900/60',
            'text' => 'text-rose-900 dark:text-rose-200',
            'desc' => 'text-rose-700 dark:text-rose-300/90',
            'icon' => 'bx-error-circle text-rose-600 dark:text-rose-400',
            'iconBg' => 'bg-rose-100 dark:bg-rose-900/50',
        ],
        'warning' => [
            'bg' => 'bg-amber-50/80 dark:bg-amber-950/40',
            'border' => 'border-amber-200/90 dark:border-amber-900/60',
            'text' => 'text-amber-900 dark:text-amber-200',
            'desc' => 'text-amber-700 dark:text-amber-300/90',
            'icon' => 'bx-error text-amber-600 dark:text-amber-400',
            'iconBg' => 'bg-amber-100 dark:bg-amber-900/50',
        ],
        'info' => [
            'bg' => 'bg-indigo-50/80 dark:bg-indigo-950/40',
            'border' => 'border-indigo-200/90 dark:border-indigo-900/60',
            'text' => 'text-indigo-900 dark:text-indigo-200',
            'desc' => 'text-indigo-700 dark:text-indigo-300/90',
            'icon' => 'bx-info-circle text-indigo-600 dark:text-indigo-400',
            'iconBg' => 'bg-indigo-100 dark:bg-indigo-900/50',
        ],
    ];

    $cfg = $types[$resolvedType] ?? $types['info'];
    $iconClass = $icon ?? $cfg['icon'];
    $alertId = 'alert-' . uniqid();
@endphp

<div
    id="{{ $alertId }}"
    {{ $attributes->merge(['class' => "p-4 sm:p-4.5 rounded-2xl border text-xs shadow-xs transition-all duration-200 flex items-start gap-3.5 {$cfg['bg']} {$cfg['border']} {$cfg['text']}"]) }}
    role="alert"
>
    <!-- Icon Container -->
    <div class="w-8 h-8 rounded-xl shrink-0 flex items-center justify-center {{ $cfg['iconBg'] }} mt-0.5">
        <i class="bx {{ $iconClass }} text-lg"></i>
    </div>

    <!-- Alert Text Body -->
    <div class="flex-1 min-w-0 pr-1">
        @if($title)
            <h4 class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm mb-0.5 tracking-tight">
                {{ $title }}
            </h4>
        @endif
        <div class="leading-relaxed font-medium {{ $cfg['desc'] }}">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            onclick="document.getElementById('{{ $alertId }}').remove()"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-black/5 dark:hover:bg-white/5 transition-colors shrink-0 cursor-pointer"
            aria-label="Dismiss alert"
        >
            <i class="bx bx-x text-lg"></i>
        </button>
    @endif
</div>
