@extends('backend.layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Overview')

@section('body')

    <x-backend.page-header title="Platform Overview" subtitle="{{ now()->format('l, d M Y') }} — a snapshot of everything that needs your attention." />

    @if(isset($userMetrics))
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Users &amp; Accounts</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-backend.stat-card label="Total Users" :value="$userMetrics['total']" icon="fa-users" :href="route('admin.users.index')" />
            <x-backend.stat-card label="Active Users" :value="$userMetrics['active']" tone="success" icon="fa-user-check" :href="route('admin.users.index')" />
            <x-backend.stat-card label="Total Accounts" :value="$accountMetrics['total']" icon="fa-building" :href="route('admin.accounts.index')" />
            <x-backend.stat-card label="Pending Approval" :value="$accountMetrics['pending_approval']" tone="warning" icon="fa-hourglass-half" :href="route('admin.accounts.index', ['status' => 'pending_approval'])" />
        </div>
    @endif

    @if(isset($capabilityMetrics))
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Capability Applications</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-backend.stat-card label="Buyer Applications Pending" :value="$capabilityMetrics['buyer_pending']" tone="warning" icon="fa-cart-shopping" :href="route('admin.capabilities.index', ['type' => 'buyer', 'status' => 'pending'])" />
            <x-backend.stat-card label="Active Buyers" :value="$capabilityMetrics['buyer_active']" tone="success" icon="fa-cart-shopping" :href="route('admin.buyers.index')" />
            <x-backend.stat-card label="Supplier Applications Pending" :value="$capabilityMetrics['supplier_pending']" tone="warning" icon="fa-store" :href="route('admin.capabilities.index', ['type' => 'supplier', 'status' => 'pending'])" />
            <x-backend.stat-card label="Active Suppliers" :value="$capabilityMetrics['supplier_active']" tone="success" icon="fa-store" :href="route('admin.suppliers.index')" />
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            @php
                $actionItems = collect();
                if (isset($capabilityMetrics)) {
                    if ($capabilityMetrics['buyer_pending'] > 0) $actionItems->push(['icon' => 'fa-cart-shopping', 'label' => $capabilityMetrics['buyer_pending'].' Buyer application(s) require review', 'url' => route('admin.capabilities.index', ['type' => 'buyer', 'status' => 'pending'])]);
                    if ($capabilityMetrics['supplier_pending'] > 0) $actionItems->push(['icon' => 'fa-store', 'label' => $capabilityMetrics['supplier_pending'].' Supplier application(s) require review', 'url' => route('admin.capabilities.index', ['type' => 'supplier', 'status' => 'pending'])]);
                }
                if (isset($documentMetrics) && $documentMetrics['pending'] > 0) $actionItems->push(['icon' => 'fa-file-lines', 'label' => $documentMetrics['pending'].' Supplier document(s) await verification', 'url' => route('admin.suppliers.index', ['document_status' => 'pending'])]);
                if (isset($catalogMetrics)) {
                    if ($catalogMetrics['listings_pending'] > 0) $actionItems->push(['icon' => 'fa-box', 'label' => $catalogMetrics['listings_pending'].' Listing(s) await approval', 'url' => route('admin.catalog.listings.index', ['status' => 'pending'])]);
                    if ($catalogMetrics['category_suggestions'] > 0) $actionItems->push(['icon' => 'fa-layer-group', 'label' => $catalogMetrics['category_suggestions'].' Category suggestion(s) pending', 'url' => route('admin.catalog.categories.index', ['tab' => 'suggestions'])]);
                }
                if (($roleRequestsPending ?? 0) > 0) $actionItems->push(['icon' => 'fa-shield-halved', 'label' => $roleRequestsPending.' Custom role request(s) await review', 'url' => route('admin.access-control.role-requests.index')]);
                if (($conversionsPending ?? 0) > 0) $actionItems->push(['icon' => 'fa-right-left', 'label' => $conversionsPending.' Account conversion request(s) await review', 'url' => route('admin.conversions.index')]);
                if (isset($supportMetrics)) {
                    if ($supportMetrics['reports_pending'] > 0) $actionItems->push(['icon' => 'fa-flag', 'label' => $supportMetrics['reports_pending'].' Review report(s) await moderation', 'url' => route('admin.reviews.reports.index')]);
                    if ($supportMetrics['unassigned_tickets'] > 0) $actionItems->push(['icon' => 'fa-life-ring', 'label' => $supportMetrics['unassigned_tickets'].' Unassigned support ticket(s)', 'url' => route('admin.tickets.index', ['assigned' => 'unassigned'])]);
                }
                if (isset($billingMetrics) && $billingMetrics['failed'] > 0) $actionItems->push(['icon' => 'fa-triangle-exclamation', 'label' => $billingMetrics['failed'].' Failed subscription payment(s)', 'url' => route('admin.billing.payments.index', ['status' => 'failed'])]);
            @endphp

            @if($actionItems->isNotEmpty())
                <x-backend.form-card title="Action Required">
                    <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                        @foreach($actionItems as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                    <span class="text-sm text-gray-700"><i class="fa-solid {{ $item['icon'] }} text-amber-500 mr-2"></i>{{ $item['label'] }}</span>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

            @if(isset($procurementMetrics))
                <x-backend.form-card title="Procurement Activity">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                        <div><p class="text-gray-500">Open RFQs</p><p class="text-lg font-bold text-gray-900">{{ $procurementMetrics['open_rfqs'] }}</p></div>
                        <div><p class="text-gray-500">Quotations Submitted</p><p class="text-lg font-bold text-gray-900">{{ $procurementMetrics['quotations_submitted'] }}</p></div>
                        <div><p class="text-gray-500">Awards Awaiting Response</p><p class="text-lg font-bold text-gray-900">{{ $procurementMetrics['awards_awaiting_response'] }}</p></div>
                        <div><p class="text-gray-500">Purchase Orders Issued</p><p class="text-lg font-bold text-gray-900">{{ $procurementMetrics['po_issued'] }}</p></div>
                        <div><p class="text-gray-500">Purchase Orders Completed</p><p class="text-lg font-bold text-gray-900">{{ $procurementMetrics['po_completed'] }}</p></div>
                        <div><p class="text-gray-500">Cancelled / Disputed POs</p><p class="text-lg font-bold text-gray-900">{{ $procurementMetrics['po_cancelled_disputed'] }}</p></div>
                    </div>
                </x-backend.form-card>
            @endif

        </div>

        <div class="space-y-6">

            @if(isset($documentMetrics))
                <x-backend.form-card title="Supplier Verification">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Pending Documents</dt><dd class="font-medium text-gray-900">{{ $documentMetrics['pending'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Rejected (awaiting replacement)</dt><dd class="font-medium text-gray-900">{{ $documentMetrics['rejected'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Expiring within 30 days</dt><dd class="font-medium text-gray-900">{{ $documentMetrics['expiring_soon'] }}</dd></div>
                    </dl>
                </x-backend.form-card>
            @endif

            @if(isset($billingMetrics))
                <x-backend.form-card title="Subscription Health">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Active</dt><dd class="font-medium text-green-700">{{ $billingMetrics['active'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Trialing</dt><dd class="font-medium text-gray-900">{{ $billingMetrics['trialing'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Past Due</dt><dd class="font-medium text-amber-700">{{ $billingMetrics['past_due'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Expired</dt><dd class="font-medium text-gray-500">{{ $billingMetrics['expired'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Failed Payments</dt><dd class="font-medium text-red-600">{{ $billingMetrics['failed'] }}</dd></div>
                    </dl>
                </x-backend.form-card>
            @endif

            @if(($recentActivity ?? collect())->isNotEmpty())
                <x-backend.form-card title="Recent Activity">
                    <ul class="space-y-3">
                        @foreach($recentActivity as $activity)
                            <li class="text-sm">
                                <p class="text-gray-700">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-400">{{ $activity->causer?->name ?? 'System' }} &middot; {{ $activity->created_at->diffForHumans() }}</p>
                            </li>
                        @endforeach
                    </ul>
                </x-backend.form-card>
            @endif

        </div>
    </div>

@endsection
