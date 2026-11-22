@extends('layouts.app')

@section('title', __('receipts.title'))

@section('content')
    <h1 class="text-xl font-semibold mb-4">{{ __('receipts.title') }}</h1>

    <div class="space-y-3">
        @forelse ($receipts as $receipt)
            <div class="bg-white rounded-xl shadow-sm p-4">
                <a href="{{ route('members.show', $receipt->member) }}" class="font-medium hover:underline">{{ $receipt->member->full_name }}</a>
                <div class="text-sm text-gray-500">{{ $receipt->member->phone }}</div>
                <div class="text-sm mt-2">
                    {{ $receipt->plan->label() }} · <span class="font-medium">{{ \App\Support\Money::format($receipt->amount) }}</span>
                </div>
                <div class="text-xs text-gray-500 mb-3">{{ format_date($receipt->created_at) }}</div>

                @unless ($receipt->isPdf())
                    <a href="{{ route('receipts.file', $receipt) }}" target="_blank">
                        <img src="{{ route('receipts.file', $receipt) }}" alt="" class="w-full max-h-72 object-contain rounded-lg border border-gray-200 bg-gray-50 mb-3">
                    </a>
                @endunless

                <div class="grid grid-cols-2 gap-2 text-sm">
                    <a href="{{ route('receipts.file', $receipt) }}" target="_blank" class="col-span-2 border border-gray-300 rounded-lg py-2.5 text-center font-medium hover:bg-gray-50">
                        {{ __('receipts.open_file') }}
                    </a>
                    <form method="POST" action="{{ route('receipts.confirm', $receipt) }}" class="col-span-2">
                        @csrf
                        <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-2.5 font-medium hover:bg-gray-800">
                            {{ __('receipts.confirm') }}
                        </button>
                    </form>
                    <form
                        method="POST"
                        action="{{ route('receipts.reject', $receipt) }}"
                        class="col-span-2"
                        x-data
                        @submit.prevent="confirm('{{ __('receipts.confirm_reject') }}') && $el.submit()"
                    >
                        @csrf
                        <button type="submit" class="w-full border border-gray-300 text-red-600 rounded-lg py-2.5 font-medium hover:bg-red-50">
                            {{ __('receipts.reject') }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">{{ __('receipts.no_receipts') }}</p>
        @endforelse
    </div>
@endsection
