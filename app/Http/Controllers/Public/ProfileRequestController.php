<?php

namespace App\Http\Controllers\Public;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberProfileRequest;
use App\Models\Member;
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
     * A filled honeypot field always looks like success and saves nothing,
     * so a bot never learns which field gave it away.
     */
    public function store(Request $request, PhotoUploadService $photos): RedirectResponse
    {
        if ($request->filled('company')) {
            return redirect()->route('public.profile.thanks');
        }

        if ($request->filled('phone')) {
            $request->merge(['phone' => PhoneNumber::normalize($request->string('phone')->toString())]);
        }

        $data = $request->validate((new StoreMemberProfileRequest)->rules());

        if ($request->hasFile('photo')) {
            $data['photo_url'] = $photos->store($request->file('photo'));
        }
        unset($data['photo']);

        Member::create([...$data, 'status' => MemberStatus::Pending]);

        return redirect()->route('public.profile.thanks');
    }

    public function thanks(): View
    {
        return view('public.thanks');
    }
}
