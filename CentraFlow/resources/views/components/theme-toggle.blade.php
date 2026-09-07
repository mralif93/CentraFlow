<button
    type="button"
    onclick="toggleTheme()"
    title="Toggle Theme"
    class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer items-center rounded-full p-1 transition-colors duration-300 ease-in-out bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 shadow-inner focus:outline-none"
    aria-label="Toggle Theme"
>
    <!-- Background Track Icons -->
    <div class="w-full flex items-center justify-between px-1 text-slate-400 pointer-events-none">
        <i class="bx bx-sun text-xs text-amber-500"></i>
        <i class="bx bx-moon text-xs text-indigo-400"></i>
    </div>

    <!-- Sliding Knob -->
    <span
        class="absolute left-1 top-1 h-6 w-6 rounded-full bg-white dark:bg-slate-900 shadow-md transform transition-transform duration-300 ease-in-out flex items-center justify-center border border-slate-200 dark:border-slate-700 translate-x-0 dark:translate-x-6 pointer-events-none"
    >
        <!-- Light Mode Icon -->
        <span class="block dark:hidden text-amber-500 text-xs flex items-center justify-center">
            <i class="bx bx-sun"></i>
        </span>
        <!-- Dark Mode Icon -->
        <span class="hidden dark:block text-indigo-400 text-xs flex items-center justify-center">
            <i class="bx bx-moon"></i>
        </span>
    </span>
</button>

<script>
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }
</script>
