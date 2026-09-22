@csrf

<div
    x-data="{
        prices: @js($prices),
        plan: '{{ old('plan', $payment?->plan->value ?? '') }}',
        amount: '{{ old('amount', $payment?->amount ?? '') }}',
    }"
    x-init="$watch('plan', value => { amount = prices[value] ?? amount })"
    class="space-y-4"
>
    <div>
        <label for="plan" class="block text-sm font-medium mb-1">{{ __('payments.plan_field') }}</label>
        <select
            name="plan"
            id="plan"
            x-model="plan"
            class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
        >
            <option value="">—</option>
            @foreach (\App\Enums\PlanType::cases() as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('plan')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="amount" class="block text-sm font-medium mb-1">{{ __('payments.amount_field') }}</label>
        <input
            type="number"
            name="amount"
            id="amount"
            x-model="amount"
            min="0"
            class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
        >
        @error('amount')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label for="paid_at" class="block text-sm font-medium mb-1">{{ __('payments.paid_at_field') }}</label>
    <input
        type="date"
        name="paid_at"
        id="paid_at"
        value="{{ old('paid_at', ($payment->paid_at ?? now())->format('Y-m-d')) }}"
        max="{{ now()->format('Y-m-d') }}"
        class="w-full rounded-lg border border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
    >
    @error('paid_at')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
