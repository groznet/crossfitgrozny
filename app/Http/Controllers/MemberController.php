<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Services\PhotoUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');
        $search = $request->string('search')->trim()->toString();
        $filter = $request->string('filter')->toString();

        $query = Member::query()
            ->where('status', $showArchived ? MemberStatus::Archived : MemberStatus::Active)
            ->with('latestPayment');

        if ($search !== '') {
            $query->where('full_name', 'like', '%'.$search.'%');
        }

        $members = $query->get();

        $counts = $members->countBy(fn (Member $member) => $member->subscription_status->value);

        if ($filter !== '') {
            $members = $members->filter(fn (Member $member) => $member->subscription_status->value === $filter)->values();
        }

        $members = $members
            ->sortBy(fn (Member $member) => [$member->subscription_status->sortPriority(), $member->full_name])
            ->values();

        return view('members.index', compact('members', 'counts', 'showArchived', 'search', 'filter'));
    }

    public function show(Member $member): View
    {
        $member->load(['latestPayment', 'payments' => fn ($query) => $query->orderByDesc('paid_at')]);

        return view('members.show', compact('member'));
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function update(UpdateMemberRequest $request, Member $member, PhotoUploadService $photos): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $photos->delete($member->photo_url);
            $data['photo_url'] = $photos->store($request->file('photo'));
        }

        $member->update($data);

        return redirect()->route('members.show', $member)->with('status', __('members.updated'));
    }

    public function archive(Member $member): RedirectResponse
    {
        $member->update(['status' => MemberStatus::Archived]);

        return redirect()->route('members.index')->with('status', __('members.archived_status'));
    }

    public function restore(Member $member): RedirectResponse
    {
        $member->update(['status' => MemberStatus::Active]);

        return redirect()->route('members.show', $member)->with('status', __('members.restored_status'));
    }
}
