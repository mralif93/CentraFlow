@props([
    'label' => null,
    'name' => '',
    'id' => null,
    'value' => null,
    'min' => null,
    'max' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'icon' => 'bx bx-calendar',
])

@php
    $inputId = $id ?? $name;
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <label for="{{ $inputId }}" class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
            <i class="{{ $icon }} text-base"></i>
        </div>

        <input
            type="date"
            name="{{ $name }}"
            id="{{ $inputId }}"
            value="{{ old($name, $value) }}"
            @if($min) min="{{ $min }}" @endif
            @if($max) max="{{ $max }}" @endif
            @if($required) required @endif
            {{ $attributes->merge([
                'class' => 'w-full py-2.5 pl-10 pr-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border ' .
                    ($error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500' : 'border-slate-200 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20') .
                    ' text-slate-900 dark:text-white font-mono text-xs focus:outline-none transition-colors cursor-pointer'
            ]) }}
        >
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
