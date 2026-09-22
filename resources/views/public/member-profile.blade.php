@extends('layouts.guest')

@section('title', __('public.member_profile_title'))

@section('content')
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
            {{ __('public.contact_adam') }}
        </a>
    </div>
@endsection
