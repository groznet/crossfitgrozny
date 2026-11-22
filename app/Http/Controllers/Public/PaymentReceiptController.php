<?php

namespace App\Http\Controllers\Public;

use App\Enums\PlanType;
use App\Enums\ReceiptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentReceiptRequest;
use App\Models\Member;
use App\Models\PlanPrice;
use Illuminate\Http\RedirectResponse;

class PaymentReceiptController extends Controller
{
    /**
     * A member submits a bank-transfer receipt for Magomed to review.
     * The amount is fixed server-side from the current plan price; nothing
     * is recorded as a payment until Magomed confirms it.
     */
    public function store(StorePaymentReceiptRequest $request, Member $member): RedirectResponse
    {
        $plan = PlanType::from($request->string('plan')->toString());

        $member->paymentReceipts()->create([
            'plan' => $plan,
            'amount' => (int) PlanPrice::where('plan', $plan)->value('price'),
            'file_path' => $request->file('receipt')->store('receipts', 'local'),
            'status' => ReceiptStatus::Pending,
        ]);

        return redirect()->route('public.member.show', $member->public_token)->with('status', __('receipts.sent'));
    }
}
