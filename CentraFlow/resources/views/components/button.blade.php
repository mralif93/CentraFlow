@props([
    'variant' => 'primary', // primary, secondary, danger, success, warning, outline, ghost
    'size' => 'md',        // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/25 border-indigo-500/30 focus:ring-indigo-500/30',
        'secondary' => 'bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 shadow-xs focus:ring-slate-500/30',
        'outline' => 'bg-transparent text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 focus:ring-slate-500/30',
        'danger' => 'bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-900/50 focus:ring-rose-500/30',
        'warning' => 'bg-amber-600 hover:bg-amber-500 text-white shadow-md shadow-amber-600/25 border-amber-500/30 focus:ring-amber-500/30',
        'success' => 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/25 border-emerald-500/30 focus:ring-emerald-500/30',
        'ghost' => 'bg-transparent hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border-transparent',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-4 py-2.5 text-xs rounded-xl gap-2 font-bold',
        'lg' => 'px-5 py-3 text-sm rounded-xl gap-2.5 font-bold',
    ];

    $baseClass = 'inline-flex items-center justify-center border font-semibold transition-all duration-150 focus:outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer ';
    $classes = $baseClass . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} text-base"></i>
        @endif
        @if(trim($slot))
            <span>{{ $slot }}</span>
        @endif
        @if($iconRight)
            <i class="{{ $iconRight }} text-base"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} text-base"></i>
        @endif
        @if(trim($slot))
            <span>{{ $slot }}</span>
        @endif
        @if($iconRight)
            <i class="{{ $iconRight }} text-base"></i>
        @endif
    </button>
@endif
