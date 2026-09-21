@csrf

<div>
    <label for="full_name" class="block text-sm font-medium mb-1">{{ __('members.full_name') }}</label>
    <input
        type="text"
        name="full_name"
        id="full_name"
        value="{{ old('full_name', $member->full_name) }}"
        class="w-full rounded-lg border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
    >
    @error('full_name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="phone" class="block text-sm font-medium mb-1">{{ __('members.phone') }}</label>
    <input
        type="tel"
        name="phone"
        id="phone"
        value="{{ old('phone', $member->phone) }}"
        class="w-full rounded-lg border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
    >
    @error('phone')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="photo" class="block text-sm font-medium mb-1">{{ __('members.photo') }}</label>
    @if ($member->photo_url)
        <img src="{{ Storage::url($member->photo_url) }}" alt="" class="w-16 h-16 rounded-full object-cover mb-2">
    @endif
    <input
        type="file"
        name="photo"
        id="photo"
        accept="image/*"
        class="w-full text-sm"
    >
    @error('photo')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="birth_date" class="block text-sm font-medium mb-1">{{ __('members.birth_date') }}</label>
    <input
        type="date"
        name="birth_date"
        id="birth_date"
        value="{{ old('birth_date', $member->birth_date?->format('Y-m-d')) }}"
        class="w-full rounded-lg border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
    >
    @error('birth_date')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <span class="block text-sm font-medium mb-1">{{ __('members.preferred_time') }}</span>
    <div class="flex gap-4">
        @foreach (\App\Enums\PreferredTime::cases() as $time)
            <label class="flex items-center gap-2 text-sm">
                <input
                    type="radio"
                    name="preferred_time"
                    value="{{ $time->value }}"
                    @checked(old('preferred_time', $member->preferred_time?->value) === $time->value)
                >
                {{ $time->label() }}
            </label>
        @endforeach
    </div>
    @error('preferred_time')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="note" class="block text-sm font-medium mb-1">{{ __('members.note') }}</label>
    <textarea
        name="note"
        id="note"
        rows="3"
        class="w-full rounded-lg border-gray-300 py-3 px-4 text-base focus:border-gray-500 focus:ring-gray-500"
    >{{ old('note', $member->note) }}</textarea>
    @error('note')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
