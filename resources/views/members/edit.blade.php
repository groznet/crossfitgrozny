@extends('layouts.app')

@section('title', __('app.edit').' — '.$member->full_name)

@section('content')
    <h1 class="text-xl font-semibold mb-4">{{ __('app.edit') }}</h1>

    <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @method('PUT')
        @include('members._form')

        <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 bg-gray-900 text-white rounded-lg py-3 text-base font-medium hover:bg-gray-800">
                {{ __('app.save') }}
            </button>
            <a href="{{ route('members.show', $member) }}" class="flex-1 text-center border border-gray-300 rounded-lg py-3 text-base font-medium hover:bg-gray-50">
                {{ __('app.cancel') }}
            </a>
        </div>
    </form>
@endsection
