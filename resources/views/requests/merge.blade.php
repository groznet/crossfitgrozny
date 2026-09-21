@extends('layouts.app')

@section('title', __('requests.merge_title'))

@section('content')
    <h1 class="text-xl font-semibold mb-2">{{ __('requests.merge_title') }}</h1>
    <p class="text-sm text-gray-500 mb-4">
        {{ __('requests.merge_intro', ['target' => $target->full_name, 'pending' => $member->full_name]) }}
    </p>

    <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="font-medium mb-2">{{ $target->full_name }}</div>
            <dl class="space-y-1 text-gray-600">
                <div>{{ __('members.phone') }}: {{ $target->phone }}</div>
                <div>{{ __('members.birth_date') }}: {{ format_date($target->birth_date) ?? '—' }}</div>
                <div>{{ __('members.preferred_time') }}: {{ $target->preferred_time?->label() ?? '—' }}</div>
                <div>{{ __('members.note') }}: {{ $target->note ?? '—' }}</div>
            </dl>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="font-medium mb-2">{{ $member->full_name }}</div>
            <dl class="space-y-1 text-gray-600">
                <div>{{ __('members.phone') }}: {{ $member->phone }}</div>
                <div>{{ __('members.birth_date') }}: {{ format_date($member->birth_date) ?? '—' }}</div>
                <div>{{ __('members.preferred_time') }}: {{ $member->preferred_time?->label() ?? '—' }}</div>
                <div>{{ __('members.note') }}: {{ $member->note ?? '—' }}</div>
            </dl>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('requests.merge', [$member, $target]) }}"
        x-data
        @submit.prevent="confirm('{{ __('requests.confirm_merge') }}') && $el.submit()"
    >
        @csrf
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
                {{ __('requests.merge') }}
            </button>
            <a href="{{ route('requests.index') }}" class="flex-1 text-center border border-gray-300 rounded-lg py-3 text-base font-medium hover:bg-gray-50">
                {{ __('app.cancel') }}
            </a>
        </div>
    </form>
@endsection
