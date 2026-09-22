<?php

namespace App\Http\Requests;

use App\Enums\MemberStatus;
use App\Models\Member;
use Closure;

class StoreMemberProfileRequest extends UpdateMemberRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // A phone that already has an unreviewed submission can't submit
        // another one — Adam would just see the same person twice.
        $rules['phone'][] = function (string $attribute, mixed $value, Closure $fail) {
            if (Member::where('phone', $value)->where('status', MemberStatus::Pending)->exists()) {
                $fail(__('public.phone_already_pending'));
            }
        };

        $rules['captcha_answer'] = ['required', function (string $attribute, mixed $value, Closure $fail) {
            if ((int) $value !== (int) session('profile_captcha_answer')) {
                $fail(__('public.captcha_wrong'));
            }
        }];

        return $rules;
    }
}
