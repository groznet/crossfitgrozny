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

        <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
            {{ __(config('services.smsru.verification_enabled') ? 'public.send_code' : 'public.submit') }}
        </button>
    </form>
@endsection
