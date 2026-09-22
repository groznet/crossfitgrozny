<?php

namespace App\Http\Controllers\Public;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberProfileRequest;
use App\Models\Member;
use App\Services\OtpService;
use App\Services\PhotoUploadService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileRequestController extends Controller
{
    public function create(): View
    {
        return view('public.profile', ['member' => new Member]);
    }

    /**
     * A filled honeypot field always looks like a normal success response
     * and sends nothing, so a bot never learns which field gave it away —
     * and we never waste an SMS on it either.
     *
     * SMS verification is currently switched off via
     * `SMS_VERIFICATION_ENABLED` (see .env / README) — the submission is
     * saved directly instead of going through the code step. The OtpService/
     * SmsRuGateway code is left in place so it's a one-line flip to turn
     * back on.
     */
    public function sendCode(Request $request, PhotoUploadService $photos, OtpService $otp): View|RedirectResponse
    {
        $verificationEnabled = config('services.smsru.verification_enabled');

        if ($request->filled('company')) {
            return $verificationEnabled
                ? view('public.verify', ['phone' => $request->string('phone')->toString() ?: '—'])
                : redirect()->route('public.profile.thanks');
        }

        if ($request->filled('phone')) {
            $request->merge(['phone' => PhoneNumber::normalize($request->string('phone')->toString())]);
        }

        $data = $request->validate((new StoreMemberProfileRequest)->rules());

        if ($request->hasFile('photo')) {
            $data['photo_url'] = $photos->store($request->file('photo'));
        }
        unset($data['photo']);

        if (! $verificationEnabled) {
            Member::create([...$data, 'status' => MemberStatus::Pending]);

            return redirect()->route('public.profile.thanks');
        }

        $otp->send($data['phone'], $data, $request->ip());

        return view('public.verify', ['phone' => $data['phone']]);
    }

    public function resendCode(Request $request, OtpService $otp): View
    {
        $request->validate(['phone' => ['required', 'string']]);
        $phone = PhoneNumber::normalize($request->string('phone')->toString());

        if (! $otp->resend($phone, $request->ip())) {
            return view('public.verify', [
                'phone' => $phone,
                'error' => __('public.resend_too_soon'),
            ]);
        }

        return view('public.verify', [
            'phone' => $phone,
            'status' => __('public.code_resent'),
        ]);
    }

    public function verifyCode(Request $request, OtpService $otp): View|RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $phone = PhoneNumber::normalize($request->string('phone')->toString());
        $result = $otp->verify($phone, $request->string('code')->toString());

        if (! $result['ok']) {
            $expired = in_array($result['reason'], ['expired', 'too_many_attempts'], true);

            return view('public.verify', [
                'phone' => $phone,
                'expired' => $expired,
                'error' => match ($result['reason']) {
                    'expired' => __('public.code_expired'),
                    'too_many_attempts' => __('public.too_many_attempts'),
                    default => __('public.invalid_code'),
                },
            ]);
        }

        Member::create([...$result['data'], 'status' => MemberStatus::Pending]);

        return redirect()->route('public.profile.thanks');
    }

    public function thanks(): View
    {
        return view('public.thanks');
    }
}
