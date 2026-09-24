@extends('layouts.guest')

@section('title', __('public.member_profile_title'))

@section('content')
    @if (session('status'))
        <div class="bg-green-50 text-green-800 rounded-lg p-3 mb-4 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
        @if ($member->photo_url)
            <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-20 h-20 rounded-full object-cover mx-auto mb-3">
        @else
            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium text-2xl mx-auto mb-3">
                {{ mb_substr($member->full_name, 0, 1) }}
            </div>
        @endif

        <h1 class="text-lg font-semibold mb-1">{{ $member->full_name }}</h1>

        @if ($member->status === \App\Enums\MemberStatus::Pending)
            <p class="text-sm text-gray-500 mt-2">{{ __('public.member_pending_notice') }}</p>
        @elseif ($member->status === \App\Enums\MemberStatus::Archived)
            <p class="text-sm text-gray-500 mt-2">{{ __('public.member_archived_notice') }}</p>
        @else
            <span class="inline-block text-xs font-medium rounded-full px-2 py-1 mt-1 {{ $member->subscription_status->colorClass() }}">
                {{ $member->subscription_status->label() }}
            </span>

            @if ($member->latestPayment)
                <p class="text-sm text-gray-500 mt-2">
                    {{ __('members.plan_and_expiry', ['date' => format_date($member->latestPayment->valid_until)]) }}
                </p>
            @endif
        @endif

        <a
            href="{{ $adminWhatsappUrl }}"
            target="_blank"
            class="block w-full bg-green-600 text-white rounded-lg py-3 text-center text-sm font-medium hover:bg-green-700 mt-6"
        >
            {{ __('public.contact_trainer') }}
        </a>
    </div>

    @if ($member->status === \App\Enums\MemberStatus::Active)
        <form method="POST" action="{{ route('public.member.username', $member->public_token) }}" class="bg-white rounded-xl shadow-sm p-6 mt-4">
            @csrf
            <label for="username" class="block text-sm font-medium mb-1">{{ __('public.username_label') }}</label>
            <div class="flex items-center rounded-lg border border-gray-300 focus-within:border-gray-500 overflow-hidden">
                <span class="pl-3 text-gray-400 text-base shrink-0">/u/</span>
                <input
                    type="text"
                    name="username"
                    id="username"
                    value="{{ old('username', $member->username) }}"
                    autocapitalize="none"
                    autocomplete="off"
                    spellcheck="false"
                    maxlength="30"
                    class="flex-1 min-w-0 border-0 py-3 pr-4 pl-1 text-base focus:ring-0"
                >
            </div>
            @error('username')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @else
                <p class="text-xs text-gray-500 mt-1">{{ __('public.username_hint') }}</p>
            @enderror

            <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-3 text-sm font-medium hover:bg-gray-800 mt-3">
                {{ __('public.username_save') }}
            </button>

            <a href="{{ $member->profileUrl() }}" class="block text-center text-sm text-gray-600 underline mt-3 break-all">
                {{ $member->profileUrl() }}
            </a>
        </form>
    @endif
@endsection
