<?php

use App\Http\Controllers\Backend\Admin\AccessControl\PermissionController as AdminPermissionController;
use App\Http\Controllers\Backend\Admin\AccessControl\RoleController as AdminRoleController;
use App\Http\Controllers\Backend\Admin\AccessControl\RoleRequestController as AdminRoleRequestController;
use App\Http\Controllers\Backend\Admin\Account\AccountController;
use App\Http\Controllers\Backend\Admin\Account\AccountMemberController;
use App\Http\Controllers\Backend\Admin\Account\BuyerController;
use App\Http\Controllers\Backend\Admin\Account\CapabilityController;
use App\Http\Controllers\Backend\Admin\Account\ClosureController;
use App\Http\Controllers\Backend\Admin\Account\ConversionController;
use App\Http\Controllers\Backend\Admin\Account\SupplierController;
use App\Http\Controllers\Backend\Admin\Account\SupplierDocumentController;
use App\Http\Controllers\Backend\Admin\Account\UserController;
use App\Http\Controllers\Backend\Admin\Approval\ApprovalCenterController;
use App\Http\Controllers\Backend\Admin\Billing\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Backend\Admin\Billing\SubscriptionPaymentController;
use App\Http\Controllers\Backend\Admin\Billing\SubscriptionPlanController;
use App\Http\Controllers\Backend\Admin\Catalog\AttributeController;
use App\Http\Controllers\Backend\Admin\Catalog\BrandController;
use App\Http\Controllers\Backend\Admin\Catalog\BuyerTypeController;
use App\Http\Controllers\Backend\Admin\Catalog\CategoryController;
use App\Http\Controllers\Backend\Admin\Catalog\DocumentTypeController;
use App\Http\Controllers\Backend\Admin\Catalog\ExhibitionController;
use App\Http\Controllers\Backend\Admin\Catalog\ListingController;
use App\Http\Controllers\Backend\Admin\Catalog\SupplierTypeController;
use App\Http\Controllers\Backend\Admin\Catalog\UnitController;
use App\Http\Controllers\Backend\Admin\Communication\ContactInquiryController;
use App\Http\Controllers\Backend\Admin\Communication\ConversationController;
use App\Http\Controllers\Backend\Admin\Communication\NotificationController as AdminNotificationController;
use App\Http\Controllers\Backend\Admin\DashboardController;
use App\Http\Controllers\Backend\Admin\Procurement\AwardController as AdminAwardController;
use App\Http\Controllers\Backend\Admin\Procurement\PurchaseOrderController as AdminPurchaseOrderController;
use App\Http\Controllers\Backend\Admin\Procurement\QuotationController as AdminQuotationController;
use App\Http\Controllers\Backend\Admin\Procurement\RfqController as AdminRfqController;
use App\Http\Controllers\Backend\Admin\Review\ReviewController as AdminReviewController;
use App\Http\Controllers\Backend\Admin\Review\ReviewReplyController;
use App\Http\Controllers\Backend\Admin\Review\ReviewReportController;
use App\Http\Controllers\Backend\Admin\Support\TicketController as AdminTicketController;
use App\Http\Controllers\Backend\Admin\System\AuditLogController;
use App\Http\Controllers\Backend\Admin\System\CurrencyController;
use App\Http\Controllers\Backend\Admin\System\FailedJobController;
use App\Http\Controllers\Backend\Admin\System\GeographyController;
use App\Http\Controllers\Backend\Admin\System\LanguageController;
use App\Http\Controllers\Backend\Admin\System\SettingController;
use App\Http\Controllers\Backend\Admin\System\ThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
|
| Platform governance per docs/ai/workflows/admin_dashboard_workflow.md.
| Rebuilt as Controllers + Blade (no Filament, no Livewire) per that spec's
| explicit instruction. Gated by EnsurePlatformAdmin (reserved system
| account + platform staff role) and fine-grained by Spatie permission
| checks inside each controller/policy.
|
*/

Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsurePlatformAdmin::class])
    ->prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        /* Users & Accounts */
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
        Route::post('/accounts/{account}/approve', [AccountController::class, 'approve'])->name('accounts.approve');
        Route::post('/accounts/{account}/suspend', [AccountController::class, 'suspend'])->name('accounts.suspend');
        Route::post('/accounts/{account}/reactivate', [AccountController::class, 'reactivate'])->name('accounts.reactivate');

        Route::get('/buyers', [BuyerController::class, 'index'])->name('buyers.index');
        Route::get('/buyers/{account}', [BuyerController::class, 'show'])->name('buyers.show');

        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{account}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::post('/suppliers/{account}/documents/{document}/verify', [SupplierDocumentController::class, 'verify'])->name('suppliers.documents.verify');
        Route::post('/suppliers/{account}/documents/{document}/reject', [SupplierDocumentController::class, 'reject'])->name('suppliers.documents.reject');

        Route::get('/account-members', [AccountMemberController::class, 'index'])->name('account-members.index');

        Route::get('/capabilities', [CapabilityController::class, 'index'])->name('capabilities.index');
        Route::get('/capabilities/{capability}', [CapabilityController::class, 'show'])->name('capabilities.show');
        Route::post('/capabilities/{capability}/approve', [CapabilityController::class, 'approve'])->name('capabilities.approve');
        Route::post('/capabilities/{capability}/revision', [CapabilityController::class, 'revision'])->name('capabilities.revision');
        Route::post('/capabilities/{capability}/reject', [CapabilityController::class, 'reject'])->name('capabilities.reject');
        Route::post('/capabilities/{capability}/suspend', [CapabilityController::class, 'suspend'])->name('capabilities.suspend');
        Route::post('/capabilities/{capability}/reactivate', [CapabilityController::class, 'reactivate'])->name('capabilities.reactivate');

        Route::get('/conversions', [ConversionController::class, 'index'])->name('conversions.index');
        Route::get('/conversions/{conversion}', [ConversionController::class, 'show'])->name('conversions.show');
        Route::post('/conversions/{conversion}/approve', [ConversionController::class, 'approve'])->name('conversions.approve');
        Route::post('/conversions/{conversion}/revision', [ConversionController::class, 'revision'])->name('conversions.revision');
        Route::post('/conversions/{conversion}/reject', [ConversionController::class, 'reject'])->name('conversions.reject');

        Route::get('/closures', [ClosureController::class, 'index'])->name('closures.index');
        Route::get('/closures/{account}', [ClosureController::class, 'show'])->name('closures.show');
        Route::post('/closures/{account}/finalize', [ClosureController::class, 'finalize'])->name('closures.finalize');
        Route::post('/closures/{account}/hold', [ClosureController::class, 'hold'])->name('closures.hold');

        /* Approval Center */
        Route::get('/approvals', [ApprovalCenterController::class, 'index'])->name('approvals.index');

        /* Catalog & Taxonomy */
        Route::prefix('catalog')->name('catalog.')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::post('/categories/suggestions/{suggestion}/approve', [CategoryController::class, 'approveSuggestion'])->name('categories.suggestions.approve');
            Route::post('/categories/suggestions/{suggestion}/reject', [CategoryController::class, 'rejectSuggestion'])->name('categories.suggestions.reject');

            Route::resource('attributes', AttributeController::class)->except(['show']);
            Route::post('/attributes/suggestions/{suggestion}/approve', [AttributeController::class, 'approveSuggestion'])->name('attributes.suggestions.approve');
            Route::post('/attributes/suggestions/{suggestion}/reject', [AttributeController::class, 'rejectSuggestion'])->name('attributes.suggestions.reject');

            Route::resource('brands', BrandController::class)->except(['show']);
            Route::post('/brands/{brand}/approve', [BrandController::class, 'approve'])->name('brands.approve');
            Route::post('/brands/{brand}/reject', [BrandController::class, 'reject'])->name('brands.reject');

            Route::resource('units', UnitController::class)->except(['show']);
            Route::post('/units/{unit}/approve', [UnitController::class, 'approve'])->name('units.approve');
            Route::post('/units/{unit}/reject', [UnitController::class, 'reject'])->name('units.reject');

            Route::get('/listings', [ListingController::class, 'index'])->name('listings.index');
            Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
            Route::post('/listings/{listing}/approve', [ListingController::class, 'approve'])->name('listings.approve');
            Route::post('/listings/{listing}/reject', [ListingController::class, 'reject'])->name('listings.reject');
            Route::post('/listings/{listing}/deactivate', [ListingController::class, 'deactivate'])->name('listings.deactivate');
            Route::post('/listings/{listing}/reactivate', [ListingController::class, 'reactivate'])->name('listings.reactivate');
            Route::post('/listings/{listing}/feature', [ListingController::class, 'feature'])->name('listings.feature');

            Route::resource('buyer-types', BuyerTypeController::class)->except(['show']);
            Route::resource('supplier-types', SupplierTypeController::class)->except(['show']);
            Route::resource('document-types', DocumentTypeController::class)->except(['show']);
            Route::resource('exhibitions', ExhibitionController::class)->except(['show']);
        });

        /* Procurement Oversight (read + controlled override) */
        Route::prefix('procurement')->name('procurement.')->group(function () {
            Route::get('/rfqs', [AdminRfqController::class, 'index'])->name('rfqs.index');
            Route::get('/rfqs/{rfq}', [AdminRfqController::class, 'show'])->name('rfqs.show');
            Route::post('/rfqs/{rfq}/approve', [AdminRfqController::class, 'approve'])->name('rfqs.approve');
            Route::post('/rfqs/{rfq}/cancel', [AdminRfqController::class, 'cancel'])->name('rfqs.cancel');

            Route::get('/quotations', [AdminQuotationController::class, 'index'])->name('quotations.index');
            Route::get('/quotations/{quotation}', [AdminQuotationController::class, 'show'])->name('quotations.show');

            Route::get('/awards', [AdminAwardController::class, 'index'])->name('awards.index');
            Route::get('/awards/{award}', [AdminAwardController::class, 'show'])->name('awards.show');
            Route::post('/awards/{award}/cancel', [AdminAwardController::class, 'cancel'])->name('awards.cancel');

            Route::get('/purchase-orders', [AdminPurchaseOrderController::class, 'index'])->name('purchase-orders.index');
            Route::get('/purchase-orders/{purchaseOrder}', [AdminPurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        });

        /* Subscription & Billing */
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::resource('plans', SubscriptionPlanController::class)->except(['show']);
            Route::post('/plans/{plan}/activate', [SubscriptionPlanController::class, 'activate'])->name('plans.activate');
            Route::post('/plans/{plan}/deactivate', [SubscriptionPlanController::class, 'deactivate'])->name('plans.deactivate');

            Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::get('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'show'])->name('subscriptions.show');
            Route::post('/subscriptions/{subscription}/suspend', [AdminSubscriptionController::class, 'suspend'])->name('subscriptions.suspend');
            Route::post('/subscriptions/{subscription}/cancel', [AdminSubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
            Route::post('/subscriptions/{subscription}/extend', [AdminSubscriptionController::class, 'extend'])->name('subscriptions.extend');

            Route::get('/payments', [SubscriptionPaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{payment}', [SubscriptionPaymentController::class, 'show'])->name('payments.show');
            Route::post('/payments/{payment}/mark-paid', [SubscriptionPaymentController::class, 'markPaid'])->name('payments.mark-paid');
            Route::post('/payments/{payment}/refund', [SubscriptionPaymentController::class, 'refund'])->name('payments.refund');
        });

        /* Communication */
        Route::prefix('communication')->name('communication.')->group(function () {
            Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
            Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
            Route::post('/conversations/{conversation}/join', [ConversationController::class, 'join'])->name('conversations.join');
            Route::post('/conversations/{conversation}', [ConversationController::class, 'store'])->name('conversations.store');

            Route::get('/inquiries', [ContactInquiryController::class, 'index'])->name('inquiries.index');
            Route::get('/inquiries/{inquiry}', [ContactInquiryController::class, 'show'])->name('inquiries.show');
            Route::post('/inquiries/{inquiry}/status', [ContactInquiryController::class, 'updateStatus'])->name('inquiries.status');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [AdminNotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('read');
            Route::post('/read-all', [AdminNotificationController::class, 'markAllRead'])->name('read-all');
        });

        /* Reviews & Moderation
           /replies and /reports must be registered BEFORE the single-segment
           /{review} wildcard below — otherwise Laravel matches GET /replies
           and GET /reports as {review}="replies"/"reports" and 404s trying to
           find a Review with that route key. ── */
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [AdminReviewController::class, 'index'])->name('index');

            Route::get('/replies', [ReviewReplyController::class, 'index'])->name('replies.index');
            Route::post('/replies/{reply}/hide', [ReviewReplyController::class, 'hide'])->name('replies.hide');
            Route::post('/replies/{reply}/publish', [ReviewReplyController::class, 'publish'])->name('replies.publish');

            Route::get('/reports', [ReviewReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/{report}', [ReviewReportController::class, 'show'])->name('reports.show');
            Route::post('/reports/{report}/dismiss', [ReviewReportController::class, 'dismiss'])->name('reports.dismiss');
            Route::post('/reports/{report}/action-taken', [ReviewReportController::class, 'actionTaken'])->name('reports.action-taken');

            Route::get('/{review}', [AdminReviewController::class, 'show'])->name('show');
            Route::post('/{review}/publish', [AdminReviewController::class, 'publish'])->name('publish');
            Route::post('/{review}/hide', [AdminReviewController::class, 'hide'])->name('hide');
            Route::post('/{review}/reject', [AdminReviewController::class, 'reject'])->name('reject');
        });

        /* Support */
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [AdminTicketController::class, 'index'])->name('index');
            Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/resolve', [AdminTicketController::class, 'resolve'])->name('resolve');
            Route::post('/{ticket}/close', [AdminTicketController::class, 'close'])->name('close');
            Route::post('/{ticket}/reopen', [AdminTicketController::class, 'reopen'])->name('reopen');
        });

        /* Access Control */
        Route::prefix('access-control')->name('access-control.')->group(function () {
            Route::resource('roles', AdminRoleController::class)->except(['show']);
            Route::post('/roles/{role}/assign', [AdminRoleController::class, 'assign'])->name('roles.assign');
            Route::post('/roles/{role}/unassign', [AdminRoleController::class, 'unassign'])->name('roles.unassign');

            Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');
            Route::put('/permissions/{permission}', [AdminPermissionController::class, 'update'])->name('permissions.update');

            Route::get('/role-requests', [AdminRoleRequestController::class, 'index'])->name('role-requests.index');
            Route::get('/role-requests/{roleRequest}', [AdminRoleRequestController::class, 'show'])->name('role-requests.show');
            Route::post('/role-requests/{roleRequest}/approve', [AdminRoleRequestController::class, 'approve'])->name('role-requests.approve');
            Route::post('/role-requests/{roleRequest}/reject', [AdminRoleRequestController::class, 'reject'])->name('role-requests.reject');
        });

        /* System & Settings */
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

            Route::get('/theme', [ThemeController::class, 'edit'])->name('theme.edit');
            Route::put('/theme', [ThemeController::class, 'update'])->name('theme.update');
            Route::post('/theme/reset', [ThemeController::class, 'reset'])->name('theme.reset');

            Route::get('/geography', [GeographyController::class, 'index'])->name('geography.index');
            Route::post('/geography/countries/{country}/toggle', [GeographyController::class, 'toggleCountry'])->name('geography.countries.toggle');
            Route::get('/geography/countries/{country}/states', [GeographyController::class, 'states'])->name('geography.states');
            Route::post('/geography/states/{state}/toggle', [GeographyController::class, 'toggleState'])->name('geography.states.toggle');
            Route::get('/geography/states/{state}/cities', [GeographyController::class, 'cities'])->name('geography.cities');
            Route::post('/geography/cities/{city}/toggle', [GeographyController::class, 'toggleCity'])->name('geography.cities.toggle');

            Route::resource('currencies', CurrencyController::class)->except(['show']);
            Route::post('/currencies/{currency}/default', [CurrencyController::class, 'makeDefault'])->name('currencies.default');

            Route::resource('languages', LanguageController::class)->except(['show']);

            Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
            Route::get('/audit/{activity}', [AuditLogController::class, 'show'])->name('audit.show');

            Route::get('/jobs', [FailedJobController::class, 'index'])->name('jobs.index');
            Route::post('/jobs/{id}/retry', [FailedJobController::class, 'retry'])->name('jobs.retry');
            Route::delete('/jobs/{id}', [FailedJobController::class, 'destroy'])->name('jobs.destroy');
        });
    });
