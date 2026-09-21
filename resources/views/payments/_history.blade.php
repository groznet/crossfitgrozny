<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="font-medium mb-3">{{ __('members.payment_history') }}</h2>
    @forelse ($payments as $payment)
        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
            <div>
                <div>{{ $payment->plan->label() }} · {{ format_date($payment->paid_at) }}</div>
                <div class="text-gray-500">до {{ format_date($payment->valid_until) }}</div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('payments.edit', $payment) }}" class="text-gray-500 hover:text-gray-900">{{ __('app.edit') }}</a>
                <form
                    method="POST"
                    action="{{ route('payments.destroy', $payment) }}"
                    x-data
                    @submit.prevent="confirm('{{ __('app.confirm_delete') }}') && $el.submit()"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800">{{ __('app.delete') }}</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">{{ __('members.no_payments') }}</p>
    @endforelse
</div>
