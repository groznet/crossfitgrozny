<?php

namespace App\Http\Controllers;

use App\Enums\PlanType;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Member;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Member $member): View
    {
        $prices = PlanPrice::pluck('price', 'plan');

        return view('payments.create', compact('member', 'prices'));
    }

    public function store(StorePaymentRequest $request, Member $member, PaymentService $service): RedirectResponse
    {
        $service->recordPayment(
            $member,
            PlanType::from($request->string('plan')->toString()),
            $request->integer('amount'),
            $request->date('paid_at'),
        );

        return redirect()->route('members.show', $member)->with('status', __('payments.recorded'));
    }

    public function edit(Payment $payment): View
    {
        $prices = PlanPrice::pluck('price', 'plan');

        return view('payments.edit', ['payment' => $payment, 'member' => $payment->member, 'prices' => $prices]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment, PaymentService $service): RedirectResponse
    {
        $service->recalculate(
            $payment,
            PlanType::from($request->string('plan')->toString()),
            $request->integer('amount'),
            $request->date('paid_at'),
        );

        return redirect()->route('members.show', $payment->member)->with('status', __('payments.updated'));
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $member = $payment->member;
        $payment->delete();

        return redirect()->route('members.show', $member)->with('status', __('payments.deleted'));
    }
}
