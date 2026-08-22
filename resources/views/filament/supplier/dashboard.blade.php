<x-filament-panels::page>
    <style>
    .db-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        font-family: inherit;
    }

    /* Welcome Banner */
    .db-banner {
        position: relative;
        overflow: hidden;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #0d9488 0%, #059669 100%);
        padding: 1.5rem;
        color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .db-banner-content {
        position: relative;
        z-index: 10;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    @media (min-width: 640px) {
        .db-banner-content {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    .db-banner-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }
    .db-banner-sub {
        font-size: 0.875rem;
        color: #ccfbf1;
        margin: 0.25rem 0 0;
    }
    .db-banner-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .db-banner-btn-white {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        color: #0f766e;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .db-banner-btn-white:hover {
        background: #f0fdfa;
    }
    .db-banner-btn-trans {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }
    .db-banner-btn-trans:hover {
        background: rgba(255, 255, 255, 0.25);
    }

    /* Plan Alert */
    .db-alert {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid transparent;
    }
    .db-alert-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }
    .dark .db-alert-warning {
        background: rgba(245, 158, 11, 0.05);
        border-color: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    .db-alert-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1e3a8a;
    }
    .dark .db-alert-info {
        background: rgba(59, 130, 246, 0.05);
        border-color: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    .db-alert-content {
        flex: 1;
        min-width: 0;
    }
    .db-alert-title {
        font-size: 0.875rem;
        font-weight: 700;
        margin: 0;
    }
    .db-alert-sub {
        font-size: 0.75rem;
        margin: 0.125rem 0 0;
        opacity: 0.9;
    }
    .db-alert-btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.5rem;
        text-decoration: none;
        flex-shrink: 0;
    }
    .db-alert-btn-warning {
        background: #d97706;
        color: #ffffff;
    }
    .db-alert-btn-warning:hover {
        background: #b45309;
    }
    .db-alert-btn-info {
        background: #2563eb;
        color: #ffffff;
    }
    .db-alert-btn-info:hover {
        background: #1d4ed8;
    }

    /* Stats Grid */
    .db-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 1024px) {
        .db-stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .db-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .dark .db-stat-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .db-stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .db-stat-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #64748b;
        margin: 0;
    }
    .dark .db-stat-label {
        color: #94a3b8;
    }
    .db-stat-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        flex-shrink: 0;
    }
    .db-stat-val {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0.75rem 0 0;
    }
    .dark .db-stat-val {
        color: #ffffff;
    }

    /* Grids & Layouts */
    .db-grid-main {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .db-grid-main {
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        }
    }
    .db-grid-bottom {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .db-grid-bottom {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* Panel cards */
    .db-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .dark .db-card {
        background: #0f172a;
        border-color: #1e293b;
    }
    .db-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .db-card-header {
        border-color: #1e293b;
    }
    .db-card-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dark .db-card-title {
        color: #ffffff;
    }
    .db-card-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #0d9488;
        text-decoration: none;
    }
    .dark .db-card-link {
        color: #2dd4bf;
    }
    .db-card-link:hover {
        text-decoration: underline;
    }

    /* Available RFQ items list */
    .db-list {
        display: flex;
        flex-direction: column;
    }
    .db-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        text-decoration: none;
        transition: background 0.15s;
    }
    .dark .db-list-item {
        border-color: #1e293b;
    }
    .db-list-item:hover {
        background: #f8fafc;
    }
    .dark .db-list-item:hover {
        background: rgba(30, 41, 59, 0.3);
    }
    .db-list-item:last-child {
        border-bottom: none;
    }
    .db-item-details {
        min-width: 0;
        flex: 1;
    }
    .db-item-title {
        font-size: 0.875rem;
        font-weight: 750;
        color: #0f172a;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .db-item-title {
        color: #ffffff;
    }
    .db-item-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        margin-top: 0.375rem;
        font-size: 0.75rem;
        color: #64748b;
    }
    .dark .db-item-meta {
        color: #94a3b8;
    }
    .db-meta-pill {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .db-item-action {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex-shrink: 0;
    }
    .db-item-budget {
        font-size: 0.875rem;
        font-weight: 700;
        color: #334155;
    }
    .dark .db-item-budget {
        color: #cbd5e1;
    }
    .db-bid-btn {
        background: #0d9488;
        color: #ffffff;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.15s;
    }
    .db-bid-btn:hover {
        background: #0f766e;
    }

    /* Sidebar navigation */
    .db-nav-list {
        display: flex;
        flex-direction: column;
        padding: 0.5rem;
    }
    .db-nav-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-radius: 0.5rem;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        text-decoration: none;
        transition: background 0.15s;
    }
    .dark .db-nav-item {
        color: #cbd5e1;
    }
    .db-nav-item:hover {
        background: #f8fafc;
        color: #0f172a;
    }
    .dark .db-nav-item:hover {
        background: rgba(30, 41, 59, 0.4);
        color: #ffffff;
    }

    /* Profile completeness strength */
    .db-strength-card {
        background: #f0fdfa;
        border: 1px solid #ccfbf1;
        border-radius: 0.75rem;
        padding: 1.25rem;
    }
    .dark .db-strength-card {
        background: rgba(13, 148, 136, 0.05);
        border-color: rgba(13, 148, 136, 0.15);
    }
    .db-strength-header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .db-strength-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #0f766e;
        margin: 0;
    }
    .dark .db-strength-title {
        color: #2dd4bf;
    }
    .db-progress-bar-wrapper {
        height: 0.5rem;
        width: 100%;
        background: #ccfbf1;
        border-radius: 9999px;
        overflow: hidden;
        margin-top: 0.625rem;
    }
    .dark .db-progress-bar-wrapper {
        background: #1e293b;
    }
    .db-progress-bar {
        height: 100%;
        background: #0d9488;
        border-radius: 9999px;
    }
    .db-strength-sub {
        font-size: 0.75rem;
        color: #0f766e;
        margin: 0.375rem 0 0;
    }
    .dark .db-strength-sub {
        color: #94a3b8;
    }
    .db-strength-link {
        font-size: 0.75rem;
        font-weight: 700;
        color: #0d9488;
        text-decoration: none;
        display: inline-block;
        margin-top: 0.75rem;
    }
    .db-strength-link:hover {
        text-decoration: underline;
    }

    /* Empty states */
    .db-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 3rem 0;
        text-align: center;
    }
    .db-empty-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #64748b;
        margin: 0;
    }
    .db-empty-sub {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0;
    }
    .db-empty-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #0d9488;
        text-decoration: none;
        margin-top: 0.5rem;
    }
    .db-empty-link:hover {
        text-decoration: underline;
    }
    </style>

    <div class="db-container">

        {{-- Welcome Banner --}}
        <div class="db-banner">
            <div class="db-banner-content">
                <div>
                    <p style="font-size:0.875rem;font-weight:500;color:#ccfbf1;margin:0;">Welcome back,</p>
                    <h1 class="db-banner-title" style="margin-top:0.25rem;">{{ $companyName }}</h1>
                    <p class="db-banner-sub">Manage your RFQ bids, products, and supplier profile.</p>
                </div>
                <div class="db-banner-actions">
                    <a href="#" class="db-banner-btn-white">
                        <x-heroicon-s-inbox-arrow-down style="width:16px;height:16px;" />
                        <span>Browse RFQs</span>
                    </a>
                    <a href="#" class="db-banner-btn-trans">
                        <x-heroicon-s-user-circle style="width:16px;height:16px;" />
                        <span>View Profile</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- State-aware Supplier Onboarding Banners --}}
        @if($onboardingState === 'pending')
            <div class="db-alert db-alert-info">
                <x-heroicon-s-clock style="width:24px;height:24px;" class="flex-shrink-0 text-amber-500" />
                <div class="db-alert-content">
                    <p class="db-alert-title">Supplier Application Under Verification</p>
                    <p class="db-alert-sub">Your Supplier registration details and compliance documents are currently being reviewed by our administration team.</p>
                </div>
            </div>
        @elseif($onboardingState === 'pending_document_action_required')
            <div class="db-alert db-alert-warning border-red-300 bg-red-50 text-red-900">
                <x-heroicon-s-exclamation-triangle style="width:24px;height:24px;" class="flex-shrink-0 text-red-600" />
                <div class="db-alert-content">
                    <p class="db-alert-title text-red-900 font-bold">Action Required: Compliance Document Rejected</p>
                    <p class="db-alert-sub text-red-700">One or more of your submitted compliance documents requires correction or re-upload.</p>
                </div>
                <a href="{{ route('supplier.onboarding.documents') }}" class="db-alert-btn bg-red-600 text-white hover:bg-red-700">
                    Re-upload Document →
                </a>
            </div>
        @elseif($onboardingState === 'revision_required')
            <div class="db-alert db-alert-warning border-amber-300 bg-amber-50 text-amber-900">
                <x-heroicon-s-arrow-path style="width:24px;height:24px;" class="flex-shrink-0 text-amber-600" />
                <div class="db-alert-content">
                    <p class="db-alert-title text-amber-900 font-bold">Application Revision Requested</p>
                    <p class="db-alert-sub text-amber-800">
                        @if($capability?->revision_reason)
                            <strong>Admin Note:</strong> {{ $capability->revision_reason }}
                        @else
                            Please review your profile details and resubmit your application.
                        @endif
                    </p>
                </div>
                <a href="{{ route('supplier.onboarding.profile') }}" class="db-alert-btn bg-amber-600 text-white hover:bg-amber-700">
                    Review & Resubmit →
                </a>
            </div>
        @elseif($onboardingState === 'rejected')
            <div class="db-alert bg-red-100 border-red-300 text-red-900">
                <x-heroicon-s-x-circle style="width:24px;height:24px;" class="flex-shrink-0 text-red-600" />
                <div class="db-alert-content">
                    <p class="db-alert-title text-red-900 font-bold">Supplier Application Rejected</p>
                    <p class="db-alert-sub text-red-800">
                        @if($capability?->rejection_reason)
                            <strong>Reason:</strong> {{ $capability->rejection_reason }}
                        @else
                            Your application to operate as a Supplier was not approved.
                        @endif
                    </p>
                </div>
            </div>
        @elseif($onboardingState === 'approved_no_subscription')
            <div class="db-alert db-alert-warning border-green-300 bg-green-50 text-green-900">
                <x-heroicon-s-check-circle style="width:24px;height:24px;" class="flex-shrink-0 text-green-600" />
                <div class="db-alert-content">
                    <p class="db-alert-title text-green-900 font-bold">Capability Approved 🎉 Select a Plan to Activate</p>
                    <p class="db-alert-sub text-green-800">Your Supplier capability has been approved! Choose a membership plan to unlock RFQs and marketplace bidding.</p>
                </div>
                <a href="{{ route('supplier.onboarding.plan') }}" class="db-alert-btn bg-green-600 text-white hover:bg-green-700">
                    Select Plan →
                </a>
            </div>
        @endif

        {{-- Plan Status Banner --}}
        @if($planStatus === 'none' && $onboardingState === 'ready')
            <div class="db-alert db-alert-warning">
                <x-heroicon-s-exclamation-triangle style="width:24px;height:24px;" class="flex-shrink-0" />
                <div class="db-alert-content">
                    <p class="db-alert-title">No active plan</p>
                    <p class="db-alert-sub">Choose a subscription plan to access RFQs and list your products.</p>
                </div>
                <a href="{{ route('supplier.onboarding.plan') }}" class="db-alert-btn db-alert-btn-warning">
                    View Plans
                </a>
            </div>
        @elseif($planStatus === 'free')
            <div class="db-alert db-alert-info">
                <x-heroicon-s-information-circle style="width:24px;height:24px;" class="flex-shrink-0" />
                <div class="db-alert-content">
                    <p class="db-alert-title">
                        <span>Free Trial Plan</span>
                        @if($planExpiry)
                            <span style="font-weight:400;font-size:0.75rem;margin-left:0.25rem;opacity:0.8;">— expires {{ $planExpiry->format('d M Y') }}</span>
                        @endif
                    </p>
                    <p class="db-alert-sub">Upgrade to receive RFQs faster and unlock more features.</p>
                </div>
                <a href="{{ route('supplier.onboarding.plan') }}" class="db-alert-btn db-alert-btn-info">
                    Upgrade
                </a>
            </div>
        @endif

        {{-- KPI Stats --}}
        <div class="db-stats-grid">
            @php
                $stats = [
                    ['label' => 'New RFQs',         'value' => '0', 'icon' => 'heroicon-s-bell',               'color' => 'color:#0d9488;',   'bg' => 'background:#ccfbf1;'],
                    ['label' => 'Bids Submitted',   'value' => '0', 'icon' => 'heroicon-s-paper-airplane',      'color' => 'color:#2563eb;',   'bg' => 'background:#dbeafe;'],
                    ['label' => 'Projects Won',     'value' => '0', 'icon' => 'heroicon-s-trophy',              'color' => 'color:#d97706;',  'bg' => 'background:#fef3c7;'],
                    ['label' => 'Response Rate',    'value' => '—', 'icon' => 'heroicon-s-chart-bar',           'color' => 'color:#059669;','bg' => 'background:#d1fae5;'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="db-stat-card">
                    <div class="db-stat-header">
                        <p class="db-stat-label">{{ $stat['label'] }}</p>
                        <span class="db-stat-icon-wrapper" style="{{ $stat['bg'] }} {{ $stat['color'] }}">
                            <x-dynamic-component :component="$stat['icon']" style="width:20px;height:20px;" />
                        </span>
                    </div>
                    <p class="db-stat-val">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Main Grid --}}
        <div class="db-grid-main">

            {{-- Available RFQs --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <x-heroicon-s-bell style="width:20px;height:20px;color:#0d9488;" />
                        <span>Available RFQs</span>
                    </h2>
                    <a href="#" class="db-card-link">Browse all →</a>
                </div>
                <div class="db-list">
                    @php
                        $demoRfqs = [
                            ['title' => '50 Interactive Displays', 'category' => 'Smart Classroom', 'budget' => '$100,000', 'deadline' => '15 days', 'country' => 'UAE'],
                            ['title' => 'Robotics Kits for 200 Students', 'category' => 'STEM & Robotics', 'budget' => '$45,000', 'deadline' => '8 days', 'country' => 'Saudi Arabia'],
                            ['title' => 'LMS Platform for University', 'category' => 'Software', 'budget' => '$30,000', 'deadline' => '22 days', 'country' => 'Egypt'],
                        ];
                    @endphp
                    @foreach($demoRfqs as $rfq)
                        <div class="db-list-item">
                            <div class="db-item-details">
                                <p class="db-item-title">{{ $rfq['title'] }}</p>
                                <div class="db-item-meta">
                                    <span class="db-meta-pill">
                                        <x-heroicon-m-tag style="width:12px;height:12px;" />
                                        <span>{{ $rfq['category'] }}</span>
                                    </span>
                                    <span class="db-meta-pill">
                                        <x-heroicon-m-globe-alt style="width:12px;height:12px;" />
                                        <span>{{ $rfq['country'] }}</span>
                                    </span>
                                    <span class="db-meta-pill">
                                        <x-heroicon-m-clock style="width:12px;height:12px;color:#d97706;" />
                                        <span>{{ $rfq['deadline'] }} left</span>
                                    </span>
                                </div>
                            </div>
                            <div class="db-item-action">
                                <span class="db-item-budget">{{ $rfq['budget'] }}</span>
                                <a href="#" class="db-bid-btn">
                                    Bid
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div style="padding:0.75rem 1.25rem;border-top:1px solid #f1f5f9;background:#f8fafc;" class="dark:border-slate-800 dark:bg-slate-900/50">
                    <p style="font-size:0.6875rem;color:#94a3b8;margin:0;">Showing demo data — connect your RFQ engine to display live opportunities.</p>
                </div>
            </div>

            {{-- Sidebar column --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">
                {{-- Quick Links --}}
                <div class="db-card">
                    <div class="db-card-header">
                        <h2 class="db-card-title" style="font-size:0.8125rem;text-transform:uppercase;letter-spacing:0.05em;color:#475569;">Navigation</h2>
                    </div>
                    <div class="db-nav-list">
                        @php
                            $quickLinks = [
                                ['label' => 'RFQ Center',            'icon' => 'heroicon-o-inbox-arrow-down',       'href' => '#', 'color' => 'color:#0d9488;'],
                                ['label' => 'My Quotations',         'icon' => 'heroicon-o-paper-airplane',         'href' => '#', 'color' => 'color:#2563eb;'],
                                ['label' => 'Products & Services',   'icon' => 'heroicon-o-cube',                   'href' => '#', 'color' => 'color:#4f46e5;'],
                                ['label' => 'My Reviews',            'icon' => 'heroicon-o-star',                   'href' => '#', 'color' => 'color:#d97706;'],
                                ['label' => 'Analytics',             'icon' => 'heroicon-o-chart-bar',              'href' => '#', 'color' => 'color:#059669;'],
                                ['label' => 'Messages',              'icon' => 'heroicon-o-chat-bubble-left-right', 'href' => '#', 'color' => 'color:#7c3aed;'],
                                ['label' => 'Subscription',          'icon' => 'heroicon-o-credit-card',            'href' => '#', 'color' => 'color:#e11d48;'],
                                ['label' => 'Support Tickets',       'icon' => 'heroicon-o-lifebuoy',               'href' => '#', 'color' => 'color:#64748b;'],
                            ];
                        @endphp
                        @foreach($quickLinks as $link)
                            <a href="{{ $link['href'] }}" class="db-nav-item">
                                <x-dynamic-component :component="$link['icon']" style="width:20px;height:20px;{{ $link['color'] }}" />
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Profile Strength --}}
                <div class="db-strength-card">
                    <div class="db-strength-header">
                        <x-heroicon-s-shield-check style="width:24px;height:24px;color:#0d9488;" class="flex-shrink-0" />
                        <div style="flex:1;min-width:0;">
                            <p class="db-strength-title">Profile Completeness</p>
                            <div class="db-progress-bar-wrapper">
                                <div class="db-progress-bar" style="width: 40%"></div>
                            </div>
                            <p class="db-strength-sub">40% — Add products and gallery to boost visibility.</p>
                            <a href="#" class="db-strength-link">
                                Complete profile →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Row --}}
        <div class="db-grid-bottom">
            {{-- My Active Bids --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <x-heroicon-s-paper-airplane style="width:20px;height:20px;color:#2563eb;" />
                        <span>My Active Bids</span>
                    </h2>
                    <a href="#" class="db-card-link">View all →</a>
                </div>
                <div class="db-empty-state">
                    <x-heroicon-o-paper-airplane style="width:36px;height:36px;color:#cbd5e1;" />
                    <p class="db-empty-title">No active bids yet</p>
                    <a href="#" class="db-empty-link">Browse RFQs to start bidding →</a>
                </div>
            </div>

            {{-- Recent Reviews --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <x-heroicon-s-star style="width:20px;height:20px;color:#d97706;" />
                        <span>Recent Reviews</span>
                    </h2>
                    <a href="#" class="db-card-link">View all →</a>
                </div>
                <div class="db-empty-state">
                    <x-heroicon-o-star style="width:36px;height:36px;color:#cbd5e1;" />
                    <p class="db-empty-title">No reviews yet</p>
                    <p class="db-empty-sub">Reviews appear after completing awarded projects.</p>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
