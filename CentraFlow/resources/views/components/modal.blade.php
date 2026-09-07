@props([
    'name',
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'size' => 'md', // sm, md, lg, xl, 2xl, 4xl
])

@php
    $sizeClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '4xl' => 'sm:max-w-4xl',
    ][$size] ?? 'sm:max-w-md';
@endphp

<div
    id="modal-{{ $name }}"
    class="hidden fixed inset-0 z-[100] overflow-y-auto"
    aria-labelledby="modal-title-{{ $name }}"
    role="dialog"
    aria-modal="true"
>
    <!-- Full-screen Backdrop Blur & Dark Dim overlaying entire viewport and sidebar -->
    <div
        class="fixed inset-0 bg-slate-950/75 backdrop-blur-md transition-opacity"
        onclick="document.getElementById('modal-{{ $name }}').classList.add('hidden')"
    ></div>

    <!-- Modal Container (Mobile bottom sheet with rounded-t-[28px], desktop centered) -->
    <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
        <div class="relative transform overflow-hidden rounded-t-[28px] sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-left shadow-2xl transition-all w-full {{ $sizeClasses }} max-h-[92vh] sm:max-h-[90vh] flex flex-col animate__animated animate__fadeInUp sm:animate__zoomIn animate__faster">
            
            <!-- Mobile Drag Handle -->
            <div class="sm:hidden flex justify-center pt-3 pb-1">
                <div class="w-12 h-1.5 rounded-full bg-slate-200 dark:bg-slate-700"></div>
            </div>

            <!-- Styled Modal Header -->
            @if($title || isset($header))
                <div class="px-6 py-4 sm:px-7 sm:py-5 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/30 flex items-center justify-between gap-3 shrink-0">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <div class="flex items-center gap-3 min-w-0">
                            @if($icon)
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-800/80 flex items-center justify-center text-lg shrink-0">
                                    <i class="bx {{ $icon }}"></i>
                                </div>
                            @endif
                            <div class="min-w-0 pr-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight leading-snug" id="modal-title-{{ $name }}">
                                    {{ $title }}
                                </h3>
                                @if($subtitle)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-normal">{{ $subtitle }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Circular Close Button -->
                    <button
                        type="button"
                        onclick="document.getElementById('modal-{{ $name }}').classList.add('hidden')"
                        class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-all cursor-pointer shrink-0 focus:outline-none"
                        aria-label="Close modal"
                    >
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>
            @endif

            <!-- Modal Body (Scrollable with consistent padding) -->
            <div class="p-6 sm:p-7 overflow-y-auto flex-1 custom-scrollbar">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <!-- Dedicated Modal Footer Component Slot -->
                <div class="px-6 py-4 sm:px-7 sm:py-4.5 bg-slate-50/70 dark:bg-slate-950/40 border-t border-slate-100 dark:border-slate-800/80 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 shrink-0">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
