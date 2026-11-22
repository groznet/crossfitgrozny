@extends('layouts.app')

@section('title', __('settings.title'))

@section('content')
    <h1 class="text-xl font-semibold mb-2">{{ __('settings.title') }}</h1>
    <p class="text-sm text-gray-500 mb-4">{{ __('settings.intro') }}</p>

    <form method="POST" action="{{ route('settings.prices.update') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @method('PUT')
        @csrf

        @foreach (\App\Enums\PlanType::cases() as $type)
            <div>
                <label for="price_{{ $type->value }}" class="block text-sm font-medium mb-1">{{ $type->label() }}</label>
                <div class="relative">
                    <input
                        type="number"
                        name="prices[{{ $type->value }}]"
                        id="price_{{ $type->value }}"
                        value="{{ old('prices.'.$type->value, $prices->get($type->value)) }}"
                        min="0"
                        class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
                    >
                </div>
                @error('prices.'.$type->value)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
            {{ __('app.save') }}
        </button>
    </form>

    <h2 class="text-xl font-semibold mt-8 mb-2">{{ __('settings.details_title') }}</h2>
    <p class="text-sm text-gray-500 mb-4">{{ __('settings.details_intro') }}</p>

    <form method="POST" action="{{ route('settings.payment-details.update') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @method('PUT')
        @csrf

        @foreach (['payment_phone' => 'details_phone', 'payment_bank' => 'details_bank', 'payment_recipient' => 'details_recipient'] as $key => $label)
            <div>
                <label for="{{ $key }}" class="block text-sm font-medium mb-1">{{ __('settings.'.$label) }}</label>
                <input
                    type="text"
                    name="{{ $key }}"
                    id="{{ $key }}"
                    value="{{ old($key, $details->get($key)) }}"
                    class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
                >
                @error($key)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <button type="submit" class="w-full bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
            {{ __('app.save') }}
        </button>
    </form>
@endsection
