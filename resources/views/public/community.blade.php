@extends('layouts.guest')

@section('title', __('public.community_title'))

@section('container-class', 'max-w-3xl')

@php
    $avatarColors = [
        'bg-red-100 text-red-700',
        'bg-orange-100 text-orange-700',
        'bg-yellow-100 text-yellow-700',
        'bg-green-100 text-green-700',
        'bg-teal-100 text-teal-700',
        'bg-blue-100 text-blue-700',
        'bg-indigo-100 text-indigo-700',
        'bg-purple-100 text-purple-700',
        'bg-pink-100 text-pink-700',
    ];
@endphp

@section('content')
    <div class="text-center mb-8">
        <h1 class="text-2xl font-semibold mb-1">{{ __('public.community_title') }}</h1>
        <p class="text-gray-500">{{ __('public.community_intro') }}</p>
    </div>

    @if ($members->isEmpty())
        <p class="text-center text-gray-500">{{ __('public.community_empty') }}</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-10">
            @foreach ($members as $member)
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    @if ($member->photo_url)
                        <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-16 h-16 rounded-full object-cover mx-auto mb-2">
                    @else
                        <div class="w-16 h-16 rounded-full flex items-center justify-center font-semibold text-xl mx-auto mb-2 {{ $avatarColors[crc32($member->full_name) % count($avatarColors)] }}">
                            {{ mb_substr($member->full_name, 0, 1) }}
                        </div>
                    @endif
                    <div class="text-sm font-medium truncate">{{ $member->full_name }}</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="text-center bg-white rounded-xl shadow-sm p-6">
        <p class="font-medium mb-3">{{ __('public.community_join_cta') }}</p>
        <a href="{{ route('public.profile.create') }}" class="inline-block bg-gray-900 text-white rounded-lg py-3 px-6 text-sm font-medium hover:bg-gray-800">
            {{ __('public.community_join_button') }}
        </a>
    </div>
@endsection
