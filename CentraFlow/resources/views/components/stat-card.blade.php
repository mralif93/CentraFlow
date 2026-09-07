@props([
    'title',
    'value',
    'icon' => null,
    'change' => null,
    'changeType' => 'increase', // increase, decrease, neutral
    'subtitle' => null,
    'color' => 'indigo',        // indigo, emerald, amber, purple, blue, rose
])

@php
    $colorMap = [
        'indigo' => [
            'bg' => 'bg-indigo-50 dark:bg-indigo-500/10',
            'text' => 'text-indigo-600 dark:text-indigo-400',
        ],
        'emerald' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-500/10',
            'text' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'amber' => [
            'bg' => 'bg-amber-50 dark:bg-amber-500/10',
            'text' => 'text-amber-600 dark:text-amber-400',
        ],
        'purple' => [
            'bg' => 'bg-purple-50 dark:bg-purple-500/10',
            'text' => 'text-purple-600 dark:text-purple-400',
        ],
        'blue' => [
            'bg' => 'bg-blue-50 dark:bg-blue-500/10',
            'text' => 'text-blue-600 dark:text-blue-400',
        ],
        'rose' => [
            'bg' => 'bg-rose-50 dark:bg-rose-500/10',
            'text' => 'text-rose-600 dark:text-rose-400',
        ],
    ];

    $colors = $colorMap[$color] ?? $colorMap['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-md transition-colors']) }}>
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $title }}</span>
        @if($icon)
            <span class="p-2 rounded-xl {{ $colors['bg'] }} {{ $colors['text'] }}">
                <i class="{{ $icon }} text-lg"></i>
            </span>
        @endif
    </div>

    <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white font-mono">
        {{ $value }}
    </div>

    @if($change)
        <div class="mt-1 text-[11px] font-medium flex items-center gap-1 {{ $changeType === 'increase' ? 'text-emerald-600 dark:text-emerald-400' : ($changeType === 'decrease' ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
            @if($changeType === 'increase')
                <i class="bx bx-trending-up"></i>
            @elseif($changeType === 'decrease')
                <i class="bx bx-trending-down"></i>
            @endif
            <span>{{ $change }}</span>
        </div>
    @endif

    @if($subtitle)
        <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
            {{ $subtitle }}
        </div>
    @endif
</div>
