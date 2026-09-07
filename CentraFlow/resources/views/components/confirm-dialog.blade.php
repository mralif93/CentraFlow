@props([
    'name' => 'confirm-action',
    'title' => 'Are you sure you want to proceed?',
    'message' => 'This action cannot be undone. Please confirm your decision.',
    'confirmText' => 'Confirm & Proceed',
    'cancelText' => 'Cancel',
    'variant' => 'danger', // danger (rose), warning (amber), info (indigo), success (emerald)
    'icon' => null,
])

@php
    $variants = [
        'danger' => [
            'icon' => 'bx-trash text-rose-600 dark:text-rose-400',
            'iconBg' => 'bg-rose-50 dark:bg-rose-950/60 border-rose-200/80 dark:border-rose-900/60 text-rose-600 dark:text-rose-400 ring-8 ring-rose-500/10 dark:ring-rose-500/20',
            'btn' => 'danger',
        ],
        'warning' => [
            'icon' => 'bx-error-alt text-amber-600 dark:text-amber-400',
            'iconBg' => 'bg-amber-50 dark:bg-amber-950/60 border-amber-200/80 dark:border-amber-900/60 text-amber-600 dark:text-amber-400 ring-8 ring-amber-500/10 dark:ring-amber-500/20',
            'btn' => 'warning',
        ],
        'info' => [
            'icon' => 'bx-info-circle text-indigo-600 dark:text-indigo-400',
            'iconBg' => 'bg-indigo-50 dark:bg-indigo-950/60 border-indigo-200/80 dark:border-indigo-900/60 text-indigo-600 dark:text-indigo-400 ring-8 ring-indigo-500/10 dark:ring-indigo-500/20',
            'btn' => 'primary',
        ],
        'success' => [
            'icon' => 'bx-check-circle text-emerald-600 dark:text-emerald-400',
            'iconBg' => 'bg-emerald-50 dark:bg-emerald-950/60 border-emerald-200/80 dark:border-emerald-900/60 text-emerald-600 dark:text-emerald-400 ring-8 ring-emerald-500/10 dark:ring-emerald-500/20',
            'btn' => 'success',
        ],
    ];

    $cfg = $variants[$variant] ?? $variants['danger'];
    $iconClass = $icon ?? $cfg['icon'];
    $modalId = "modal-confirm-{$name}";
@endphp

<div
    id="{{ $modalId }}"
    class="hidden fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <!-- Dark Dim Backdrop with Glassmorphism Blur -->
    <div
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity"
        onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
    ></div>

    <!-- Centered Modal Wrapper (Both mobile and desktop centered) -->
    <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-center shadow-2xl transition-all w-full max-w-md p-6 sm:p-8 animate__animated animate__zoomIn animate__faster">
            
            <!-- Top Right Close Button -->
            <button
                type="button"
                onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                class="absolute top-4 right-4 p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none cursor-pointer"
                aria-label="Close dialog"
            >
                <i class="bx bx-x text-2xl"></i>
            </button>

            <!-- Centered Status Icon with Glowing Outer Halo Ring -->
            <div class="flex justify-center mb-5 mt-1">
                <div class="w-16 h-16 rounded-full flex items-center justify-center border shadow-xs transition-transform duration-200 hover:scale-105 {{ $cfg['iconBg'] }}" id="{{ $modalId }}-icon-container">
                    <i class="bx {{ $iconClass }} text-3xl" id="{{ $modalId }}-icon"></i>
                </div>
            </div>

            <!-- Centered Header & Description -->
            <div class="space-y-2.5 max-w-sm mx-auto">
                <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight leading-snug" id="{{ $modalId }}-title">
                    {{ $title }}
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-normal" id="{{ $modalId }}-desc">
                    {{ $message }}
                </p>

                @if($slot->isNotEmpty())
                    <div class="mt-4 text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800 text-left">
                        {{ $slot }}
                    </div>
                @endif
            </div>

            <!-- Centered Action Buttons (Equal width grid on mobile and desktop) -->
            <div class="mt-7 pt-5 border-t border-slate-100 dark:border-slate-800/80 grid grid-cols-2 gap-3">
                <x-button
                    type="button"
                    variant="outline"
                    size="md"
                    class="w-full font-bold justify-center"
                    onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                >
                    {{ $cancelText }}
                </x-button>

                <!-- Target Form Submission Trigger -->
                <form id="{{ $modalId }}-form" method="POST" action="" class="w-full">
                    @csrf
                    <div id="{{ $modalId }}-method"></div>
                    <x-button
                        type="submit"
                        :variant="$cfg['btn']"
                        size="md"
                        id="{{ $modalId }}-submit-btn"
                        class="w-full font-bold justify-center shadow-lg"
                    >
                        {{ $confirmText }}
                    </x-button>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Helper to trigger this confirmation dynamically from anywhere -->
<script>
    if (typeof window.openConfirmDialog !== 'function') {
        window.openConfirmDialog = function({
            name = 'confirm-action',
            action = '',
            method = 'POST',
            title = null,
            message = null,
            confirmText = null
        }) {
            const modal = document.getElementById(`modal-confirm-${name}`);
            if (!modal) return;

            const form = document.getElementById(`modal-confirm-${name}-form`);
            const methodContainer = document.getElementById(`modal-confirm-${name}-method`);
            const titleEl = document.getElementById(`modal-confirm-${name}-title`);
            const descEl = document.getElementById(`modal-confirm-${name}-desc`);
            const btnEl = document.getElementById(`modal-confirm-${name}-submit-btn`);

            if (form && action) form.action = action;
            if (methodContainer) {
                if (method.toUpperCase() === 'DELETE') {
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="DELETE">';
                } else if (method.toUpperCase() === 'PUT' || method.toUpperCase() === 'PATCH') {
                    methodContainer.innerHTML = `<input type="hidden" name="_method" value="${method.toUpperCase()}">`;
                } else {
                    methodContainer.innerHTML = '';
                }
            }
            if (titleEl && title) titleEl.innerText = title;
            if (descEl && message) descEl.innerText = message;
            if (btnEl && confirmText) {
                const span = btnEl.querySelector('span') || btnEl;
                span.innerText = confirmText;
            }

            modal.classList.remove('hidden');
        };
    }
</script>
