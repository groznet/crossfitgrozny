@extends('layouts.guest')

@section('title', __('public.profile_title'))

@section('content')
    <h1 class="text-xl font-semibold mb-2 text-center">{{ __('public.profile_title') }}</h1>
    <p class="text-sm text-gray-500 mb-6 text-center">{{ __('public.profile_intro') }}</p>

    <form method="POST" action="{{ route('public.profile.send-code') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf

        {{-- Honeypot: hidden from real visitors, invisible trap for bots --}}
        <div class="absolute -left-[9999px]" aria-hidden="true">
            <label for="company">Company</label>
            <input type="text" name="company" id="company" tabindex="-1" autocomplete="off">
        </div>

        @include('members._form')

        <div>
            <label for="captcha_answer" class="block text-sm font-medium mb-1">
                {{ __('public.captcha_question', ['a' => $captchaA, 'b' => $captchaB]) }}
            </label>
            <input
                type="number"
                name="captcha_answer"
                id="captcha_answer"
                inputmode="numeric"
                class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
            >
            @error('captcha_answer')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
            {{ __(config('services.smsru.verification_enabled') ? 'public.send_code' : 'public.submit') }}
        </button>
    </form>
@endsection
