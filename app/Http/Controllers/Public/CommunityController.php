<?php

namespace App\Http\Controllers\Public;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * A public, name-and-photo-only roster of current members. Deliberately
     * shows nothing about payment/subscription status — that's private, and
     * this page is a community showcase, not a management tool.
     */
    public function index(): View
    {
        $members = Member::where('status', MemberStatus::Active)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'photo_url', 'username']);

        return view('public.community', compact('members'));
    }
}
