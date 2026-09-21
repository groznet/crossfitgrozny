<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="font-medium mb-3">{{ __('members.payment_history') }}</h2>
    @forelse ($payments as $payment)
        <div class="flex justify-between text-sm py-2 border-b border-gray-100 last:border-0">
            <span>{{ $payment->plan->label() }} · {{ format_date($payment->paid_at) }}</span>
            <span class="text-gray-500">до {{ format_date($payment->valid_until) }}</span>
        </div>
    @empty
        <p class="text-sm text-gray-500">{{ __('members.no_payments') }}</p>
    @endforelse
</div>
