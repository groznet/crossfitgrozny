<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePaymentDetailsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;

class PaymentDetailsController extends Controller
{
    public function update(UpdatePaymentDetailsRequest $request): RedirectResponse
    {
        foreach (['payment_phone', 'payment_bank', 'payment_recipient'] as $key) {
            Setting::setValue($key, $request->validated($key));
        }

        return redirect()->route('settings.prices.edit')->with('status', __('settings.details_saved'));
    }
}
