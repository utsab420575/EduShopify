@extends('backend.layouts.admin')

@section('title', 'Activity')
@section('breadcrumb', 'System & Settings / Audit Log / Detail')

@section('body')

    <x-backend.page-header :title="$activity->description" :subtitle="$activity->log_name" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            @if(!empty($activity->properties) && $activity->properties->isNotEmpty())
                <x-backend.form-card title="Properties">
                    <pre class="text-xs bg-gray-50 rounded-lg p-4 overflow-x-auto">{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                </x-backend.form-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-backend.form-card title="Details">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Causer</dt><dd class="font-medium text-gray-900">{{ $activity->causer?->name ?? 'System' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Subject</dt><dd class="font-medium text-gray-900">{{ class_basename($activity->subject_type ?? '') }} #{{ $activity->subject_id }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd class="font-medium text-gray-900">{{ $activity->created_at->format('d M Y H:i') }}</dd></div>
                </dl>
            </x-backend.form-card>
        </div>
    </div>

@endsection
