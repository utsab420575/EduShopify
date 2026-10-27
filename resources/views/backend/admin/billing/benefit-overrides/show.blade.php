@extends('backend.layouts.admin')

@php
    $supplierName = $account->supplierProfile?->display_name ?: $account->display_name;
@endphp

@section('title', 'Benefits — '.$supplierName)
@section('breadcrumb', 'Subscription & Billing / Benefit Overrides / '.$supplierName)

@section('body')

    @php
        $sourceTones = [
            'override' => 'bg-violet-50 text-violet-700 border-violet-200',
            'snapshot' => 'bg-sky-50 text-sky-700 border-sky-200',
            'plan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'default' => 'bg-gray-100 text-gray-600 border-gray-200',
            'ineligible' => 'bg-red-50 text-red-700 border-red-200',
        ];
        $statusMap = ['scheduled' => 'info', 'expired' => 'neutral', 'inactive' => 'neutral'];
    @endphp

    <x-backend.page-header :title="$supplierName" :subtitle="$account->account_number.' · Account ID '.$account->id">
        <x-slot:actions>
            <a href="{{ route('admin.billing.benefit-overrides.index') }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"><i class="fa-solid fa-arrow-left mr-1.5"></i>All Suppliers</a>
            <a href="{{ route('admin.suppliers.show', $account) }}" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Supplier Details</a>
            @if($benefits->isNotEmpty())
                <button type="button" @click="$dispatch('prefill-override', { id: '' }); $dispatch('open-modal-override-create')"
                        class="btn-primary text-sm font-medium px-4 py-2 rounded-lg"><i class="fa-solid fa-plus mr-1.5"></i>Add Override</button>
            @endif
        </x-slot:actions>
    </x-backend.page-header>

    @unless($eligible)
        <div class="flex items-start gap-3 p-4 mb-6 rounded-xl border border-red-200 bg-red-50 text-sm text-red-800">
            <i class="fa-solid fa-ban mt-0.5"></i>
            <p><span class="font-semibold">Not eligible for benefits.</span> The account must be active with an active supplier capability.
               Until then every benefit — overrides included — resolves to its default value.</p>
        </div>
    @endunless

    {{-- Supplier + subscription summary --}}
    @php
        $photo = $account->supplierProfile?->profile_photo;
        $hasSnapshot = is_array($subscription?->features_snapshot['benefits'] ?? null);
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-5">
            <div class="flex items-center gap-4 min-w-0 lg:w-80 shrink-0">
                <div class="w-14 h-14 rounded-full border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 text-lg font-bold text-gray-500">
                    @if($photo)
                        <img src="{{ asset('storage/'.$photo) }}" alt="" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($supplierName ?? 'S', 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-base font-semibold text-gray-900 truncate">{{ $supplierName }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <x-backend.status-badge :status="$account->status" />
                        @if($eligible)
                            <span class="text-[11px] font-semibold text-emerald-700"><i class="fa-solid fa-circle-check"></i> Eligible</span>
                        @else
                            <span class="text-[11px] font-semibold text-red-600"><i class="fa-solid fa-ban"></i> Not eligible</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 flex-1 lg:border-l lg:border-gray-100 lg:pl-5">
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Plan</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1 truncate">{{ $subscription?->plan?->name ?? 'No subscription' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Subscription</p>
                    <p class="mt-1">
                        @if($subscription)
                            <x-backend.status-badge :status="$subscription->isActive() ? 'active' : $subscription->status" />
                        @else
                            <span class="text-sm text-gray-400">—</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Period Ends</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $subscription?->current_period_end?->format('d M Y') ?? '—' }}</p>
                </div>
                <div title="{{ $hasSnapshot ? 'Benefit values captured at purchase — kept until renewal or plan change' : 'No captured values — the live plan values apply' }}">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Snapshot</p>
                    <p class="text-sm font-semibold mt-1 {{ $hasSnapshot ? 'text-sky-700' : 'text-gray-500' }}">{{ $hasSnapshot ? 'Captured' : 'Live plan' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Overrides</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        <span class="text-violet-700">{{ $activeCount }} active</span>
                        @if($scheduledCount > 0)
                            <span class="text-gray-400 font-normal">· {{ $scheduledCount }} scheduled</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Effective benefits --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Effective Benefits</h3>
            <p class="text-xs text-gray-500 mt-0.5">Priority: active override → subscription snapshot → plan → default.</p>
        </div>
        @if($resolved->isEmpty())
            <x-backend.empty-state icon="fa-gift" title="No active benefits defined" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Benefit</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Snapshot</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Override</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Effective</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($resolved as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->benefit->name }}</p>
                                    <p class="text-xs font-mono text-gray-400">{{ $item->benefit->code }}</p>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600">{{ $item->hasPlanValue ? $item->benefit->formatValue($item->planValue) : '—' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-600">{{ $item->hasSnapshotValue ? $item->benefit->formatValue($item->snapshotValue) : '—' }}</td>
                                <td class="px-5 py-3 text-sm">
                                    @if($item->override)
                                        <span class="font-medium text-violet-700">{{ $item->benefit->formatValue($item->benefit->normalize($item->override->value)) }}</span>
                                        <p class="text-[11px] text-gray-400">{{ $item->override->expires_at ? 'until '.$item->override->expires_at->format('d M Y H:i') : 'no expiry' }}</p>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->formatted() }}</p>
                                    <span class="inline-block mt-0.5 text-[10px] font-semibold px-1.5 py-0.5 rounded border {{ $sourceTones[$item->source] ?? $sourceTones['default'] }}">{{ $item->sourceLabel() }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if($item->override)
                                        <button type="button" @click="$dispatch('open-modal-override-edit-{{ $item->override->id }}')"
                                                class="text-xs font-medium text-indigo-600 hover:underline">Edit override</button>
                                    @else
                                        <button type="button" @click="$dispatch('prefill-override', { id: {{ $item->benefit->id }} }); $dispatch('open-modal-override-create')"
                                                class="text-xs font-medium text-indigo-600 hover:underline">Override</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- History --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Override History</h3>
            <p class="text-xs text-gray-500 mt-0.5">Every override ever set for this supplier. Expired and inactive ones no longer apply.</p>
        </div>
        @if($history->isEmpty())
            <x-backend.empty-state icon="fa-clock-rotate-left" title="No overrides yet" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Benefit</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason / By</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($history as $override)
                            @php
                                $status = $override->status();
                            @endphp
                            <tr class="{{ in_array($status, ['expired', 'inactive'], true) ? 'text-gray-400' : '' }}">
                                <td class="px-5 py-3">
                                    <p class="text-sm font-medium {{ in_array($status, ['expired', 'inactive'], true) ? 'text-gray-500' : 'text-gray-900' }}">{{ $override->benefit?->name ?? '—' }}</p>
                                    <p class="text-[11px] text-gray-400">#{{ $override->id }} · {{ $override->created_at?->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-3 text-sm font-medium">{{ $override->benefit?->formatValue($override->benefit->normalize($override->value)) }}</td>
                                <td class="px-5 py-3 text-xs">
                                    {{ $override->starts_at?->format('d M Y H:i') ?? 'Immediately' }}
                                    <span class="text-gray-400">→</span>
                                    {{ $override->expires_at?->format('d M Y H:i') ?? 'No expiry' }}
                                </td>
                                <td class="px-5 py-3"><x-backend.status-badge :status="$status" :map="$statusMap" /></td>
                                <td class="px-5 py-3 text-xs max-w-xs">
                                    <p class="text-gray-600 line-clamp-2" title="{{ $override->reason }}">{{ $override->reason ?: '—' }}</p>
                                    <p class="text-gray-400">{{ $override->createdBy?->name ?? 'System' }}</p>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($status !== 'expired')
                                            <button type="button" @click="$dispatch('open-modal-override-edit-{{ $override->id }}')" title="Edit"
                                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-indigo-600 hover:bg-indigo-50"><i class="fa-regular fa-pen-to-square"></i></button>
                                        @endif
                                        @if($status === 'inactive')
                                            <form method="POST" action="{{ route('admin.billing.benefit-overrides.reactivate', [$account, $override]) }}">
                                                @csrf
                                                <button type="submit" title="Reactivate" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-toggle-off"></i></button>
                                            </form>
                                        @elseif($status !== 'expired')
                                            <form method="POST" action="{{ route('admin.billing.benefit-overrides.deactivate', [$account, $override]) }}">
                                                @csrf
                                                <button type="submit" title="Deactivate (can be reactivated)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-emerald-600 hover:bg-gray-100"><i class="fa-solid fa-toggle-on"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.billing.benefit-overrides.revoke', [$account, $override]) }}"
                                                  onsubmit="return confirmSwal(this, 'Revoke this override?', 'It ends now and stays in history; the subscription value applies again.', 'warning', 'Yes, revoke')">
                                                @csrf
                                                <button type="submit" title="Revoke (end now)" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-500 hover:bg-red-50"><i class="fa-solid fa-ban"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modals --}}
    <x-backend.modal id="override-create" title="Add Benefit Override" width="max-w-xl">
        @include('backend.admin.billing.benefit-overrides.partials._override-form', ['override' => null])
    </x-backend.modal>

    @foreach($history->filter(fn ($o) => $o->status() !== 'expired' && $o->benefit) as $override)
        <x-backend.modal :id="'override-edit-'.$override->id" :title="'Edit Override #'.$override->id" width="max-w-xl">
            @include('backend.admin.billing.benefit-overrides.partials._override-form', ['override' => $override])
        </x-backend.modal>
    @endforeach

    {{-- Reopen the form that failed validation --}}
    @if($errors->any() && old('_form'))
        <script>
            document.addEventListener('alpine:initialized', () => {
                const form = @js(old('_form'));
                window.dispatchEvent(new CustomEvent(form === 'create' ? 'open-modal-override-create' : 'open-modal-override-' + form));
            });
        </script>
    @endif

@endsection
