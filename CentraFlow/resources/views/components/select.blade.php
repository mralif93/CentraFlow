@props([
    'label' => null,
    'name' => '',
    'id' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
])

@php
    $selectId = $id ?? $name;
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <label for="{{ $selectId }}" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $selectId }}"
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'w-full h-10 py-2.5 pl-3.5 pr-10 rounded-xl bg-slate-50 dark:bg-slate-800 border ' .
                    ($error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20') .
                    ' text-slate-900 dark:text-white text-xs focus:outline-none appearance-none transition-all cursor-pointer'
            ]) }}
        >
            {{ $slot }}
        </select>

        <!-- Custom Down Chevron Indicator -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-500">
            <i class="bx bx-chevron-down text-lg"></i>
        </div>
    </div>

    @if($hint && !$error)
        <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="text-[11px] text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1">
            <i class="bx bx-error-circle"></i>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
