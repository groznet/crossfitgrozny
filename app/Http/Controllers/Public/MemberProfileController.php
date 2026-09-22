<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\View\View;

class MemberProfileController extends Controller
{
    /**
     * A read-only view of a member's own status, reachable only by knowing
     * their unguessable public_token link (no login, no directory lookup).
     */
    public function show(Member $member): View
    {
        $adminWhatsappUrl = PhoneNumber::toWhatsAppUrl(User::first()->phone);

        return view('public.member-profile', compact('member', 'adminWhatsappUrl'));
    }
}
