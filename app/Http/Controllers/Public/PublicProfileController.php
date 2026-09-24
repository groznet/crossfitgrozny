<?php

namespace App\Http\Controllers\Public;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMemberUsernameRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    /**
     * A member's public page at /u/{username} or /u/{id}. Shows only name,
     * photo and join date — never payment status, which stays on the
     * private /m/{token} page.
     */
    public function show(string $handle): View|RedirectResponse
    {
        $member = Member::with('firstPayment')
            ->where('status', MemberStatus::Active)
            ->where(ctype_digit($handle) ? 'id' : 'username', mb_strtolower($handle))
            ->firstOrFail();

        if ($member->username && $handle !== $member->username) {
            return redirect($member->profileUrl(), 301);
        }

        return view('public.public-profile', compact('member'));
    }

    /**
     * Set or change the username, from the member's own private page.
     */
    public function updateUsername(UpdateMemberUsernameRequest $request, Member $member): RedirectResponse
    {
        $member->username = $request->validated('username');
        $member->save();

        return redirect($member->publicUrl())->with('status', __('public.username_saved'));
    }
}
