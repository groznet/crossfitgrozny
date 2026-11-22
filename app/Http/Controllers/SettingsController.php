<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlanPricesRequest;
use App\Models\PlanPrice;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $prices = PlanPrice::pluck('price', 'plan');

        $details = Setting::whereIn('key', ['payment_phone', 'payment_bank', 'payment_recipient'])->pluck('value', 'key');

        return view('settings.prices', compact('prices', 'details'));
    }

    public function update(UpdatePlanPricesRequest $request): RedirectResponse
    {
        foreach ($request->validated('prices') as $plan => $price) {
            PlanPrice::where('plan', $plan)->update(['price' => $price]);
        }

        return redirect()->route('settings.prices.edit')->with('status', __('settings.saved'));
    }
}
