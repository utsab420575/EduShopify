@extends('backend.layouts.buyer')

@section('title', 'Dashboard')
@section('breadcrumb', 'Overview')

@section('body')

    @if($capabilityStatus !== 'active')

        @if($capabilityStatus === 'pending')
            <div class="bg-white rounded-xl border border-amber-200 p-6 text-center max-w-xl mx-auto mt-10">
                <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-clock text-amber-600 text-xl"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">Your Buyer Application is Under Review</h1>
                <p class="text-sm text-gray-500 mt-2">We'll notify you as soon as an administrator reviews your application. This usually takes 1–2 business days.</p>
            </div>
        @elseif($capabilityStatus === 'revision_required')
            <div class="bg-white rounded-xl border border-amber-200 p-6 max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Revision required</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $revisionReason ?? 'Please review and update your Buyer application.' }}</p>
                <a href="{{ route('buyer.onboarding.profile') }}" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Update Application</a>
            </div>
        @elseif($capabilityStatus === 'rejected')
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Your application was not approved</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $rejectionReason ?? 'Please contact support for more information.' }}</p>
            </div>
        @elseif($capabilityStatus === 'suspended')
            <div class="bg-white rounded-xl border border-red-200 p-6 max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Your Buyer capability is suspended</h1>
                <p class="text-sm text-gray-600 mt-2">{{ $suspensionReason ?? 'Please contact support for more information.' }}</p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center max-w-xl mx-auto mt-10">
                <h1 class="text-lg font-bold text-gray-900">Complete your Buyer application</h1>
                <a href="{{ route('buyer.onboarding.profile') }}" class="btn-primary inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg mt-4">Continue Setup</a>
            </div>
        @endif

    @else

        <x-backend.page-header title="Welcome back, {{ $user->name }}" subtitle="Here's what's happening with your procurement activity.">
            <x-slot:actions>
                @can('create', \App\Models\Rfq::class)
                    <a href="{{ route('buyer.rfqs.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Create RFQ
                    </a>
                @endcan
            </x-slot:actions>
        </x-backend.page-header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-backend.stat-card label="Open RFQs" :value="$openRfqCount" hint="Currently accepting quotations" icon="fa-file-signature" :href="route('buyer.rfqs.index', ['status' => 'open'])" />
            <x-backend.stat-card label="Draft RFQs" :value="$draftRfqCount" hint="Not yet published" icon="fa-file-pen" :href="route('buyer.rfqs.index', ['status' => 'draft'])" />
            <x-backend.stat-card label="Quotations to Review" :value="$quotationsAwaitingReview" tone="info" hint="Awaiting your decision" icon="fa-inbox" :href="route('buyer.quotations.index')" />
            <x-backend.stat-card label="Awards Awaiting Response" :value="$awardsAwaitingResponse" tone="warning" hint="Waiting on supplier" icon="fa-trophy" :href="route('buyer.awards.index')" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-backend.stat-card label="Shortlisted Quotes" :value="$shortlistedCount" icon="fa-star" :href="route('buyer.quotations.index')" />
            <x-backend.stat-card label="Active Purchase Orders" :value="$activePoCount" tone="info" icon="fa-clipboard-list" :href="route('buyer.purchase-orders.index')" />
            <x-backend.stat-card label="Completed Purchase Orders" :value="$completedPoCount" tone="success" icon="fa-circle-check" :href="route('buyer.purchase-orders.index')" />
            <x-backend.stat-card label="Open Support Tickets" :value="$openTicketsCount" icon="fa-life-ring" :href="route('buyer.tickets.index')" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                @if($pendingQuestionsCount > 0 || $poAwaitingCompletion > 0 || $awardsAwaitingResponse > 0)
                    <x-backend.form-card title="Action Required">
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            @if($pendingQuestionsCount > 0)
                                <li>
                                    <a href="{{ route('buyer.rfqs.index') }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-circle-question text-amber-500 mr-2"></i>{{ $pendingQuestionsCount }} supplier question(s) waiting for your answer</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            @endif
                            @if($awardsAwaitingResponse > 0)
                                <li>
                                    <a href="{{ route('buyer.awards.index') }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-trophy text-amber-500 mr-2"></i>{{ $awardsAwaitingResponse }} award(s) awaiting supplier response</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            @endif
                            @if($poAwaitingCompletion > 0)
                                <li>
                                    <a href="{{ route('buyer.purchase-orders.index', ['status' => 'delivered']) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
                                        <span class="text-sm text-gray-700"><i class="fa-solid fa-box-open text-amber-500 mr-2"></i>{{ $poAwaitingCompletion }} purchase order(s) delivered — confirm to complete</span>
                                        <i class="fa-solid fa-chevron-right text-xs text-gray-300"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </x-backend.form-card>
                @endif

                <x-backend.form-card title="My RFQs" description="Your most recently updated RFQs.">
                    @if($recentRfqs->isEmpty())
                        <x-backend.empty-state icon="fa-file-signature" title="No RFQs yet" description="Create your first RFQ to start receiving supplier quotations.">
                            <x-slot:actions>
                                <a href="{{ route('buyer.rfqs.create') }}" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Create RFQ</a>
                            </x-slot:actions>
                        </x-backend.empty-state>
                    @else
                        <ul class="divide-y divide-gray-100 -mx-5 -mb-5">
                            @foreach($recentRfqs as $rfq)
                                <li>
                                    <a href="{{ route('buyer.rfqs.show', $rfq) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $rfq->title }}</p>
                                            <p class="text-xs text-gray-400">{{ $rfq->rfq_number }} &middot; {{ $rfq->created_at->format('d M Y') }}</p>
                                        </div>
                                        <x-backend.status-badge :status="$rfq->status" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>

            </div>

            <div class="space-y-6">
                <x-backend.form-card title="Upcoming Deadlines">
                    @if($upcomingDeadlines->isEmpty())
                        <p class="text-sm text-gray-400">No quotation deadlines in the next 3 days.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($upcomingDeadlines as $rfq)
                                <li>
                                    <a href="{{ route('buyer.rfqs.show', $rfq) }}" class="block">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $rfq->title }}</p>
                                        <p class="text-xs text-amber-600 mt-0.5"><i class="fa-regular fa-clock mr-1"></i>{{ $rfq->quotation_deadline->format('d M Y, h:i A') }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>

                <x-backend.form-card title="Recent Quotations">
                    @if($recentQuotations->isEmpty())
                        <p class="text-sm text-gray-400">No quotations received yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach($recentQuotations as $quotation)
                                <li>
                                    <a href="{{ route('buyer.quotations.show', $quotation) }}" class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-gray-700 truncate">{{ $quotation->supplierAccount?->supplierProfile?->display_name ?? $quotation->supplierAccount?->display_name }}</span>
                                        <x-backend.status-badge :status="$quotation->status" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-backend.form-card>
            </div>

        </div>

    @endif

@endsection
