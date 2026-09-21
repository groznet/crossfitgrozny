<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', __('app.app_name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ Route::has('members.index') ? route('members.index') : '#' }}" class="font-semibold text-lg">
                {{ __('app.app_name') }}
            </a>
            <nav class="flex items-center gap-4 text-sm">
                @if (Route::has('requests.index'))
                    <a href="{{ route('requests.index') }}" class="text-gray-600 hover:text-gray-900">
                        {{ __('app.nav_requests') }}
                    </a>
                @endif
                @if (Route::has('settings.prices.edit'))
                    <a href="{{ route('settings.prices.edit') }}" class="text-gray-600 hover:text-gray-900">
                        {{ __('app.nav_settings') }}
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-gray-900">{{ __('app.logout') }}</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-3xl w-full mx-auto px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
