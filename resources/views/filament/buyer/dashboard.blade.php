<x-filament-panels::page>

    {{-- ══════════════ CAPABILITY STATE BANNERS ══════════════ --}}

    @if(isset($capabilityStatus) && $capabilityStatus !== 'active')

        {{-- PENDING --}}
        @if($capabilityStatus === 'pending')
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.75rem;padding:1.25rem 1.5rem;display:flex;gap:1rem;align-items:flex-start;margin-bottom:0.5rem;">
            <div style="width:2.5rem;height:2.5rem;background:#dcfce7;border-radius:9999px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:1.25rem;height:1.25rem;color:#16a34a;animation:spin 2s linear infinite;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div style="flex:1;">
                <p style="font-weight:600;font-size:0.9rem;color:#15803d;margin:0 0 0.25rem;">Application Under Review</p>
                <p style="font-size:0.8rem;color:#166534;margin:0;">Your Buyer application has been received and is being reviewed by our team. We typically respond within <strong>1–3 business days</strong>. You'll receive an email once reviewed.</p>
                <div style="display:flex;gap:1.5rem;margin-top:0.75rem;">
                    <div style="text-align:center;background:#dcfce7;border-radius:0.5rem;padding:0.5rem 1rem;font-size:0.75rem;">
                        <div style="font-size:1.25rem;">📋</div>
                        <div style="font-weight:600;color:#166534;">Submitted</div>
                    </div>
                    <div style="text-align:center;background:#dcfce7;border-radius:0.5rem;padding:0.5rem 1rem;font-size:0.75rem;">
                        <div style="font-size:1.25rem;">🔍</div>
                        <div style="font-weight:600;color:#166534;">In Review</div>
                    </div>
                    <div style="text-align:center;background:#f0fdf4;border:1px dashed #bbf7d0;border-radius:0.5rem;padding:0.5rem 1rem;font-size:0.75rem;">
                        <div style="font-size:1.25rem;">✅</div>
                        <div style="color:#86efac;">Approval</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- REVISION REQUIRED --}}
        @elseif($capabilityStatus === 'revision_required')
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:0.5rem;">
            <div style="display:flex;gap:1rem;align-items:flex-start;">
                <div style="width:2.5rem;height:2.5rem;background:#fef3c7;border-radius:9999px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:1.25rem;height:1.25rem;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.502 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div style="flex:1;">
                    <p style="font-weight:600;font-size:0.9rem;color:#b45309;margin:0 0 0.25rem;">Changes Required — Attempt {{ $applicationAttempts ?? 1 }}</p>
                    @if($revisionReason ?? null)
                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:0.5rem;padding:0.75rem;margin:0.5rem 0;">
                        <p style="font-size:0.75rem;font-weight:600;color:#92400e;margin:0 0 0.25rem;">Reviewer notes:</p>
                        <p style="font-size:0.8rem;color:#78350f;margin:0;">{{ $revisionReason }}</p>
                    </div>
                    @endif
                    @if($canResubmit ?? false)
                    <a href="{{ route('buyer.onboarding.profile') }}"
                        style="display:inline-flex;align-items:center;gap:0.5rem;background:#d97706;color:#fff;padding:0.5rem 1.25rem;border-radius:0.5rem;font-size:0.8rem;font-weight:600;text-decoration:none;margin-top:0.5rem;">
                        Update Application →
                    </a>
                    @else
                    <p style="font-size:0.75rem;color:#92400e;margin-top:0.5rem;">Maximum attempts reached. Please contact support.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- REJECTED --}}
        @elseif($capabilityStatus === 'rejected')
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:0.5rem;">
            <div style="display:flex;gap:1rem;align-items:flex-start;">
                <div style="width:2.5rem;height:2.5rem;background:#fee2e2;border-radius:9999px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:1.25rem;height:1.25rem;color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div style="flex:1;">
                    <p style="font-weight:600;font-size:0.9rem;color:#b91c1c;margin:0 0 0.25rem;">Application Not Approved</p>
                    @if($rejectionReason ?? null)
                    <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:0.5rem;padding:0.75rem;margin:0.5rem 0;">
                        <p style="font-size:0.75rem;font-weight:600;color:#991b1b;margin:0 0 0.25rem;">Reason:</p>
                        <p style="font-size:0.8rem;color:#7f1d1d;margin:0;">{{ $rejectionReason }}</p>
                    </div>
                    @endif
                    <p style="font-size:0.75rem;color:#991b1b;margin-top:0.5rem;">
                        If you believe this is an error, please
                        <a href="mailto:support@edushopify.com" style="color:#dc2626;font-weight:600;">contact support</a>.
                    </p>
                </div>
            </div>
        </div>
        @endif

    @endif
    {{-- ══════════════ END STATE BANNERS ══════════════ --}}


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
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
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
        color: #ddd6fe;
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
        color: #6d28d9;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .db-banner-btn-white:hover {
        background: #f5f3ff;
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
        color: #7c3aed;
        text-decoration: none;
    }
    .dark .db-card-link {
        color: #a78bfa;
    }
    .db-card-link:hover {
        text-decoration: underline;
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
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 0.75rem;
        padding: 1.25rem;
    }
    .dark .db-strength-card {
        background: rgba(124, 58, 237, 0.05);
        border-color: rgba(124, 58, 237, 0.15);
    }
    .db-strength-header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .db-strength-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #6d28d9;
        margin: 0;
    }
    .dark .db-strength-title {
        color: #a78bfa;
    }
    .db-strength-sub {
        font-size: 0.75rem;
        color: #6d28d9;
        margin: 0.375rem 0 0;
    }
    .dark .db-strength-sub {
        color: #94a3b8;
    }
    .db-strength-link {
        font-size: 0.75rem;
        font-weight: 700;
        color: #7c3aed;
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
    .db-empty-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        background: #f8fafc;
        color: #94a3b8;
        margin-bottom: 0.5rem;
    }
    .dark .db-empty-icon-wrapper {
        background: #1e293b;
        color: #475569;
    }
    .db-empty-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #475569;
        margin: 0;
    }
    .dark .db-empty-title {
        color: #cbd5e1;
    }
    .db-empty-sub {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 0;
        max-width: 280px;
        line-height: 1.4;
    }
    .db-empty-btn {
        background: #7c3aed;
        color: #ffffff;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 700;
        text-decoration: none;
        margin-top: 0.75rem;
        transition: background 0.15s;
    }
    .db-empty-btn:hover {
        background: #6d28d9;
    }
    .db-empty-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #7c3aed;
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
                    <p style="font-size:0.875rem;font-weight:500;color:#ddd6fe;margin:0;">Welcome back,</p>
                    <h1 class="db-banner-title" style="margin-top:0.25rem;">{{ $userName }}</h1>
                    <p class="db-banner-sub">Manage your procurement requests and supplier relationships.</p>
                </div>
                <div class="db-banner-actions">
                    <a href="#" class="db-banner-btn-white">
                        <x-heroicon-s-plus-circle style="width:16px;height:16px;" />
                        <span>Post an RFQ</span>
                    </a>
                    <a href="#" class="db-banner-btn-trans">
                        <x-heroicon-s-magnifying-glass style="width:16px;height:16px;" />
                        <span>Browse Suppliers</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI Stats --}}
        <div class="db-stats-grid">
            @php
                $stats = [
                    ['label' => 'Active RFQs',       'value' => '0', 'icon' => 'heroicon-s-document-text',  'color' => 'color:#7c3aed;',  'bg' => 'background:#f5f3ff;'],
                    ['label' => 'Received Quotes',   'value' => '0', 'icon' => 'heroicon-s-inbox-arrow-down','color' => 'color:#2563eb;',    'bg' => 'background:#eff6ff;'],
                    ['label' => 'Saved Suppliers',   'value' => '0', 'icon' => 'heroicon-s-heart',           'color' => 'color:#e11d48;',    'bg' => 'background:#fff1f2;'],
                    ['label' => 'Awarded Projects',  'value' => '0', 'icon' => 'heroicon-s-trophy',          'color' => 'color:#d97706;',   'bg' => 'background:#fffbeb;'],
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

            {{-- My RFQs --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <x-heroicon-s-document-text style="width:20px;height:20px;color:#7c3aed;" />
                        <span>My RFQs</span>
                    </h2>
                    <a href="#" class="db-card-link">View all →</a>
                </div>
                <div class="db-empty-state" style="padding:4rem 0;">
                    <div class="db-empty-icon-wrapper">
                        <x-heroicon-o-document-text style="width:24px;height:24px;" />
                    </div>
                    <p class="db-empty-title">No RFQs posted yet</p>
                    <p class="db-empty-sub">Post your first Request for Quotation to start receiving bids from suppliers.</p>
                    <a href="#" class="db-empty-btn">
                        Post RFQ
                    </a>
                </div>
            </div>

            {{-- Sidebar Column --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">
                {{-- Quick Actions --}}
                <div class="db-card">
                    <div class="db-card-header">
                        <h2 class="db-card-title" style="font-size:0.8125rem;text-transform:uppercase;letter-spacing:0.05em;color:#475569;">Quick Actions</h2>
                    </div>
                    <div class="db-nav-list">
                        @php
                            $quickLinks = [
                                ['label' => 'Post New RFQ',         'icon' => 'heroicon-o-plus-circle',           'href' => '#', 'color' => 'color:#7c3aed;'],
                                ['label' => 'View Received Quotes',  'icon' => 'heroicon-o-inbox-arrow-down',      'href' => '#', 'color' => 'color:#2563eb;'],
                                ['label' => 'Compare Quotes',        'icon' => 'heroicon-o-scale',                 'href' => '#', 'color' => 'color:#4f46e5;'],
                                ['label' => 'Saved Suppliers',       'icon' => 'heroicon-o-heart',                 'href' => '#', 'color' => 'color:#e11d48;'],
                                ['label' => 'Saved Products',        'icon' => 'heroicon-o-bookmark',              'href' => '#', 'color' => 'color:#d97706;'],
                                ['label' => 'Messages',              'icon' => 'heroicon-o-chat-bubble-left-right','href' => '#', 'color' => 'color:#0d9488;'],
                                ['label' => 'Support Tickets',       'icon' => 'heroicon-o-lifebuoy',              'href' => '#', 'color' => 'color:#64748b;'],
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

                {{-- Profile Completion --}}
                <div class="db-strength-card">
                    <div class="db-strength-header">
                        <x-heroicon-s-user-circle style="width:24px;height:24px;color:#7c3aed;" class="flex-shrink-0" />
                        <div style="flex:1;min-width:0;">
                            <p class="db-strength-title">Complete your profile</p>
                            <p class="db-strength-sub" style="margin-top:0.25rem;">A complete profile helps suppliers respond to your RFQs faster.</p>
                            <a href="#" class="db-strength-link">
                                Update profile →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Row --}}
        <div class="db-grid-bottom">
            {{-- Recent Quotes --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <x-heroicon-s-inbox-arrow-down style="width:20px;height:20px;color:#2563eb;" />
                        <span>Recent Quotes</span>
                    </h2>
                    <a href="#" class="db-card-link">View all →</a>
                </div>
                <div class="db-empty-state">
                    <x-heroicon-o-inbox style="width:36px;height:36px;color:#cbd5e1;" />
                    <p class="db-empty-title">No quotes received yet</p>
                </div>
            </div>

            {{-- Awarded Projects --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <x-heroicon-s-trophy style="width:20px;height:20px;color:#d97706;" />
                        <span>Awarded Projects</span>
                    </h2>
                    <a href="#" class="db-card-link">View all →</a>
                </div>
                <div class="db-empty-state">
                    <x-heroicon-o-trophy style="width:36px;height:36px;color:#cbd5e1;" />
                    <p class="db-empty-title">No awarded projects yet</p>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
