@props([
    'variant' => 'indigo', // indigo, emerald, amber, rose, slate, purple, sky
    'size' => 'md',       // sm, md, lg
    'dot' => false,
])

@php
    $variants = [
        'indigo' => 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-400/30',
        'emerald' => 'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-400/30',
        'amber' => 'bg-amber-50 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-400/30',
        'rose' => 'bg-rose-50 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-400/30',
        'slate' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
        'purple' => 'bg-purple-50 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-400/30',
        'sky' => 'bg-sky-50 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-400/30',
    ];

    $dotColors = [
        'indigo' => 'bg-indigo-500',
        'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500',
        'rose' => 'bg-rose-500',
        'slate' => 'bg-slate-400',
        'purple' => 'bg-purple-500',
        'sky' => 'bg-sky-500',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];

    $classes = 'inline-flex items-center gap-1.5 font-bold rounded-full border ' . ($variants[$variant] ?? $variants['indigo']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $dotClass = $dotColors[$variant] ?? $dotColors['indigo'];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} animate-pulse"></span>
    @endif
    {{ $slot }}
</span>
