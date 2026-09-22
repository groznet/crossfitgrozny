<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', __('app.app_name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between relative" x-data="{ open: false }">
            <a href="{{ route('public.community.index') }}" class="font-semibold text-lg">
                {{ __('app.app_name') }}
            </a>

            <nav class="hidden sm:flex items-center gap-4 text-sm">
                <a href="{{ route('public.community.index') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('app.nav_community') }}
                </a>
                <a href="{{ route('public.profile.create') }}" class="text-gray-600 hover:text-gray-900">
                    {{ __('app.nav_join') }}
                </a>
            </nav>

            <button
                type="button"
                @click="open = !open"
                class="sm:hidden -mr-2 p-2 text-gray-600"
                aria-label="Menu"
            >
                <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <nav
                x-show="open"
                x-cloak
                @click.outside="open = false"
                x-transition
                class="sm:hidden absolute right-4 top-14 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 text-sm"
            >
                <a href="{{ route('public.community.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                    {{ __('app.nav_community') }}
                </a>
                <a href="{{ route('public.profile.create') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                    {{ __('app.nav_join') }}
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1 flex justify-center px-4 py-10">
        <div class="w-full @yield('container-class', 'max-w-sm')">
            @yield('content')
        </div>
    </main>
</body>
</html>
