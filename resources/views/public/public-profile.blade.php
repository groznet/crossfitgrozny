@extends('layouts.guest')

@section('title', $member->full_name)

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
        @if ($member->photo_url)
            <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-24 h-24 rounded-full object-cover mx-auto mb-3">
        @else
            <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium text-3xl mx-auto mb-3">
                {{ mb_substr($member->full_name, 0, 1) }}
            </div>
        @endif

        <h1 class="text-lg font-semibold mb-1">{{ $member->full_name }}</h1>
        <p class="text-sm text-gray-500">
            {{ __('public.member_since', ['date' => $member->joined_at->translatedFormat('F Y')]) }}
        </p>

        <a href="{{ route('public.community.index') }}" class="block w-full border border-gray-300 rounded-lg py-3 text-center text-sm font-medium hover:bg-gray-50 mt-6">
            {{ __('public.community_title') }}
        </a>
    </div>
@endsection
