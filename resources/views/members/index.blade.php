@extends('layouts.app')

@section('title', __('members.title'))

@section('content')
    <h1 class="text-xl font-semibold mb-4">{{ __('members.title') }}</h1>

    {{-- Counters --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4 text-sm">
        <a href="{{ route('members.index', array_merge(request()->query(), ['filter' => 'active'])) }}"
           class="rounded-lg bg-green-50 text-green-800 px-3 py-2 text-center {{ $filter === 'active' ? 'ring-2 ring-green-500' : '' }}">
            <div class="text-lg font-semibold">{{ $counts->get('active', 0) }}</div>
            {{ __('members.counter_active') }}
        </a>
        <a href="{{ route('members.index', array_merge(request()->query(), ['filter' => 'expiring'])) }}"
           class="rounded-lg bg-yellow-50 text-yellow-800 px-3 py-2 text-center {{ $filter === 'expiring' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="text-lg font-semibold">{{ $counts->get('expiring', 0) }}</div>
            {{ __('members.counter_expiring') }}
        </a>
        <a href="{{ route('members.index', array_merge(request()->query(), ['filter' => 'expired'])) }}"
           class="rounded-lg bg-red-50 text-red-800 px-3 py-2 text-center {{ $filter === 'expired' ? 'ring-2 ring-red-500' : '' }}">
            <div class="text-lg font-semibold">{{ $counts->get('expired', 0) }}</div>
            {{ __('members.counter_expired') }}
        </a>
        <a href="{{ route('members.index', array_merge(request()->query(), ['filter' => 'none'])) }}"
           class="rounded-lg bg-gray-100 text-gray-700 px-3 py-2 text-center {{ $filter === 'none' ? 'ring-2 ring-gray-500' : '' }}">
            <div class="text-lg font-semibold">{{ $counts->get('none', 0) }}</div>
            {{ __('members.counter_none') }}
        </a>
    </div>

    @if ($filter !== '')
        <a href="{{ route('members.index', array_merge(request()->query(), ['filter' => null])) }}"
           class="inline-block mb-4 text-sm text-gray-600 underline">{{ __('members.filter_all') }}</a>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('members.index') }}" class="mb-4">
        @if ($showArchived)
            <input type="hidden" name="archived" value="1">
        @endif
        <input
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="{{ __('members.search_placeholder') }}"
            class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
        >
    </form>

    {{-- Member list --}}
    <div class="space-y-2 mb-4">
        @forelse ($members as $member)
            <a href="{{ route('members.show', $member) }}" class="flex items-center gap-3 bg-white rounded-xl shadow-sm p-3 hover:bg-gray-50">
                @if ($member->photo_url)
                    <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-12 h-12 rounded-full object-cover shrink-0">
                @else
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium shrink-0">
                        {{ mb_substr($member->full_name, 0, 1) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-medium truncate">{{ $member->full_name }}</div>
                    <div class="text-sm text-gray-500 truncate">
                        @if ($member->latestPayment)
                            {{ $member->latestPayment->plan->label() }} ·
                            {{ __('members.plan_and_expiry', ['date' => format_date($member->latestPayment->valid_until)]) }}
                        @else
                            {{ __('members.no_payments') }}
                        @endif
                    </div>
                </div>
                <span class="shrink-0 text-xs font-medium rounded-full px-2 py-1 {{ $member->subscription_status->colorClass() }}">
                    {{ $member->subscription_status->label() }}
                </span>
            </a>
        @empty
            <p class="text-gray-500 text-sm">{{ __('members.no_members') }}</p>
        @endforelse
    </div>

    <a href="{{ route('members.index', ['archived' => $showArchived ? null : 1]) }}" class="text-sm text-gray-500 underline">
        {{ $showArchived ? __('members.show_active') : __('members.show_archived') }}
    </a>
@endsection
