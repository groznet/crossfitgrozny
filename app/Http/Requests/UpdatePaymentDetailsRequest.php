<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_phone' => ['nullable', 'string', 'max:50'],
            'payment_bank' => ['nullable', 'string', 'max:100'],
            'payment_recipient' => ['nullable', 'string', 'max:100'],
        ];
    }
}
