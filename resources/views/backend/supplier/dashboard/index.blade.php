@extends('backend.layouts.supplier')

@section('title', 'Supplier Dashboard')
@section('breadcrumb', 'Overview')

@section('body')

    @if($capabilityStatus !== 'active')

        @if($capabilityStatus === 'pending')
            <div class="bg-white rounded-xl border border-amber-200 p-6 text-center max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-clock text-amber-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your Supplier Application is Under Review</h1>
                <p class="text-sm text-gray-500 mt-2">We'll notify you as soon as an administrator reviews your application. This usually takes 1–2 business days.</p>
            </div>
        @elseif($capabilityStatus === 'revision_required')
            <div class="bg-white rounded-xl border border-amber-200 p-6 max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Revision Required</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $revisionReason ?? 'Please review and update your Supplier application.' }}</p>
                <a href="{{ route('supplier.onboarding.revision') }}" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Update Application</a>
            </div>
        @elseif($capabilityStatus === 'rejected')
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-xmark text-red-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your application was not approved</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $rejectionReason ?? 'Please contact support for more information.' }}</p>
            </div>
        @elseif($capabilityStatus === 'suspended')
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-ban text-red-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your Supplier capability is suspended</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $suspensionReason ?? 'Please contact support for more information.' }}</p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Complete your Supplier application</h1>
                <a href="{{ route('supplier.onboarding.profile') }}" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Continue Setup</a>
            </div>
        @endif

    @else

        <x-backend.page-header title="Welcome back, {{ $user->name }}" subtitle="Here's your supplier activity overview.">
            <x-slot:actions>
                <a href="{{ route('supplier.catalog.listings.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Listing
                </a>
            </x-slot:actions>
        </x-backend.page-header>

        {{-- Subscription alert --}}
        @if($subscription && $subscription->status === 'past_due')
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <p class="text-sm text-red-700">Your subscription payment is past due. <a href="{{ route('supplier.subscription.current') }}" class="font-semibold underline">Renew now</a> to avoid service interruption.</p>
            </div>
        @elseif(!$subscription)
            <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                <p class="text-sm text-amber-700">You don't have an active subscription. <a href="{{ route('supplier.pricing') }}" class="font-semibold underline">Choose a plan</a> to unlock all features.</p>
            </div>
        @endif

        {{-- Row 1: Catalog & Opportunities --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <x-backend.stat-card label="Total Listings" :value="$totalListings" icon="fa-box-open" :href="route('supplier.catalog.listings.index')" />
            <x-backend.stat-card label="Published" :value="$publishedListings" tone="success" icon="fa-circle-check" :href="route('supplier.catalog.listings.index', ['status' => 'approved'])" />
            <x-backend.stat-card label="Pending Approval" :value="$pendingListings" tone="warning" icon="fa-hourglass-half" :href="route('supplier.catalog.listings.index', ['status' => 'pending'])" />
            <x-backend.stat-card label="Available RFQs" :value="$availableOpportunities" tone="info" icon="fa-magnifying-glass-chart" :href="route('supplier.opportunities.index')" />
        </div>

        {{-- Row 2: Procurement --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-backend.stat-card label="Draft Quotations" :value="$draftQuotations" icon="fa-file-pen" :href="route('supplier.quotations.index', ['status' => 'draft'])" />
            <x-backend.stat-card label="Submitted Quotations" :value="$submittedQuotations" tone="info" icon="fa-paper-plane" :href="route('supplier.quotations.index')" />
            <x-backend.stat-card label="Awards Awaiting Response" :value="$pendingAwards" tone="warning" icon="fa-trophy" :href="route('supplier.awards.index')" />
            <x-backend.stat-card label="Issued Purchase Orders" :value="$issuedPoCount" tone="info" icon="fa-clipboard-list" :href="route('supplier.purchase-orders.index')" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                {{-- Action Required --}}
                @if($pendingAwards > 0 || $revisionRequests > 0 || $rejectedListings > 0)
                    <x-backend.form-card title="Action Required">
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            @if($pendingAwards > 0)
                                <li>
                                    <a href="{{ route('supplier.awards.index') }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-trophy text-amber-500 mr-2"></i>{{ $pendingAwards }} award(s) awaiting your response</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            @endif
                            @if($revisionRequests > 0)
                                <li>
                                    <a href="{{ route('supplier.quotations.index', ['status' => 'revision_requested']) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-rotate text-amber-500 mr-2"></i>{{ $revisionRequests }} quotation revision(s) requested by buyers</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            @endif
                            @if($rejectedListings > 0)
                                <li>
                                    <a href="{{ route('supplier.catalog.listings.index', ['status' => 'rejected']) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i>{{ $rejectedListings }} listing(s) rejected — please review and resubmit</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </x-backend.form-card>
                @endif

                {{-- Recent Quotations --}}
                <x-backend.form-card title="Recent Quotations" description="Your most recently updated quotations.">
                    @if($recentQuotations->isEmpty())
                        <x-backend.empty-state icon="fa-file-invoice" title="No quotations yet" description="Browse available RFQs and submit your first quotation.">
                            <x-slot:actions>
                                <a href="{{ route('supplier.opportunities.index') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Browse RFQs</a>
                            </x-slot:actions>
                        </x-backend.empty-state>
                    @else
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            @foreach($recentQuotations as $quotation)
                                <li>
                                    <a href="{{ route('supplier.quotations.show', $quotation) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $quotation->rfq?->title ?? $quotation->quotation_number }}</p>
                                            <p class="text-xs text-gray-400">{{ $quotation->quotation_number }} &middot; {{ $quotation->submitted_at?->format('d M Y') ?? $quotation->created_at->format('d M Y') }}</p>
                                        </div>
                                        <x-backend.status-badge :status="$quotation->status" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>

                {{-- Recent Purchase Orders --}}
                <x-backend.form-card title="Recent Purchase Orders">
                    @if($recentPurchaseOrders->isEmpty())
                        <p class="text-sm text-gray-400">No purchase orders yet.</p>
                    @else
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            @foreach($recentPurchaseOrders as $po)
                                <li>
                                    <a href="{{ route('supplier.purchase-orders.show', $po) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $po->po_number }}</p>
                                            <p class="text-xs text-gray-400">{{ $po->rfq?->title }} &middot; {{ $po->issued_at?->format('d M Y') }}</p>
                                        </div>
                                        <x-backend.status-badge :status="$po->status" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>

            </div>

            <div class="space-y-6">

                {{-- Upcoming Deadlines --}}
                <x-backend.form-card title="Upcoming Deadlines">
                    @if($quotationDeadlines->isEmpty() && $awardDeadlines->isEmpty())
                        <p class="text-sm text-gray-400">No upcoming deadlines in the next 7 days.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($quotationDeadlines as $entry)
                                @if($entry->rfq)
                                    <li>
                                        <a href="{{ route('supplier.opportunities.show', $entry->rfq) }}" class="block">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $entry->rfq->title }}</p>
                                            <p class="text-xs text-amber-600 mt-0.5"><i class="fa-regular fa-clock mr-1"></i>Quotation due {{ $entry->rfq->quotation_deadline->format('d M Y, h:i A') }}</p>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                            @foreach($awardDeadlines as $award)
                                <li>
                                    <a href="{{ route('supplier.awards.show', $award) }}" class="block">
                                        <p class="text-sm font-medium text-gray-900 truncate">Award {{ $award->award_number }}</p>
                                        <p class="text-xs text-red-600 mt-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Response due {{ $award->response_deadline->format('d M Y, h:i A') }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>

                {{-- Recent Awards --}}
                <x-backend.form-card title="Recent Awards">
                    @if($recentAwards->isEmpty())
                        <p class="text-sm text-gray-400">No awards yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($recentAwards as $award)
                                <li>
                                    <a href="{{ route('supplier.awards.show', $award) }}" class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-gray-700 truncate">{{ $award->rfq?->title ?? $award->award_number }}</span>
                                        <x-backend.status-badge :status="$award->status" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>

                {{-- Quick Stats --}}
                <x-backend.form-card title="Quick Stats">
                    <ul class="space-y-2">
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fa-solid fa-star text-amber-400 mr-2"></i>Reviews Received</span>
                            <span class="font-semibold text-gray-900">{{ $reviewCount }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fa-solid fa-circle-check text-green-500 mr-2"></i>Completed POs</span>
                            <span class="font-semibold text-gray-900">{{ $completedPoCount }}</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fa-solid fa-life-ring text-gray-400 mr-2"></i>Open Tickets</span>
                            <span class="font-semibold text-gray-900">{{ $openTicketsCount }}</span>
                        </li>
                    </ul>
                </x-backend.form-card>

            </div>

        </div>

    @endif

@endsection
