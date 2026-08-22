@extends('backend.layouts.supplier')

@section('title', 'Business Hours')
@section('breadcrumb', 'Business Profile / Business Hours')

@section('body')

    <x-backend.page-header title="Business Hours" subtitle="Set your operational working hours to help buyers know when your team is available." />

    <form method="POST" action="{{ route('supplier.company.business-hours.update') }}">
        @csrf @method('PUT')

        <div class="max-w-3xl">
            <x-backend.form-card title="Weekly Operating Schedule">
                <div class="divide-y divide-gray-100 -mx-5 -mb-5">
                    @foreach($dayNames as $dayIndex => $dayName)
                        @php
                            $hour = $businessHours->get($dayIndex);
                            $isOpen = $hour ? $hour->is_open : ($dayIndex >= 1 && $dayIndex <= 5);
                            $openTime = $hour?->open_time ? substr($hour->open_time, 0, 5) : '09:00';
                            $closeTime = $hour?->close_time ? substr($hour->close_time, 0, 5) : '17:00';
                        @endphp
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50"
                             x-data="{ isOpen: {{ $isOpen ? 'true' : 'false' }} }">
                            <div class="w-32 flex items-center gap-2.5">
                                <input type="checkbox" name="days[{{ $dayIndex }}][is_open]" value="1"
                                       x-model="isOpen" id="day_{{ $dayIndex }}" style="accent-color:var(--theme-primary)">
                                <label for="day_{{ $dayIndex }}" class="text-sm font-semibold text-gray-800 cursor-pointer">{{ $dayName }}</label>
                            </div>

                            <div class="flex items-center gap-3" x-show="isOpen">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-400">Open:</span>
                                    <input type="time" name="days[{{ $dayIndex }}][open_time]" value="{{ $openTime }}" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white">
                                </div>
                                <span class="text-gray-300">&ndash;</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-400">Close:</span>
                                    <input type="time" name="days[{{ $dayIndex }}][close_time]" value="{{ $closeTime }}" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white">
                                </div>
                            </div>

                            <div x-show="!isOpen" class="text-xs text-gray-400 italic">
                                Closed
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-backend.form-card>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary text-sm font-medium px-6 py-2.5 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Business Hours
                </button>
            </div>
        </div>
    </form>

@endsection
