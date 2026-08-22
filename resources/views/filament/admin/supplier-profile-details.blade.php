<style>
.sp-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    font-family: inherit;
}

.sp-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
@media (min-width: 1024px) {
    .sp-grid {
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
    }
}

/* Cover & Hero Header Card */
.sp-banner {
    position: relative;
    height: 180px;
    width: 100%;
    background: #0f172a;
    overflow: hidden;
}
@media (min-width: 640px) {
    .sp-banner {
        height: 220px;
    }
}
.sp-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sp-status-pill {
    position: absolute;
    right: 1rem;
    top: 1rem;
    z-index: 10;
}
.sp-hero-info {
    position: relative;
    padding: 0 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
@media (min-width: 640px) {
    .sp-hero-info {
        flex-direction: row;
        align-items: flex-end;
        gap: 1.5rem;
    }
}
.sp-logo-wrapper {
    position: relative;
    margin-top: -3.5rem; /* overlaps banner */
    width: 96px;
    height: 96px;
    border-radius: 0.5rem;
    border: 3px solid #ffffff;
    background: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
}
@media (min-width: 640px) {
    .sp-logo-wrapper {
        margin-top: -4.5rem;
        width: 112px;
        height: 112px;
    }
}
.dark .sp-logo-wrapper {
    border-color: #0f172a;
    background: #1e293b;
}
.sp-logo-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sp-hero-text {
    flex: 1;
    min-width: 0;
}
.sp-company-name {
    font-size: 1.375rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
@media (min-width: 640px) {
    .sp-company-name {
        font-size: 1.625rem;
    }
}
.dark .sp-company-name {
    color: #ffffff;
}
.sp-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    margin-top: 0.5rem;
    font-size: 0.8125rem;
    color: #64748b;
}
.dark .sp-meta-row {
    color: #94a3b8;
}
.sp-meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 500;
}

/* Card Styling: Premium Vercel/Stripe style */
.sp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.dark .sp-card {
    background: #0f172a;
    border-color: #1e293b;
}
.sp-card:last-child {
    margin-bottom: 0;
}

.sp-card-header {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}
.dark .sp-card-header {
    border-color: #1e293b;
}
.sp-card-header h3 {
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
    margin: 0;
}
.dark .sp-card-header h3 {
    color: #94a3b8;
}
.sp-card-body {
    padding: 1.25rem;
}

/* Details list (Stripe-like key-value grid) */
.sp-info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 640px) {
    .sp-info-grid {
        grid-template-columns: 1fr 1fr;
    }
}
.sp-field {
    padding-bottom: 0.75rem;
    border-bottom: 1px dashed #f1f5f9;
}
.dark .sp-field {
    border-color: #1e293b;
}
.sp-field.col-span-2 {
    grid-column: span 1;
}
@media (min-width: 640px) {
    .sp-field.col-span-2 {
        grid-column: span 2;
    }
}
.sp-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    display: block;
}
.sp-value {
    margin-top: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #1e293b;
    word-break: break-word;
}
.dark .sp-value {
    color: #cbd5e1;
}
.sp-value a {
    color: #4f46e5;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.dark .sp-value a {
    color: #818cf8;
}
.sp-value a:hover {
    text-decoration: underline;
}

