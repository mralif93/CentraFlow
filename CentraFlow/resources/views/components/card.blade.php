@props([
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'footer' => null,
    'noPadding' => false,
    'variant' => 'default', // default, frosted, flat, highlight
])

@php
    $variants = [
        'default' => 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-xs dark:shadow-md',
        'frosted' => 'bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-slate-200/80 dark:border-slate-800/80 shadow-md',
        'flat' => 'bg-slate-50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800',
        'highlight' => 'bg-gradient-to-br from-indigo-50/50 to-white dark:from-indigo-950/20 dark:to-slate-900 border-indigo-200/60 dark:border-indigo-800/40 shadow-md',
    ];

    $cardClass = 'rounded-2xl border overflow-hidden transition-all duration-200 ' . ($variants[$variant] ?? $variants['default']);
@endphp

<div {{ $attributes->merge(['class' => $cardClass]) }}>
    @if($title || $subtitle || isset($header) || $action)
        <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-4">
            <div>
                @if(isset($header))
                    {{ $header }}
                @else
                    @if($title)
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white tracking-tight">{!! $title !!}</h3>
                    @endif
                    @if($subtitle)
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-normal">{!! $subtitle !!}</p>
                    @endif
                @endif
            </div>

            @if($action)
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $noPadding ? '' : 'p-5 sm:p-6' }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400">
            {{ $footer }}
        </div>
    @endif
</div>
