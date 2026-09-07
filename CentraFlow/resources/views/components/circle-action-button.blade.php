@props([
    'icon',
    'variant' => 'indigo', // indigo, emerald, amber, rose, slate, purple, sky
    'size' => 'md',        // sm, md, lg
    'href' => null,
    'type' => 'button',
    'title' => null,
    'disabled' => false,
])

@php
    $variants = [
        'indigo' => 'bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 text-indigo-600 dark:text-indigo-400 border-indigo-200/80 dark:border-indigo-800/80 hover:shadow-indigo-500/20',
        'emerald' => 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/80 text-emerald-600 dark:text-emerald-400 border-emerald-200/80 dark:border-emerald-800/80 hover:shadow-emerald-500/20',
        'amber' => 'bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/80 text-amber-600 dark:text-amber-400 border-amber-200/80 dark:border-amber-800/80 hover:shadow-amber-500/20',
        'rose' => 'bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/80 text-rose-600 dark:text-rose-400 border-rose-200/80 dark:border-rose-900/80 hover:shadow-rose-500/20',
        'slate' => 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:shadow-slate-500/20',
        'purple' => 'bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/60 dark:hover:bg-purple-900/80 text-purple-600 dark:text-purple-400 border-purple-200/80 dark:border-purple-800/80 hover:shadow-purple-500/20',
        'sky' => 'bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/60 dark:hover:bg-sky-900/80 text-sky-600 dark:text-sky-400 border-sky-200/80 dark:border-sky-800/80 hover:shadow-sky-500/20',
    ];

    $sizes = [
        'sm' => 'w-7 h-7 text-sm',
        'md' => 'w-8 h-8 text-base',
        'lg' => 'w-10 h-10 text-lg',
    ];

    $baseClasses = 'inline-flex items-center justify-center rounded-full border shadow-xs hover:shadow-md transition-all duration-150 transform active:scale-95 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ';
    $classes = $baseClasses . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['indigo']);
@endphp

@if($href)
    <a
        href="{{ $href }}"
        @if($title) title="{{ $title }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        <i class="{{ $icon }}"></i>
    </a>
@else
    <button
        type="{{ $type }}"
        @if($title) title="{{ $title }}" @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        <i class="{{ $icon }}"></i>
    </button>
@endif
