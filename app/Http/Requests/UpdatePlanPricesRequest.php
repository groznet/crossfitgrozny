<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanPricesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prices.visit' => ['required', 'integer', 'min:0'],
            'prices.month_day' => ['required', 'integer', 'min:0'],
            'prices.month_evening' => ['required', 'integer', 'min:0'],
            'prices.year' => ['required', 'integer', 'min:0'],
        ];
    }
}
