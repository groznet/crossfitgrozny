<?php

namespace App\Http\Requests;

use App\Enums\MemberStatus;
use App\Enums\ReceiptStatus;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentReceiptRequest extends FormRequest
{
    /**
     * Only active members may send a receipt.
     */
    public function authorize(): bool
    {
        return $this->route('member')->status === MemberStatus::Active;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan' => ['required', 'in:visit,month_day,month_evening,year', function (string $attribute, mixed $value, Closure $fail) {
                if ($this->route('member')->paymentReceipts()->where('status', ReceiptStatus::Pending)->exists()) {
                    $fail(__('receipts.already_pending'));
                }
            }],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ];
    }
}
