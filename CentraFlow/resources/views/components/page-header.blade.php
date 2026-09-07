@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'badge' => null,
    'badgeVariant' => 'indigo', // indigo, emerald, amber, sky, rose
])

@php
    $badgeClasses = [
        'indigo' => 'bg-indigo-500/20 text-indigo-300 border-indigo-400/30',
        'emerald' => 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30',
        'amber' => 'bg-amber-500/20 text-amber-300 border-amber-400/30',
        'sky' => 'bg-sky-500/20 text-sky-300 border-sky-400/30',
        'rose' => 'bg-rose-500/20 text-rose-300 border-rose-400/30',
    ][$badgeVariant] ?? 'bg-indigo-500/20 text-indigo-300 border-indigo-400/30';
@endphp

<div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-6 sm:p-7 border border-indigo-800/40 shadow-xl shadow-indigo-950/40 text-white animate__animated animate__fadeIn">
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5 flex-wrap">
                @if($icon)
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-indigo-300 font-bold border border-white/10 shrink-0">
                        <i class="bx {{ $icon }} text-lg"></i>
                    </div>
                @endif
                <h1 class="text-xl sm:text-2xl font-black tracking-tight leading-tight">
                    {!! $title !!}
                </h1>
                @if($badge)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border flex items-center gap-1.5 {{ $badgeClasses }}">
                        {{ $badge }}
                    </span>
                @endif
            </div>
            @if($subtitle)
                <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">{{ $subtitle }}</p>
            @endif
        </div>

        @if(isset($actions) || trim($slot))
            <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                {{ $actions ?? $slot }}
            </div>
        @endif
    </div>
</div>
