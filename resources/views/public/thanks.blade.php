@extends('layouts.guest')

@section('title', __('public.thanks_title'))

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6 text-center">
        <h1 class="text-xl font-semibold mb-2">{{ __('public.thanks_title') }}</h1>
        <p class="text-gray-600">{{ __('public.thanks_message') }}</p>
    </div>
@endsection
