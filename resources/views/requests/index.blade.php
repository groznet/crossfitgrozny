@extends('layouts.app')

@section('title', __('requests.title'))

@section('content')
    <h1 class="text-xl font-semibold mb-4">{{ __('requests.title') }}</h1>

    <div class="space-y-3">
        @forelse ($pending as $member)
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="flex items-center gap-3 mb-2">
                    @if ($member->photo_url)
                        <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-12 h-12 rounded-full object-cover shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium shrink-0">
                            {{ mb_substr($member->full_name, 0, 1) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="font-medium truncate">{{ $member->full_name }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ $member->phone }}</div>
                    </div>
                </div>

                @if ($member->duplicateOf)
                    <div class="text-xs bg-yellow-50 text-yellow-800 rounded-lg px-3 py-2 mb-2">
                        {{ __('requests.possible_duplicate') }}:
                        {{ __('requests.duplicate_of', ['name' => $member->duplicateOf->full_name]) }}
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2 text-sm">
                    <a href="{{ route('requests.edit', $member) }}" class="col-span-2 bg-gray-900 text-white rounded-lg py-2.5 text-center font-medium hover:bg-gray-800">
                        {{ __('requests.approve') }}
                    </a>
                    @if ($member->duplicateOf)
                        <a href="{{ route('requests.merge.confirm', [$member, $member->duplicateOf]) }}" class="border border-gray-300 rounded-lg py-2.5 text-center font-medium hover:bg-gray-50">
                            {{ __('requests.merge') }}
                        </a>
                    @endif
                    <form
                        method="POST"
                        action="{{ route('requests.reject', $member) }}"
                        class="{{ $member->duplicateOf ? '' : 'col-span-2' }}"
                        x-data
                        @submit.prevent="confirm('{{ __('requests.confirm_reject') }}') && $el.submit()"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full border border-gray-300 text-red-600 rounded-lg py-2.5 font-medium hover:bg-red-50">
                            {{ __('requests.reject') }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">{{ __('requests.no_requests') }}</p>
        @endforelse
    </div>
@endsection
