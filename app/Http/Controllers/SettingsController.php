<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlanPricesRequest;
use App\Models\PlanPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $prices = PlanPrice::pluck('price', 'plan');

        return view('settings.prices', compact('prices'));
    }

    public function update(UpdatePlanPricesRequest $request): RedirectResponse
    {
        foreach ($request->validated('prices') as $plan => $price) {
            PlanPrice::where('plan', $plan)->update(['price' => $price]);
        }

        return redirect()->route('settings.prices.edit')->with('status', __('settings.saved'));
    }
}
