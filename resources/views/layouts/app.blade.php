<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
    x-init="
        $watch('dark', val => {
            document.documentElement.classList.toggle('dark', val);
            localStorage.setItem('theme', val ? 'dark' : 'light');
        });
    "
    x-cloak
    :class="{ 'dark': dark }"
    class="">
<head>
    <meta charset="utf-8">
    {{-- Apply saved theme before first paint. Light mode is the default. --}}
    <script>
        try {
            if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
        } catch (e) {}
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LMS') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen">
    {{-- Dark Mode Toggle --}}
    <button x-show="$matchMedia('(min-width: 640px)').matches"
            @click="dark = !dark"
            class="group fixed bottom-4 right-4 z-50 flex items-center justify-center w-12 h-12 rounded-2xl bg-white/90 text-indigo-600 ring-1 ring-blue-100 shadow-lg backdrop-blur cursor-pointer transition-all duration-200 hover:scale-110 hover:shadow-xl dark:bg-slate-800/90 dark:text-amber-300 dark:ring-white/10"
            :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
        {{-- Moon with sparkle (light mode) --}}
        <svg x-show="!dark" class="w-6 h-6 transition-transform duration-300 group-hover:-rotate-12" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/><path d="M19 3v4M21 5h-4"/>
        </svg>
        {{-- Sun (dark mode) --}}
        <svg x-show="dark" class="w-6 h-6 transition-transform duration-300 group-hover:rotate-45" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
        </svg>
    </button>
    {{-- Sidebar --}}
    @section('sidebar')
        @include('partials.sidebar.main')
    @show

    {{-- Main Content Area with sm:ml-64 --}}
    <div class="p-4 sm:ml-64 min-h-screen flex flex-col justify-between">
        <div class="flex-1">
            {{-- Top Navbar (optional, provided by role layouts) --}}
            @yield('navbar')

            {{-- Flash Messages --}}
            @include('partials.flash-messages')

            @yield('content')
        </div>

        {{-- Footer --}}
        @include('partials.footer.main')
    </div>

    @yield('scripts')
</body>
</html>
