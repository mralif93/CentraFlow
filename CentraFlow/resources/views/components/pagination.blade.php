@props([
    'paginator',
    'perPageOptions' => [10, 25, 50, 100],
    'perPageParam' => 'per_page',
])

@if ($paginator && $paginator->total() > 0)
    <div {{ $attributes->merge(['class' => 'px-4 py-3.5 sm:px-6 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs']) }}>
        
        <!-- Left: Per-Page Limit Selector & Results Counter -->
        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 w-full sm:w-auto">
            <!-- Items Per Page Dropdown -->
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                {{-- Preserve existing query parameters except page and per_page --}}
                @foreach (request()->except(['page', $perPageParam]) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $subValue)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $subValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <span class="text-slate-500 dark:text-slate-400 font-medium">Show</span>
                <div class="relative">
                    <select
                        name="{{ $perPageParam }}"
                        onchange="this.form.submit()"
                        class="py-1.5 pl-2.5 pr-7 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none appearance-none transition-all cursor-pointer"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" {{ (int) request($perPageParam, $paginator->perPage()) === (int) $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                        <i class="bx bx-chevron-down text-sm"></i>
                    </div>
                </div>
                <span class="text-slate-500 dark:text-slate-400 font-medium hidden xs:inline">per page</span>
            </form>

            <span class="text-slate-300 dark:text-slate-700 hidden sm:inline">&bull;</span>

            <!-- Showing results counter -->
            <div class="text-slate-500 dark:text-slate-400">
                Showing <span class="font-bold font-mono text-slate-800 dark:text-white">{{ $paginator->firstItem() ?? 0 }}</span>
                to <span class="font-bold font-mono text-slate-800 dark:text-white">{{ $paginator->lastItem() ?? 0 }}</span>
                of <span class="font-bold font-mono text-slate-800 dark:text-white">{{ number_format($paginator->total()) }}</span> results
            </div>
        </div>

        <!-- Right: Modern Page Numbers Navigation -->
        @if ($paginator->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1.5 shrink-0 select-none">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 dark:text-slate-700 bg-slate-100/50 dark:bg-slate-800/40 cursor-not-allowed" aria-disabled="true">
                        <i class="bx bx-chevron-left text-lg"></i>
                    </span>
                @else
                    <a
                        href="{{ $paginator->previousPageUrl() }}"
                        rel="prev"
                        class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200 dark:border-slate-700 transition"
                        title="Previous page"
                    >
                        <i class="bx bx-chevron-left text-lg"></i>
                    </a>
                @endif

                {{-- Pagination Elements (Pages & Separator Ellipsis) --}}
                @foreach ($paginator->render()->elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="w-8 h-8 flex items-center justify-center text-slate-400 dark:text-slate-500 text-xs">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs bg-indigo-600 text-white shadow-md shadow-indigo-600/30" aria-current="page">
                                    {{ $page }}
                                </span>
                            @else
                                <a
                                    href="{{ $url }}"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100/60 dark:bg-slate-800/60 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a
                        href="{{ $paginator->nextPageUrl() }}"
                        rel="next"
                        class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-950 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200 dark:border-slate-700 transition"
                        title="Next page"
                    >
                        <i class="bx bx-chevron-right text-lg"></i>
                    </a>
                @else
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-300 dark:text-slate-700 bg-slate-100/50 dark:bg-slate-800/40 cursor-not-allowed" aria-disabled="true">
                        <i class="bx bx-chevron-right text-lg"></i>
                    </span>
                @endif
            </nav>
        @endif

    </div>
@endif
