@extends('layouts.app')

@section('title', __('payments.record_title'))

@section('content')
    <h1 class="text-xl font-semibold mb-1">{{ __('payments.record_title') }}</h1>
    <p class="text-sm text-gray-500 mb-4">{{ $member->full_name }}</p>

    <form method="POST" action="{{ route('payments.store', $member) }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @php($payment = null)
        @include('payments._form')

        <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
                {{ __('payments.submit') }}
            </button>
            <a href="{{ route('members.show', $member) }}" class="flex-1 text-center border border-gray-300 rounded-lg py-3 text-base font-medium hover:bg-gray-50">
                {{ __('app.cancel') }}
            </a>
        </div>
    </form>
@endsection
