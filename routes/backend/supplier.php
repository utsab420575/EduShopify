<?php

use App\Http\Controllers\Backend\Supplier\DashboardController;
use App\Http\Middleware\EnsureSupplierHasPlan;
use App\Http\Middleware\RequireSupplierCapability;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Supplier Dashboard Routes
|--------------------------------------------------------------------------
|
| All routes here are backend, portal-scoped to Supplier per ARCHITECTURE.md.
| The dashboard landing route is deliberately outside RequireSupplierCapability
| so a supplier whose capability is pending/rejected/revision_required still
| lands on a status page instead of a bare 403.
|
| Supplier onboarding wizard routes remain in web.php (supplier.pending and
| supplier.onboarding.rejected both redirect here; supplier.onboarding.*,
| supplier.pricing, supplier.subscribe.*).
|
*/

Route::middleware(['auth', 'verified'])->prefix('supplier')->name('supplier.')->group(function () {

    // Dashboard — outside RequireSupplierCapability (shows status page for non-active)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // All routes below require an active Supplier capability AND an active
    // subscription (supplier_dashboard_workflow.md Part 9.1 / concept.md:
    // "cannot use the dashboard until they pick a plan" — EnsureSupplierHasPlan
    // redirects to supplier.pricing otherwise). The dashboard root itself stays
    // exempt so the pending/no-plan supplier still lands on a status page.
    Route::middleware([RequireSupplierCapability::class, EnsureSupplierHasPlan::class])->group(function () {

        // ── Business Profile ──────────────────────────────────────────────
        // Consolidated into one expandable accordion page (docs/AI/design.md
        // §43) — plain Controller + Form Request + Service + Blade + Alpine
        // per ARCHITECTURE.md Rule 1 (no backend Livewire). Every sub-section
        // is its own resource with its own controller/routes, each posting
        // back to the same "profile" page independently.
        Route::prefix('company')->name('company.')->group(function () {
            Route::get('/profile', [\App\Http\Controllers\Backend\Supplier\Company\ProfileController::class, 'edit'])->name('profile');

            Route::put('/profile/company', [\App\Http\Controllers\Backend\Supplier\Company\CompanyController::class, 'update'])->name('profile.company.update');
            Route::put('/profile/contact', [\App\Http\Controllers\Backend\Supplier\Company\ContactController::class, 'update'])->name('profile.contact.update');
            Route::put('/profile/media', [\App\Http\Controllers\Backend\Supplier\Company\MediaController::class, 'update'])->name('profile.media.update');

            Route::post('/profile/gallery', [\App\Http\Controllers\Backend\Supplier\Company\GalleryController::class, 'store'])->name('profile.gallery.store');
            Route::delete('/profile/gallery/{image}', [\App\Http\Controllers\Backend\Supplier\Company\GalleryController::class, 'destroy'])->name('profile.gallery.destroy');

            Route::post('/profile/videos', [\App\Http\Controllers\Backend\Supplier\Company\VideoController::class, 'store'])->name('profile.videos.store');
            Route::delete('/profile/videos/{video}', [\App\Http\Controllers\Backend\Supplier\Company\VideoController::class, 'destroy'])->name('profile.videos.destroy');

            Route::post('/profile/service-areas', [\App\Http\Controllers\Backend\Supplier\Company\ServiceAreaController::class, 'store'])->name('profile.service-areas.store');
            Route::put('/profile/service-areas/{serviceArea}', [\App\Http\Controllers\Backend\Supplier\Company\ServiceAreaController::class, 'update'])->name('profile.service-areas.update');
            Route::delete('/profile/service-areas/{serviceArea}', [\App\Http\Controllers\Backend\Supplier\Company\ServiceAreaController::class, 'destroy'])->name('profile.service-areas.destroy');
            Route::post('/profile/service-areas/{serviceArea}/primary', [\App\Http\Controllers\Backend\Supplier\Company\ServiceAreaController::class, 'makePrimary'])->name('profile.service-areas.primary');

            Route::put('/profile/business-hours', [\App\Http\Controllers\Backend\Supplier\Company\BusinessHourController::class, 'update'])->name('profile.business-hours.update');

            Route::post('/profile/exhibitions/{exhibition}/join', [\App\Http\Controllers\Backend\Supplier\Company\ExhibitionParticipationController::class, 'join'])->name('profile.exhibitions.join');
            Route::delete('/profile/exhibitions/{exhibition}', [\App\Http\Controllers\Backend\Supplier\Company\ExhibitionParticipationController::class, 'leave'])->name('profile.exhibitions.leave');

            Route::post('/profile/documents', [\App\Http\Controllers\Backend\Supplier\Company\DocumentController::class, 'store'])->name('profile.documents.store');
            Route::delete('/profile/documents/{document}', [\App\Http\Controllers\Backend\Supplier\Company\DocumentController::class, 'destroy'])->name('profile.documents.destroy');

            Route::post('/profile/services', [\App\Http\Controllers\Backend\Supplier\Company\ServiceController::class, 'store'])->name('profile.services.store');
            Route::put('/profile/services/{service}', [\App\Http\Controllers\Backend\Supplier\Company\ServiceController::class, 'update'])->name('profile.services.update');
            Route::delete('/profile/services/{service}', [\App\Http\Controllers\Backend\Supplier\Company\ServiceController::class, 'destroy'])->name('profile.services.destroy');

            Route::post('/profile/achievements/{achievement}/request', [\App\Http\Controllers\Backend\Supplier\Company\AchievementController::class, 'request'])->name('profile.achievements.request');
            Route::delete('/profile/achievements/{accountAchievement}', [\App\Http\Controllers\Backend\Supplier\Company\AchievementController::class, 'undo'])->name('profile.achievements.undo');

            Route::post('/profile/certifications', [\App\Http\Controllers\Backend\Supplier\Company\CertificationController::class, 'store'])->name('profile.certifications.store');
            Route::put('/profile/certifications/{certification}', [\App\Http\Controllers\Backend\Supplier\Company\CertificationController::class, 'update'])->name('profile.certifications.update');
            Route::delete('/profile/certifications/{certification}', [\App\Http\Controllers\Backend\Supplier\Company\CertificationController::class, 'destroy'])->name('profile.certifications.destroy');
        });

        // ── Catalog ───────────────────────────────────────────────────────
        // Category & Attribute Suggestions
        Route::prefix('catalog/suggestions')->name('catalog.suggestions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Catalog\SuggestionController::class, 'index'])->name('index');
            Route::post('/category', [\App\Http\Controllers\Backend\Supplier\Catalog\SuggestionController::class, 'storeCategory'])->name('category.store');
            Route::post('/attribute', [\App\Http\Controllers\Backend\Supplier\Catalog\SuggestionController::class, 'storeAttribute'])->name('attribute.store');
        });

        Route::prefix('catalog/listings')->name('catalog.listings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'create'])->name('create');
            Route::get('/categories/{category}/attributes', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'categoryAttributes'])->name('category.attributes');
            Route::post('/', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'store'])->name('store');
            Route::get('/{listing}/edit', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'edit'])->name('edit');
            Route::get('/{listing}', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'show'])->name('show');
            Route::get('/{listing}/preview', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'previewFragment'])->name('preview');
            Route::post('/{listing}/submit', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'submit'])->name('submit');
            Route::delete('/{listing}', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'destroy'])->name('destroy');

            // ── Step-by-Step Wizard Endpoints ─────────────────────────────────
            Route::post('/wizard/step-1', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'saveStep1'])->name('wizard.step1');
            Route::post('/{listing}/wizard/step-2', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'saveStep2'])->name('wizard.step2');
            Route::post('/{listing}/wizard/step-3', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'saveStep3'])->name('wizard.step3');
            Route::post('/{listing}/wizard/step-4', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'saveStep4'])->name('wizard.step4');

            // Media
            Route::post('/{listing}/media', [\App\Http\Controllers\Backend\Supplier\Catalog\MediaController::class, 'store'])->name('media.store');
            Route::post('/{listing}/media/primary', [\App\Http\Controllers\Backend\Supplier\Catalog\ListingController::class, 'setPrimaryMedia'])->name('media.primary');
            Route::delete('/{listing}/media/{media}', [\App\Http\Controllers\Backend\Supplier\Catalog\MediaController::class, 'destroy'])->name('media.destroy');
        });

        // ── RFQ Opportunities ─────────────────────────────────────────────
        Route::prefix('opportunities')->name('opportunities.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Procurement\OpportunityController::class, 'index'])->name('index');
            Route::get('/{rfq}', [\App\Http\Controllers\Backend\Supplier\Procurement\OpportunityController::class, 'show'])->name('show');
            Route::post('/{rfq}/questions', [\App\Http\Controllers\Backend\Supplier\Procurement\OpportunityController::class, 'askQuestion'])->name('questions.store');
            Route::post('/{rfq}/decline', [\App\Http\Controllers\Backend\Supplier\Procurement\OpportunityController::class, 'decline'])->name('decline');
        });

        // ── Quotations ────────────────────────────────────────────────────
        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'index'])->name('index');
            Route::get('/create/{rfq}', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'create'])->name('create');
            Route::post('/create/{rfq}', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'store'])->name('store');
            Route::get('/categories/{category}/attributes', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'categoryAttributes'])->name('category-attributes');
            Route::get('/listings/search', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'searchListings'])->name('listings.search');
            Route::get('/listings/{listing}/prefill', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'listingPrefill'])->name('listings.prefill');
            Route::get('/{quotation}/edit', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'edit'])->name('edit');
            Route::put('/{quotation}', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'update'])->name('update');
            Route::get('/{quotation}', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'show'])->name('show');
            Route::post('/{quotation}/submit', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'submit'])->name('submit');
            Route::post('/{quotation}/withdraw', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationController::class, 'withdraw'])->name('withdraw');

            // Revision response
            Route::get('/{quotation}/revision', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationRevisionController::class, 'create'])->name('revision.create');
            Route::post('/{quotation}/revision', [\App\Http\Controllers\Backend\Supplier\Procurement\QuotationRevisionController::class, 'store'])->name('revision.store');
        });

        // ── Awards ────────────────────────────────────────────────────────
        Route::prefix('awards')->name('awards.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Procurement\AwardController::class, 'index'])->name('index');
            Route::get('/{award}', [\App\Http\Controllers\Backend\Supplier\Procurement\AwardController::class, 'show'])->name('show');
            Route::post('/{award}/accept', [\App\Http\Controllers\Backend\Supplier\Procurement\AwardController::class, 'accept'])->name('accept');
            Route::post('/{award}/reject', [\App\Http\Controllers\Backend\Supplier\Procurement\AwardController::class, 'reject'])->name('reject');
        });

        // ── Purchase Orders ───────────────────────────────────────────────
        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Procurement\PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/{purchaseOrder}', [\App\Http\Controllers\Backend\Supplier\Procurement\PurchaseOrderController::class, 'show'])->name('show');
            // Phase 1: PO completion is buyer-authorized only (buyer_dashboard_workflow.md Part 7.4 /
            // supplier_dashboard_workflow.md 8.7 — "Supplier users must not invent or bypass that
            // authorization"). See buyer.purchase-orders.complete via PurchaseOrderPolicy::complete().
        });

        // ── Subscription & Billing ────────────────────────────────────────
        Route::prefix('subscription')->name('subscription.')->group(function () {
            Route::get('/current', [\App\Http\Controllers\Backend\Supplier\Billing\SubscriptionController::class, 'current'])->name('current');
            Route::get('/plans', [\App\Http\Controllers\Backend\Supplier\Billing\SubscriptionController::class, 'plans'])->name('plans');
            Route::get('/payments', [\App\Http\Controllers\Backend\Supplier\Billing\PaymentController::class, 'index'])->name('payments');
        });

        // ── Reviews ───────────────────────────────────────────────────────
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Review\ReviewController::class, 'index'])->name('index');
            Route::post('/{review}/reply', [\App\Http\Controllers\Backend\Supplier\Review\ReviewController::class, 'reply'])->name('reply');
            Route::post('/{review}/report', [\App\Http\Controllers\Backend\Supplier\Review\ReviewReportController::class, 'store'])->name('report');
        });

        // ── Communication ─────────────────────────────────────────────────
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'index'])->name('index');
            Route::post('/start', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'start'])->name('start');
            Route::get('/{conversation}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'show'])->name('show');
            Route::post('/{conversation}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'store'])->name('store');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Communication\NotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [\App\Http\Controllers\Backend\Supplier\Communication\NotificationController::class, 'markRead'])->name('read');
            Route::post('/read-all', [\App\Http\Controllers\Backend\Supplier\Communication\NotificationController::class, 'markAllRead'])->name('read-all');
        });

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Communication\TicketController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Backend\Supplier\Communication\TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [\App\Http\Controllers\Backend\Supplier\Communication\TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [\App\Http\Controllers\Backend\Supplier\Communication\TicketController::class, 'reply'])->name('reply');
        });

        // Contact Inquiries (inbound from public supplier profile page)
        Route::prefix('contact-inquiries')->name('contact-inquiries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Communication\ContactInquiryController::class, 'index'])->name('index');
            Route::get('/{inquiry}', [\App\Http\Controllers\Backend\Supplier\Communication\ContactInquiryController::class, 'show'])->name('show');
            Route::post('/{inquiry}/mark-replied', [\App\Http\Controllers\Backend\Supplier\Communication\ContactInquiryController::class, 'markReplied'])->name('mark-replied');
            Route::post('/{inquiry}/close', [\App\Http\Controllers\Backend\Supplier\Communication\ContactInquiryController::class, 'close'])->name('close');
        });

        // ── Organization (visible only for organization accounts) ─────────
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Organization\MemberController::class, 'index'])->name('index');
            Route::get('/{member}/permissions', [\App\Http\Controllers\Backend\Supplier\Organization\MemberController::class, 'editPermissions'])->name('permissions.edit');
            Route::put('/{member}/permissions', [\App\Http\Controllers\Backend\Supplier\Organization\MemberController::class, 'updatePermissions'])->name('permissions.update');
            Route::post('/{member}/suspend', [\App\Http\Controllers\Backend\Supplier\Organization\MemberController::class, 'suspend'])->name('suspend');
            Route::post('/{member}/activate', [\App\Http\Controllers\Backend\Supplier\Organization\MemberController::class, 'activate'])->name('activate');
            Route::delete('/{member}', [\App\Http\Controllers\Backend\Supplier\Organization\MemberController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('invitations')->name('invitations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Organization\InvitationController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Backend\Supplier\Organization\InvitationController::class, 'store'])->name('store');
            Route::post('/{invitation}/resend', [\App\Http\Controllers\Backend\Supplier\Organization\InvitationController::class, 'resend'])->name('resend');
            Route::delete('/{invitation}', [\App\Http\Controllers\Backend\Supplier\Organization\InvitationController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'store'])->name('store');
            Route::get('/{role}', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'destroy'])->name('destroy');
            Route::post('/{role}/duplicate', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'duplicate'])->name('duplicate');
            Route::post('/{role}/assign', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'assign'])->name('assign');
            Route::post('/{role}/unassign', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleController::class, 'unassign'])->name('unassign');
        });
        Route::get('/permissions', [\App\Http\Controllers\Backend\Supplier\AccessControl\PermissionController::class, 'index'])->name('permissions.index');

        Route::prefix('role-requests')->name('role-requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleRequestController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleRequestController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleRequestController::class, 'store'])->name('store');
            Route::delete('/{roleRequest}', [\App\Http\Controllers\Backend\Supplier\AccessControl\RoleRequestController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('ownership')->name('ownership.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Backend\Supplier\Organization\OwnershipController::class, 'index'])->name('index');
            Route::post('/transfer', [\App\Http\Controllers\Backend\Supplier\Organization\OwnershipController::class, 'transfer'])->name('transfer');
            Route::post('/{transfer}/cancel', [\App\Http\Controllers\Backend\Supplier\Organization\OwnershipController::class, 'cancel'])->name('cancel');
            Route::post('/{transfer}/accept', [\App\Http\Controllers\Backend\Supplier\Organization\OwnershipController::class, 'accept'])->name('accept');
            Route::post('/{transfer}/reject', [\App\Http\Controllers\Backend\Supplier\Organization\OwnershipController::class, 'reject'])->name('reject');
        });

        // ── Settings & Account ────────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/security', [\App\Http\Controllers\Backend\Supplier\Settings\SecurityController::class, 'edit'])->name('security');
            Route::put('/security', [\App\Http\Controllers\Backend\Supplier\Settings\SecurityController::class, 'update'])->name('security.update');

            Route::get('/dashboard-mode', [\App\Http\Controllers\Backend\Supplier\Settings\DashboardModeController::class, 'edit'])->name('dashboard-mode');
            Route::put('/dashboard-mode', [\App\Http\Controllers\Backend\Supplier\Settings\DashboardModeController::class, 'update'])->name('dashboard-mode.update');

            Route::get('/conversion', [\App\Http\Controllers\Backend\Supplier\Settings\AccountConversionController::class, 'edit'])->name('conversion');
            Route::post('/conversion', [\App\Http\Controllers\Backend\Supplier\Settings\AccountConversionController::class, 'submit'])->name('conversion.submit');

            Route::get('/close-account', [\App\Http\Controllers\Backend\Supplier\Settings\AccountClosureController::class, 'edit'])->name('close-account');
            Route::post('/close-account', [\App\Http\Controllers\Backend\Supplier\Settings\AccountClosureController::class, 'request'])->name('close-account.request');
        });
    });
});
