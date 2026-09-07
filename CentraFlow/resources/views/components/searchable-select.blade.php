@props([
    'label' => null,
    'name' => '',
    'id' => null,
    'options' => [], // Array of ['value' => '...', 'label' => '...', 'sub' => '...'] or key => value
    'selected' => null,
    'placeholder' => 'Select an option...',
    'searchPlaceholder' => 'Search options...',
    'required' => false,
    'error' => null,
    'hint' => null,
])

@php
    $dropdownId = $id ?? 'searchable-select-' . ($name ? $name : uniqid());
    
    // Normalize options into consistent [value, label, sub] structure
    $normalizedOptions = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $normalizedOptions[] = [
                'value' => (string)($opt['value'] ?? $key),
                'label' => (string)($opt['label'] ?? $opt['name'] ?? $key),
                'sub' => (string)($opt['sub'] ?? $opt['code'] ?? ''),
            ];
        } else {
            $normalizedOptions[] = [
                'value' => (string)$key,
                'label' => (string)$opt,
                'sub' => '',
            ];
        }
    }

    $initialSelected = collect($normalizedOptions)->firstWhere('value', (string)$selected);
    $initialLabel = $initialSelected ? $initialSelected['label'] : $placeholder;
@endphp

<div class="space-y-1.5 w-full relative" id="{{ $dropdownId }}">
    @if($label)
        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <!-- Hidden Native Form Input -->
    <input type="hidden" name="{{ $name }}" id="{{ $dropdownId }}-input" value="{{ $selected }}">

    <!-- Trigger Button -->
    <div class="relative">
        <button
            type="button"
            onclick="toggleSearchableSelect('{{ $dropdownId }}')"
            id="{{ $dropdownId }}-btn"
            class="w-full py-2.5 pl-3.5 pr-10 rounded-xl bg-slate-50 dark:bg-slate-800 border {{ $error ? 'border-rose-500' : 'border-slate-200 dark:border-slate-700' }} text-slate-900 dark:text-white text-xs flex items-center justify-between transition-colors focus:ring-2 focus:ring-indigo-500/20 outline-none text-left cursor-pointer"
        >
            <span id="{{ $dropdownId }}-display" class="{{ $initialSelected ? 'font-semibold' : 'text-slate-400' }}">
                {{ $initialLabel }}
            </span>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                <i class="bx bx-chevron-down text-lg transition-transform duration-200" id="{{ $dropdownId }}-chevron"></i>
            </div>
        </button>

        <!-- Dropdown Panel (Pure Tailwind, Dark Mode Ready) -->
        <div
            id="{{ $dropdownId }}-panel"
            class="hidden absolute left-0 right-0 z-50 mt-1.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate__animated animate__fadeIn animate__faster"
        >
            <!-- In-Dropdown Search Field -->
            <div class="p-2 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/40">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <i class="bx bx-search text-sm"></i>
                    </div>
                    <input
                        type="text"
                        placeholder="{{ $searchPlaceholder }}"
                        onkeyup="filterSearchableSelect('{{ $dropdownId }}', this.value)"
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>
            </div>

            <!-- Options List Container -->
            <ul id="{{ $dropdownId }}-list" class="max-h-56 overflow-y-auto p-1.5 space-y-0.5 text-xs divide-y-0">
                <li
                    onclick="selectSearchableOption('{{ $dropdownId }}', '', '{{ addslashes($placeholder) }}')"
                    class="px-3 py-2 rounded-xl text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors"
                >
                    {{ $placeholder }}
                </li>
                @foreach ($normalizedOptions as $opt)
                    <li
                        onclick="selectSearchableOption('{{ $dropdownId }}', '{{ addslashes($opt['value']) }}', '{{ addslashes($opt['label']) }}')"
                        data-value="{{ $opt['value'] }}"
                        data-label="{{ strtolower($opt['label'] . ' ' . $opt['sub']) }}"
                        class="searchable-item px-3 py-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-400 cursor-pointer flex items-center justify-between transition-colors"
                    >
                        <span class="font-medium">{{ $opt['label'] }}</span>
                        @if($opt['sub'])
                            <span class="text-[10px] font-mono text-slate-400">{{ $opt['sub'] }}</span>
                        @endif
                    </li>
                @endforeach
                <li id="{{ $dropdownId }}-no-results" class="hidden px-3 py-3 text-center text-slate-400 text-xs">
                    No matching results found
                </li>
            </ul>
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

<!-- Pure Vanilla JS Dropdown Engine with Outside Click Listener -->
<script>
    if (typeof window.toggleSearchableSelect !== 'function') {
        window.toggleSearchableSelect = function(id) {
            const panel = document.getElementById(`${id}-panel`);
            const chevron = document.getElementById(`${id}-chevron`);
            if (panel) {
                const isHidden = panel.classList.contains('hidden');
                // Close any other open panels
                document.querySelectorAll('[id$="-panel"]').forEach(p => p.classList.add('hidden'));
                document.querySelectorAll('[id$="-chevron"]').forEach(c => c.classList.remove('rotate-180'));

                if (isHidden) {
                    panel.classList.remove('hidden');
                    if (chevron) chevron.classList.add('rotate-180');
                    const input = panel.querySelector('input');
                    if (input) input.focus();
                }
            }
        };

        window.selectSearchableOption = function(id, value, label) {
            const hiddenInput = document.getElementById(`${id}-input`);
            const displaySpan = document.getElementById(`${id}-display`);
            const panel = document.getElementById(`${id}-panel`);
            const chevron = document.getElementById(`${id}-chevron`);

            if (hiddenInput) hiddenInput.value = value;
            if (displaySpan) {
                displaySpan.innerText = label;
                displaySpan.classList.remove('text-slate-400');
                displaySpan.classList.add('font-semibold');
            }
            if (panel) panel.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        };

        window.filterSearchableSelect = function(id, term) {
            const list = document.getElementById(`${id}-list`);
            const noResults = document.getElementById(`${id}-no-results`);
            if (!list) return;

            const items = list.querySelectorAll('.searchable-item');
            const cleanTerm = term.toLowerCase().trim();
            let matches = 0;

            items.forEach(item => {
                const label = item.getAttribute('data-label') || '';
                if (label.includes(cleanTerm)) {
                    item.style.display = 'flex';
                    matches++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.style.display = matches === 0 ? 'block' : 'none';
            }
        };

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="searchable-select-"]')) {
                document.querySelectorAll('[id$="-panel"]').forEach(p => p.classList.add('hidden'));
                document.querySelectorAll('[id$="-chevron"]').forEach(c => c.classList.remove('rotate-180'));
            }
        });
    }
</script>
