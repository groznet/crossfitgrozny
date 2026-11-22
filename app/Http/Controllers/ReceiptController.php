<?php

namespace App\Http\Controllers;

use App\Enums\ReceiptStatus;
use App\Models\PaymentReceipt;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function index(): View
    {
        $receipts = PaymentReceipt::with('member')
            ->where('status', ReceiptStatus::Pending)
            ->oldest()
            ->get();

        return view('receipts.index', compact('receipts'));
    }

    /**
     * Receipts are bank screenshots, so they live on the private disk and
     * are only served to the logged-in admin.
     */
    public function file(PaymentReceipt $receipt): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($receipt->file_path), 404);

        return Storage::disk('local')->response($receipt->file_path);
    }

    /**
     * Confirming records a real payment (dated today) through the same
     * service as manual entry, so expiry rules stay identical. Magomed can
     * still edit the payment afterwards.
     */
    public function confirm(PaymentReceipt $receipt, PaymentService $service): RedirectResponse
    {
        if ($receipt->status === ReceiptStatus::Pending) {
            DB::transaction(function () use ($receipt, $service) {
                $payment = $service->recordPayment(
                    $receipt->member,
                    $receipt->plan,
                    $receipt->amount,
                    today(config('app.timezone')),
                );

                $receipt->update([
                    'status' => ReceiptStatus::Confirmed,
                    'payment_id' => $payment->id,
                    'reviewed_at' => now(),
                ]);
            });
        }

        return redirect()->route('receipts.index')->with('status', __('receipts.confirmed'));
    }

    public function reject(PaymentReceipt $receipt): RedirectResponse
    {
        if ($receipt->status === ReceiptStatus::Pending) {
            $receipt->update(['status' => ReceiptStatus::Rejected, 'reviewed_at' => now()]);
        }

        return redirect()->route('receipts.index')->with('status', __('receipts.rejected'));
    }
}
