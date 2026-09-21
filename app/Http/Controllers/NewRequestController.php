<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Services\PhotoUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewRequestController extends Controller
{
    public function index(): View
    {
        $pending = Member::where('status', MemberStatus::Pending)
            ->latest()
            ->get()
            ->map(function (Member $member) {
                $member->duplicateOf = Member::where('phone', $member->phone)
                    ->where('id', '!=', $member->id)
                    ->where('status', '!=', MemberStatus::Pending)
                    ->first();

                return $member;
            });

        return view('requests.index', compact('pending'));
    }

    public function edit(Member $member): View
    {
        return view('requests.edit', compact('member'));
    }

    public function approve(UpdateMemberRequest $request, Member $member, PhotoUploadService $photos): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $photos->delete($member->photo_url);
            $data['photo_url'] = $photos->store($request->file('photo'));
        }

        $data['status'] = MemberStatus::Active;
        $member->update($data);

        return redirect()->route('members.show', $member)->with('status', __('requests.approved'));
    }

    public function reject(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('requests.index')->with('status', __('requests.rejected'));
    }

    public function confirmMerge(Member $member, Member $target): View
    {
        return view('requests.merge', compact('member', 'target'));
    }

    public function merge(Member $member, Member $target): RedirectResponse
    {
        foreach (['photo_url', 'birth_date', 'preferred_time', 'note'] as $field) {
            if (blank($target->{$field}) && filled($member->{$field})) {
                $target->{$field} = $member->{$field};
            }
        }
        $target->save();
        $member->delete();

        return redirect()->route('members.show', $target)->with('status', __('requests.merged'));
    }
}
