@extends('layouts.guest')

@section('title', __('app.login_title'))

@section('content')
    <h1 class="text-xl font-semibold mb-6 text-center">{{ __('app.app_name') }}</h1>

    <form method="POST" action="{{ route('login.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label for="login" class="block text-sm font-medium mb-1">{{ __('app.login_field') }}</label>
            <input
                type="text"
                name="login"
                id="login"
                value="{{ old('login') }}"
                autofocus
                class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
            >
            @error('login')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">{{ __('app.password_field') }}</label>
            <input
                type="password"
                name="password"
                id="password"
                class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="w-full bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800"
        >
            {{ __('app.login_button') }}
        </button>
    </form>
@endsection
