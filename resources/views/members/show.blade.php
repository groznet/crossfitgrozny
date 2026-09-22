@extends('layouts.app')

@section('title', $member->full_name)

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="flex items-center gap-4 mb-4">
            @if ($member->photo_url)
                <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-16 h-16 rounded-full object-cover shrink-0">
            @else
                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium text-xl shrink-0">
                    {{ mb_substr($member->full_name, 0, 1) }}
                </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-xl font-semibold truncate">{{ $member->full_name }}</h1>
                <span class="inline-block text-xs font-medium rounded-full px-2 py-1 mt-1 {{ $member->subscription_status->colorClass() }}">
                    {{ $member->subscription_status->label() }}
                </span>
            </div>
        </div>

        @if ($member->status === \App\Enums\MemberStatus::Archived)
            <p class="text-sm text-gray-500 mb-4">{{ __('members.archived_notice') }}</p>
        @endif

        <dl class="text-sm text-gray-700 space-y-1 mb-4">
            <div class="flex justify-between">
                <dt class="text-gray-500">{{ __('members.phone') }}</dt>
                <dd>{{ $member->phone }}</dd>
            </div>
            @if ($member->birth_date)
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('members.birth_date') }}</dt>
                    <dd>{{ format_date($member->birth_date) }}</dd>
                </div>
            @endif
            @if ($member->preferred_time)
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('members.preferred_time') }}</dt>
                    <dd>{{ $member->preferred_time->label() }}</dd>
                </div>
            @endif
            @if ($member->note)
                <div>
                    <dt class="text-gray-500">{{ __('members.note') }}</dt>
                    <dd>{{ $member->note }}</dd>
                </div>
            @endif
        </dl>

        <div class="mb-4" x-data="{ copied: false }">
            <label class="block text-sm text-gray-500 mb-1">{{ __('members.public_link_label') }}</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    readonly
                    value="{{ $member->publicUrl() }}"
                    x-ref="publicLink"
                    onclick="this.select()"
                    class="flex-1 min-w-0 rounded-lg border border-gray-300 py-2 px-3 text-sm text-gray-600 bg-gray-50"
                >
                <button
                    type="button"
                    @click="navigator.clipboard.writeText($refs.publicLink.value); copied = true; setTimeout(() => copied = false, 2000)"
                    class="shrink-0 border border-gray-300 rounded-lg px-3 text-sm font-medium hover:bg-gray-50"
                >
                    <span x-show="!copied">{{ __('app.copy') }}</span>
                    <span x-show="copied">{{ __('app.copied') }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            @if (Route::has('payments.create'))
                <a href="{{ route('payments.create', $member) }}" class="col-span-2 bg-gray-900 text-white rounded-lg py-3 text-center text-sm font-medium hover:bg-gray-800">
                    {{ __('members.record_payment') }}
                </a>
            @endif
            <a href="{{ $member->whatsappUrl() }}" target="_blank" class="bg-green-600 text-white rounded-lg py-3 text-center text-sm font-medium hover:bg-green-700">
                {{ __('members.whatsapp') }}
            </a>
            <a href="{{ route('members.edit', $member) }}" class="border border-gray-300 rounded-lg py-3 text-center text-sm font-medium hover:bg-gray-50">
                {{ __('app.edit') }}
            </a>
            @if ($member->status === \App\Enums\MemberStatus::Archived)
                <form method="POST" action="{{ route('members.restore', $member) }}" class="col-span-2">
                    @csrf
                    <button type="submit" class="w-full border border-gray-300 rounded-lg py-3 text-sm font-medium hover:bg-gray-50">
                        {{ __('members.restore') }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('members.archive', $member) }}" class="col-span-2" x-data @submit.prevent="confirm('{{ __('app.confirm_delete') }}') && $el.submit()">
                    @csrf
                    <button type="submit" class="w-full border border-gray-300 text-red-600 rounded-lg py-3 text-sm font-medium hover:bg-red-50">
                        {{ __('members.archive') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    @include('payments._history', ['payments' => $member->payments])
@endsection