/* Document cards */
.sp-doc-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.sp-doc-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
}
.dark .sp-doc-item {
    background: rgba(30, 41, 59, 0.3);
    border-color: rgba(51, 65, 85, 0.5);
}
.sp-doc-item:hover {
    border-color: #cbd5e1;
    background: #f1f5f9;
}
.dark .sp-doc-item:hover {
    border-color: #475569;
    background: rgba(30, 41, 59, 0.6);
}
.sp-doc-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
    flex: 1;
}
.sp-doc-icon {
    color: #64748b;
    flex-shrink: 0;
}
.sp-doc-text {
    min-width: 0;
    flex: 1;
}
.sp-doc-title {
    font-size: 0.8125rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dark .sp-doc-title {
    color: #f1f5f9;
}
.sp-doc-sub {
    font-size: 0.6875rem;
    color: #94a3b8;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sp-doc-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

/* Photo Gallery styling */
.sp-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
}
.sp-gallery-img {
    aspect-ratio: 1/1;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.dark .sp-gallery-img {
    border-color: #1e293b;
}
.sp-gallery-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.sp-gallery-img:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

/* Videos */
.sp-video-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
@media (min-width: 640px) {
    .sp-video-grid {
        grid-template-columns: 1fr 1fr;
    }
}
.sp-video-item {
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.dark .sp-video-item {
    border-color: #1e293b;
    background: #0f172a;
}
.sp-video-frame {
    aspect-ratio: 16/9;
    width: 100%;
    display: block;
}
.sp-video-title {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dark .sp-video-title {
    color: #cbd5e1;
}

/* Status pill & Badges */
.sp-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.125rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.725rem;
    font-weight: 600;
    line-height: 1.25rem;
    border: 1px solid transparent;
}
.sp-badge-verified {
    background: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}
.dark .sp-badge-verified {
    background: rgba(16, 185, 129, 0.1);
    color: #34d399;
    border-color: rgba(52, 211, 153, 0.2);
}
.sp-badge-unverified {
    background: #fef2f2;
    color: #b91c1c;
    border-color: #fecaca;
}
.dark .sp-badge-unverified {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border-color: rgba(248, 113, 113, 0.2);
}

/* Branded Social Buttons */
.sp-social-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.sp-social-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
}
.dark .sp-social-btn {
    border-color: #1e293b;
    background: rgba(30, 41, 59, 0.5);
    color: #cbd5e1;
}
.sp-social-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.dark .sp-social-btn:hover {
    background: #1e293b;
    border-color: #475569;
}

/* Tags */
.sp-tag-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.sp-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 0.375rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: 0.75rem;
    font-weight: 600;
}
.dark .sp-tag {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
.sp-tag-indigo {
    background: #eeebff;
    border-color: #d9d4ff;
    color: #4c3eac;
}
.dark .sp-tag-indigo {
    background: rgba(79, 70, 229, 0.1);
    border-color: rgba(99, 102, 241, 0.2);
    color: #a5b4fc;
}

/* Right Sidebar specific */
.sp-status-card {
    position: relative;
}
.sp-status-bar {
    height: 4px;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
}
.bg-status-pending { background: #f59e0b; }
.bg-status-approved { background: #10b981; }
.bg-status-rejected { background: #ef4444; }
.bg-status-revision { background: #f97316; }
.bg-status-draft { background: #64748b; }

.sp-sidebar-list {
    display: flex;
    flex-direction: column;
}
.sp-sidebar-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.8125rem;
}
.dark .sp-sidebar-row {
    border-color: #1e293b;
}
.sp-sidebar-row:last-child {
    border-bottom: none;
}
.sp-sidebar-label {
    color: #64748b;
    font-weight: 505;
}
.dark .sp-sidebar-label {
    color: #94a3b8;
}
.sp-sidebar-value {
    color: #0f172a;
    font-weight: 600;
}
.dark .sp-sidebar-value {
    color: #f1f5f9;
}

/* Rep photo */
.sp-rep-photo {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    margin: 0 auto;
}
.dark .sp-rep-photo {
    border-color: #1e293b;
}
.sp-rep-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Business Hours */
.sp-hours-list {
    display: flex;
    flex-direction: column;
}
.sp-hours-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8fafc;
    font-size: 0.8125rem;
}
.dark .sp-hours-row {
    border-color: #1e293b;
}
.sp-hours-row:last-child {
    border-bottom: none;
}
.sp-hours-day {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #475569;
}
.dark .sp-hours-day {
    color: #cbd5e1;
}
.sp-hours-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.sp-hours-dot.open { background: #10b981; }
.sp-hours-dot.closed { background: #ef4444; }
.sp-hours-time {
    font-weight: 600;
    color: #0f172a;
}
.dark .sp-hours-time {
    color: #f1f5f9;
}
.sp-hours-closed-badge {
    font-size: 0.6875rem;
    font-weight: 700;
    color: #ef4444;
    text-transform: uppercase;
}
</style>

@php
    $record  = $getRecord();
    $user    = $record->user;
    $days    = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    $statusMap = [
        'pending'  => ['label' => 'Pending Review',  'bg' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-300 border-amber-100 dark:border-amber-900/30',   'icon' => 'heroicon-s-clock'],
        'approved' => ['label' => 'Approved',         'bg' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-300 border-emerald-100 dark:border-emerald-900/30','icon' => 'heroicon-s-check-circle'],
        'rejected' => ['label' => 'Rejected',         'bg' => 'bg-red-50 text-red-750 dark:bg-red-950/20 dark:text-red-300 border-red-100 dark:border-red-900/30',          'icon' => 'heroicon-s-x-circle'],
        'revision' => ['label' => 'Needs Revision',   'bg' => 'bg-orange-50 text-orange-705 dark:bg-orange-950/20 dark:text-orange-300 border-orange-100 dark:border-orange-900/30','icon' => 'heroicon-s-exclamation-triangle'],
        'draft'    => ['label' => 'Draft',            'bg' => 'bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',         'icon' => 'heroicon-s-pencil'],
    ];
    $st = $statusMap[$record->review_status] ?? $statusMap['pending'];
@endphp

<div class="sp-container">

    {{-- ── Cover & Profile Header Card ── --}}
    <div class="sp-card">
        <div class="sp-banner">
            @if($record->banner)
                <img src="{{ Storage::url($record->banner) }}" alt="Banner">
            @else
                <div style="width:100%;height:100%;background:linear-gradient(135deg,#1e293b,#0f172a);"></div>
            @endif

            <div class="sp-status-pill">
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $st['bg'] }}">
                    <x-filament::icon :icon="$st['icon']" class="text-current" style="width:12px;height:12px;" />
                    {{ $st['label'] }}
                </span>
            </div>
        </div>

        <div class="sp-hero-info">
            <div class="sp-logo-wrapper">
                @if($record->logo)
                    <img src="{{ Storage::url($record->logo) }}" alt="Logo">
                @else
                    <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;background:#f1f5f9;">
                        <x-filament::icon icon="heroicon-o-building-storefront" class="text-slate-350" style="width:24px;height:24px;" />
                    </div>
                @endif
            </div>

            <div class="sp-hero-text">
                <div class="sp-company-name">
                    <span>{{ $record->company_name }}</span>
                    @if($record->review_status === 'approved')
                        <x-filament::icon icon="heroicon-s-check-circle" class="text-emerald-500" style="width:20px;height:20px;" />
                    @endif
                </div>

                <div class="sp-meta-row">
                    @if($record->company_type)
                        <div class="sp-meta-item">
                            <x-filament::icon icon="heroicon-o-briefcase" style="width:14px;height:14px;" />
                            <span>{{ $record->company_type }}</span>
                        </div>
                    @endif
                    @if($record->founded_year)
                        <div class="sp-meta-item">
                            <x-filament::icon icon="heroicon-o-calendar" style="width:14px;height:14px;" />
                            <span>Founded {{ $record->founded_year }}</span>
                        </div>
                    @endif
                    @if($record->employees)
                        <div class="sp-meta-item">
                            <x-filament::icon icon="heroicon-o-users" style="width:14px;height:14px;" />
                            <span>{{ $record->employees }} Employees</span>
                        </div>
                    @endif
                    @if($record->country)
                        <div class="sp-meta-item">
                            <x-filament::icon icon="heroicon-o-globe-alt" style="width:14px;height:14px;" />
                            <span>{{ $record->city?->name ? $record->city->name . ', ' : '' }}{{ $record->country->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Rejection / Revision Alert Bar ── --}}
    @if($record->review_reason && in_array($record->review_status, ['rejected', 'revision']))
        @php $isRejected = $record->review_status === 'rejected'; @endphp
        <div class="rounded-lg border p-4 {{ $isRejected ? 'border-red-200 bg-red-50/40 dark:border-red-950/20 dark:bg-red-950/10' : 'border-orange-200 bg-orange-50/40 dark:border-orange-950/20 dark:bg-orange-950/10' }}">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-s-exclamation-triangle" class="w-5 h-5 {{ $isRejected ? 'text-red-650' : 'text-orange-550' }} mt-0.5 flex-shrink-0" />
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider {{ $isRejected ? 'text-red-800 dark:text-red-300' : 'text-orange-850 dark:text-orange-300' }}">
                        {{ $isRejected ? 'Rejection Reason' : 'Revision Required Note' }}
                    </h4>
                    <p class="mt-1 text-sm leading-relaxed {{ $isRejected ? 'text-red-750 dark:text-red-400' : 'text-orange-750 dark:text-orange-400' }}">
                        {{ $record->review_reason }}
                    </p>
                    @if($record->reviewed_at)
                        <p class="mt-2 text-[10px] font-semibold {{ $isRejected ? 'text-red-500/70' : 'text-orange-500/70' }}">
                            Reviewed by {{ $record->reviewedBy?->name ?? 'Admin' }} on {{ $record->reviewed_at->format('d M Y, h:i A') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── Two Column Layout ── --}}
    <div class="sp-grid">

        {{-- ══ Left Column (Main Details) ══ --}}
        <div>

            {{-- Account Information --}}
            <div class="sp-card">
                <div class="sp-card-header">
                    <x-filament::icon icon="heroicon-o-user" class="text-slate-400" style="width:16px;height:16px;" />
                    <h3>Account Information</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-info-grid">
                        <div class="sp-field">
                            <span class="sp-label">Full Name</span>
                            <div class="sp-value">{{ $user?->name ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Email Address</span>
                            <div class="sp-value" style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                <span>{{ $user?->email ?? '—' }}</span>
                                @if($user?->email_verified_at)
                                    <span class="sp-badge sp-badge-verified">Verified</span>
                                @else
                                    <span class="sp-badge sp-badge-unverified">Unverified</span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Phone Number</span>
                            <div class="sp-value" style="display:flex;align-items:center;gap:0.5rem;">
                                <span>{{ $user?->phone ?? '—' }}</span>
                                @if($user?->phone_verified_at)
                                    <span class="sp-badge sp-badge-verified">Verified</span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Registration Date</span>
                            <div class="sp-value">{{ $user?->created_at?->format('d M Y, h:i A') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Company Profile --}}
            <div class="sp-card">
                <div class="sp-card-header">
                    <x-filament::icon icon="heroicon-o-building-office" class="text-slate-400" style="width:16px;height:16px;" />
                    <h3>Company Profile</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-info-grid">
                        <div class="sp-field col-span-2">
                            <span class="sp-label">Registered Company Name</span>
                            <div class="sp-value" style="font-weight:700;font-size:0.9375rem;">{{ $record->company_name ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Business Structure</span>
                            <div class="sp-value">{{ $record->company_type ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Founded Year</span>
                            <div class="sp-value">{{ $record->founded_year ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Employee Count</span>
                            <div class="sp-value">{{ $record->employees ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Official Website</span>
                            <div class="sp-value">
                                @if($record->website)
                                    <a href="{{ $record->website }}" target="_blank">
                                        <span>{{ $record->website }}</span>
                                        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" style="width:12px;height:12px;" />
                                    </a>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-field col-span-2">
                            <span class="sp-label">Physical Address</span>
                            <div class="sp-value">{{ collect([$record->address, $record->city?->name, $record->country?->name])->filter()->join(', ') ?: '—' }}</div>
                        </div>
                    </div>

                    @if($record->description)
                        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;" class="dark:border-slate-800">
                            <span class="sp-label" style="margin-bottom:0.5rem;">About Company</span>
                            <p style="font-size:0.875rem;line-height:1.6;color:#475569;margin:0;" class="dark:text-slate-400">
                                {!! nl2br(e(is_array($record->description) ? ($record->description['en'] ?? '') : $record->description)) !!}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Contact Details --}}
            <div class="sp-card">
                <div class="sp-card-header">
                    <x-filament::icon icon="heroicon-o-phone" class="text-slate-400" style="width:16px;height:16px;" />
                    <h3>Contact Details</h3>
                </div>
                <div class="sp-card-body">
                    <div class="sp-info-grid" style="margin-bottom:1.25rem;">
                        <div class="sp-field">
                            <span class="sp-label">Contact Person</span>
                            <div class="sp-value">{{ $record->contact_person ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Contact Email</span>
                            <div class="sp-value">
                                @if($record->contact_email)
                                    <a href="mailto:{{ $record->contact_email }}">
                                        <span>{{ $record->contact_email }}</span>
                                        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" style="width:12px;height:12px;" />
                                    </a>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">Contact Phone</span>
                            <div class="sp-value">{{ $record->contact_phone ?? '—' }}</div>
                        </div>

                        <div class="sp-field">
                            <span class="sp-label">WhatsApp Contact</span>
                            <div class="sp-value">{{ $record->whatsapp ?? '—' }}</div>
                        </div>

                        <div class="sp-field col-span-2">
                            <span class="sp-label">Customer Support Email</span>
                            <div class="sp-value">
                                @if($record->support_email)
                                    <a href="mailto:{{ $record->support_email }}">
                                        <span>{{ $record->support_email }}</span>
                                        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" style="width:12px;height:12px;" />
                                    </a>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($record->socials && is_array($record->socials) && count(array_filter($record->socials)) > 0)
                        <div style="padding-top:1rem;border-top:1px solid #f1f5f9;" class="dark:border-slate-800">
                            <span class="sp-label">Social Platforms</span>
                            <div class="sp-social-list">
                                @foreach(array_filter($record->socials) as $platform => $url)
                                    <a href="{{ $url }}" target="_blank" class="sp-social-btn">
                                        <x-filament::icon icon="heroicon-o-link" style="width:12px;height:12px;" />
                                        <span>{{ ucfirst($platform) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Business Classification --}}
            @if(($record->supplierTypes && $record->supplierTypes->count() > 0) || ($record->exhibitions && $record->exhibitions->count() > 0))
                <div class="sp-card">
                    <div class="sp-card-header">
                        <x-filament::icon icon="heroicon-o-tag" class="text-slate-400" style="width:16px;height:16px;" />
                        <h3>Business Classification</h3>
                    </div>
                    <div class="sp-card-body">
                        @if($record->supplierTypes && $record->supplierTypes->count() > 0)
                            <div style="margin-bottom:1.25rem;">
                                <span class="sp-label">Supplier Categories</span>
                                <div class="sp-tag-list">
                                    @foreach($record->supplierTypes as $type)
                                        <span class="sp-tag">
                                            {{ is_array($type->name) ? ($type->name['en'] ?? '') : $type->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($record->exhibitions && $record->exhibitions->count() > 0)
                            <div>
                                <span class="sp-label">Exhibition Participations</span>
                                <div class="sp-tag-list">
                                    @foreach($record->exhibitions as $ex)
                                        <span class="sp-tag sp-tag-indigo">
                                            {{ is_array($ex->name) ? ($ex->name['en'] ?? '') : $ex->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Documents & Verification --}}
            <div class="sp-card">
                <div class="sp-card-header" style="justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:0.5rem;">
                        <x-filament::icon icon="heroicon-o-document-text" class="text-slate-400" style="width:16px;height:16px;" />
                        <h3>Documents & Verification</h3>
                    </div>
                    @if($record->documents && $record->documents->count() > 0)
                        <span style="font-size:0.75rem;font-weight:700;color:#94a3b8;">
                            {{ $record->documents->count() }} Attached
                        </span>
                    @endif
                </div>
                <div class="sp-card-body">
                    @if($record->documents && $record->documents->count() > 0)
                        <div class="sp-doc-list">
                            @foreach($record->documents as $doc)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="sp-doc-item">
                                    <div class="sp-doc-info">
                                        <x-filament::icon icon="heroicon-o-document-text" class="sp-doc-icon" style="width:20px;height:20px;" />
                                        <div class="sp-doc-text">
                                            <p class="sp-doc-title">
                                                {{ is_array($doc->documentType?->name) ? ($doc->documentType->name['en'] ?? 'Document') : ($doc->documentType?->name ?? 'Document') }}
                                            </p>
                                            <p class="sp-doc-sub">{{ $doc->original_name ?? 'Attachment file' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="sp-doc-meta">
                                        @php $docStatus = $doc->status ?? 'pending'; @endphp
                                        @if($docStatus === 'verified')
                                            <span class="sp-badge sp-badge-verified">Verified</span>
                                        @elseif($docStatus === 'rejected')
                                            <span class="sp-badge sp-badge-unverified">Rejected</span>
                                        @else
                                            <span class="sp-badge" style="background:#fef3c7;color:#d97706;border-color:#fde68a;">Pending</span>
                                        @endif
                                        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="text-slate-400" style="width:14px;height:14px;" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.5rem;padding:2rem 0;text-align:center;">
                            <x-filament::icon icon="heroicon-o-document-text" class="text-slate-300" style="width:32px;height:32px;" />
                            <p style="font-size:0.8125rem;font-weight:600;color:#94a3b8;margin:0;">No verification documents submitted</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Photo Gallery --}}
            @if($record->gallery && $record->gallery->count() > 0)
                <div class="sp-card">
                    <div class="sp-card-header" style="justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <x-filament::icon icon="heroicon-o-photo" class="text-slate-400" style="width:16px;height:16px;" />
                            <h3>Photo Gallery</h3>
                        </div>
                        <span style="font-size:0.75rem;font-weight:700;color:#94a3b8;">
                            {{ $record->gallery->count() }} Images
                        </span>
                    </div>
                    <div class="sp-card-body">
                        <div class="sp-gallery-grid">
                            @foreach($record->gallery as $image)
                                <a href="{{ Storage::url($image->image_path) }}" target="_blank" class="sp-gallery-img">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="Gallery Image">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Corporate Videos --}}
            @if($record->videos && $record->videos->count() > 0)
                <div class="sp-card">
                    <div class="sp-card-header">
                        <x-filament::icon icon="heroicon-o-video-camera" class="text-slate-400" style="width:16px;height:16px;" />
                        <h3>Corporate Videos</h3>
                    </div>
                    <div class="sp-card-body">
                        <div class="sp-video-grid">
                            @foreach($record->videos as $video)
                                <div class="sp-video-item">
                                    <iframe class="sp-video-frame"
                                        src="https://www.youtube.com/embed/{{ $video->youtube_id }}"
                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                                    </iframe>
                                    @if($video->title)
                                        <p class="sp-video-title">{{ $video->title }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ══ Right Column (Sidebar Panels) ══ --}}
        <div>

            {{-- Widget 1: Application Status --}}
            <div class="sp-card sp-status-card">
                @php
                    $statusBarClass = [
                        'pending' => 'bg-status-pending',
                        'approved' => 'bg-status-approved',
                        'rejected' => 'bg-status-rejected',
                        'revision' => 'bg-status-revision',
                        'draft' => 'bg-status-draft',
                    ][$record->review_status] ?? 'bg-status-draft';
                @endphp
                <div class="sp-status-bar {{ $statusBarClass }}"></div>

                <div class="sp-card-header" style="margin-top:4px;">
                    <x-filament::icon icon="heroicon-o-clipboard-document-check" class="text-slate-400" style="width:16px;height:16px;" />
                    <h3>Application Status</h3>
                </div>
                <div class="sp-card-body" style="padding-top:0.5rem;padding-bottom:0.5rem;">
                    <div class="sp-sidebar-list">
                        <div class="sp-sidebar-row">
                            <span class="sp-sidebar-label">Review Status</span>
                            <span class="inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs font-bold border {{ $st['bg'] }}">
                                <x-filament::icon :icon="$st['icon']" class="text-current" style="width:12px;height:12px;" />
                                {{ $st['label'] }}
                            </span>
                        </div>

                        <div class="sp-sidebar-row">
                            <span class="sp-sidebar-label">Assigned Plan</span>
                            @php
                                $planVal = strtolower($record->plan_status ?? 'none');
                                $planStyles = [
                                    'free' => 'background:#ecfdf5;color:#047857;border-color:#a7f3d0;',
                                    'basic' => 'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;',
                                    'professional' => 'background:#fffbeb;color:#b45309;border-color:#fde68a;',
                                    'featured' => 'background:#fff1f2;color:#be123c;border-color:#fecdd3;',
                                    'none' => 'background:#f8fafc;color:#475569;border-color:#e2e8f0;',
                                ];
                                $planStyle = $planStyles[$planVal] ?? $planStyles['none'];
                            @endphp
                            <span class="sp-badge" style="{{ $planStyle }}">
                                {{ ucfirst($planVal) }}
                            </span>
                        </div>

                        @if($record->plan_expires_at)
                            <div class="sp-sidebar-row">
                                <span class="sp-sidebar-label">Plan Expiry</span>
                                <span class="sp-sidebar-value" style="{{ $record->plan_expires_at->isPast() ? 'color:#b91c1c;' : '' }}">
                                    {{ $record->plan_expires_at->format('d M Y') }}
                                </span>
                            </div>
                        @endif

                        <div class="sp-sidebar-row">
                            <span class="sp-sidebar-label">Date Submitted</span>
                            <span class="sp-sidebar-value">{{ $record->created_at?->format('d M Y') ?? '—' }}</span>
                        </div>

                        @if($record->reviewed_at)
                            <div class="sp-sidebar-row">
                                <span class="sp-sidebar-label">Reviewed On</span>
                                <span class="sp-sidebar-value">{{ $record->reviewed_at->format('d M Y') }}</span>
                            </div>
                        @endif

                        @if($record->reviewedBy)
                            <div class="sp-sidebar-row">
                                <span class="sp-sidebar-label">Reviewed By</span>
                                <span class="sp-sidebar-value">{{ $record->reviewedBy->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Widget 2: Representative Photo --}}
            @if($record->profile_photo)
                <div class="sp-card">
                    <div class="sp-card-header">
                        <x-filament::icon icon="heroicon-o-user-circle" class="text-slate-400" style="width:16px;height:16px;" />
                        <h3>Representative Photo</h3>
                    </div>
                    <div class="sp-card-body" style="background:#f8fafc;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem 0;" class="dark:bg-slate-900/10">
                        <div class="sp-rep-photo">
                            <img src="{{ Storage::url($record->profile_photo) }}" alt="Representative">
                        </div>
                        <p style="font-size:0.6875rem;font-weight:700;color:#94a3b8;margin-top:0.75rem;margin-bottom:0;">Representative Account Image</p>
                    </div>
                </div>
            @endif

            {{-- Widget 3: Business Hours --}}
            @if($record->businessHours && $record->businessHours->count() > 0)
                <div class="sp-card">
                    <div class="sp-card-header">
                        <x-filament::icon icon="heroicon-o-clock" class="text-slate-400" style="width:16px;height:16px;" />
                        <h3>Business Hours</h3>
                    </div>
                    <div class="sp-card-body">
                        <div class="sp-hours-list">
                            @foreach(collect($record->businessHours)->sortBy('day_of_week') as $hour)
                                <div class="sp-hours-row">
                                    <div class="sp-hours-day">
                                        <span class="sp-hours-dot {{ $hour->is_open ? 'open' : 'closed' }}"></span>
                                        <span>{{ $days[$hour->day_of_week] ?? '—' }}</span>
                                    </div>
                                    @if($hour->is_open)
                                        <span class="sp-hours-time">
                                            {{ \Carbon\Carbon::parse($hour->open_time)->format('h:i A') }}
                                            – {{ \Carbon\Carbon::parse($hour->close_time)->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="sp-hours-closed-badge">Closed</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
