@extends('layouts.guest')

@section('title', __('public.verify_title'))

@section('content')
    <h1 class="text-xl font-semibold mb-2 text-center">{{ __('public.verify_title') }}</h1>
    <p class="text-sm text-gray-500 mb-6 text-center">{{ __('public.verify_intro', ['phone' => $phone]) }}</p>

    <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @if (isset($status))
            <div class="rounded-lg bg-green-50 text-green-800 px-4 py-3 text-sm">{{ $status }}</div>
        @endif
        @if (isset($error))
            <div class="rounded-lg bg-red-50 text-red-800 px-4 py-3 text-sm">{{ $error }}</div>
        @endif

        @if ($expired ?? false)
            <a href="{{ route('public.profile.create') }}" class="block w-full text-center border border-gray-300 rounded-lg py-3 text-base font-medium hover:bg-gray-50">
                {{ __('public.start_over') }}
            </a>
        @else
            <form method="POST" action="{{ route('public.profile.verify-code') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="phone" value="{{ $phone }}">
                <div>
                    <label for="code" class="block text-sm font-medium mb-1">{{ __('public.code_field') }}</label>
                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        name="code"
                        id="code"
                        autofocus
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base text-center tracking-widest focus:border-gray-500 focus:ring-gray-500"
                    >
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
                    {{ __('public.verify_submit') }}
                </button>
            </form>

            <form method="POST" action="{{ route('public.profile.resend-code') }}">
                @csrf
                <input type="hidden" name="phone" value="{{ $phone }}">
                <button type="submit" class="w-full text-sm text-gray-500 underline">{{ __('public.resend_code') }}</button>
            </form>
        @endif
    </div>
@endsection
