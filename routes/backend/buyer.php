<?php

use App\Http\Controllers\Backend\Buyer\AccessControl\PermissionController;
use App\Http\Controllers\Backend\Buyer\AccessControl\RoleController;
use App\Http\Controllers\Backend\Buyer\AccessControl\RoleRequestController;
use App\Http\Controllers\Backend\Buyer\Account\LocationController;
use App\Http\Controllers\Backend\Buyer\Account\ProfileController;
use App\Http\Controllers\Backend\Buyer\Communication\MessageController;
use App\Http\Controllers\Backend\Buyer\Communication\NotificationController;
use App\Http\Controllers\Backend\Buyer\Communication\TicketController;
use App\Http\Controllers\Backend\Buyer\DashboardController;
use App\Http\Controllers\Backend\Buyer\Marketplace\ProductController;
use App\Http\Controllers\Backend\Buyer\Marketplace\ServiceController;
use App\Http\Controllers\Backend\Buyer\Organization\InvitationController;
use App\Http\Controllers\Backend\Buyer\Organization\MemberController;
use App\Http\Controllers\Backend\Buyer\Organization\OwnershipController;
use App\Http\Controllers\Backend\Buyer\Procurement\AwardController;
use App\Http\Controllers\Backend\Buyer\Procurement\PurchaseOrderController;
use App\Http\Controllers\Backend\Buyer\Procurement\QuotationController;
use App\Http\Controllers\Backend\Buyer\Procurement\RfqController;
use App\Http\Controllers\Backend\Buyer\Review\ReviewController;
use App\Http\Controllers\Backend\Buyer\SavedItem\SavedItemController;
use App\Http\Controllers\Backend\Buyer\Settings\AccountClosureController;
use App\Http\Controllers\Backend\Buyer\Settings\AccountConversionController;
use App\Http\Controllers\Backend\Buyer\Settings\DashboardModeController;
use App\Http\Controllers\Backend\Buyer\Settings\SecurityController;
use App\Http\Controllers\Backend\Buyer\Supplier\SupplierDirectoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Buyer Dashboard Routes
|--------------------------------------------------------------------------
|
| All routes here are backend, portal-scoped to Buyer per ARCHITECTURE.md.
| The dashboard landing route is deliberately outside RequireBuyerCapability
| so a buyer whose capability is pending/rejected/revision_required still
| lands on a status page instead of a bare 403.
|
*/

Route::middleware(['auth', 'verified'])->prefix('buyer')->name('buyer.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware([\App\Http\Middleware\RequireBuyerCapability::class])->group(function () {

        /* Marketplace */
        Route::prefix('marketplace/products')->name('marketplace.products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/{listing}', [ProductController::class, 'show'])->name('show');
        });

        Route::prefix('marketplace/services')->name('marketplace.services.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::get('/{listing}', [ServiceController::class, 'show'])->name('show');
        });

        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierDirectoryController::class, 'index'])->name('index');
            Route::get('/{supplierAccount}', [SupplierDirectoryController::class, 'show'])->name('show');
            Route::post('/{supplierAccount}/save', [SupplierDirectoryController::class, 'toggleSave'])->name('save');
            Route::post('/{supplierAccount}/message', [SupplierDirectoryController::class, 'message'])->name('message');
        });

        /* Procurement */
        Route::prefix('rfqs')->name('rfqs.')->group(function () {
            Route::get('/', [RfqController::class, 'index'])->name('index');
            Route::get('/create', [RfqController::class, 'create'])->name('create');
            Route::post('/', [RfqController::class, 'store'])->name('store');
            Route::get('/supplier-search', [RfqController::class, 'searchSuppliers'])->name('supplier-search');
            Route::get('/{rfq}/edit', [RfqController::class, 'edit'])->name('edit');
            Route::put('/{rfq}', [RfqController::class, 'update'])->name('update');
            Route::get('/{rfq}', [RfqController::class, 'show'])->name('show');
            Route::post('/{rfq}/publish', [RfqController::class, 'publish'])->name('publish');
            Route::post('/{rfq}/cancel', [RfqController::class, 'cancel'])->name('cancel');
            Route::post('/{rfq}/extend-deadline', [RfqController::class, 'extendDeadline'])->name('extend-deadline');
            Route::post('/{rfq}/questions/{question}/answer', [RfqController::class, 'answerQuestion'])->name('questions.answer');
        });

        Route::prefix('quotations')->name('quotations.')->group(function () {
            Route::get('/', [QuotationController::class, 'index'])->name('index');
            Route::get('/compare/{rfq}', [QuotationController::class, 'compare'])->name('compare');
            Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
            Route::post('/{quotation}/shortlist', [QuotationController::class, 'shortlist'])->name('shortlist');
            Route::delete('/{quotation}/shortlist', [QuotationController::class, 'unshortlist'])->name('unshortlist');
            Route::post('/{quotation}/request-revision', [QuotationController::class, 'requestRevision'])->name('request-revision');
            Route::post('/{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
            Route::post('/{quotation}/award', [QuotationController::class, 'award'])->name('award');
        });

        Route::prefix('awards')->name('awards.')->group(function () {
            Route::get('/', [AwardController::class, 'index'])->name('index');
            Route::get('/{award}', [AwardController::class, 'show'])->name('show');
        });

        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('show');
            Route::post('/{purchaseOrder}/complete', [PurchaseOrderController::class, 'complete'])->name('complete');
        });

        /* Saved Items */
        Route::get('/saved-items', [SavedItemController::class, 'index'])->name('saved-items.index');
        Route::post('/saved-items/toggle', [SavedItemController::class, 'toggle'])->name('saved-items.toggle');

        /* Reviews */
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::post('/quotation/{quotation}', [ReviewController::class, 'storeForQuotation'])->name('store-for-quotation');
            Route::post('/purchase-order/{purchaseOrder}', [ReviewController::class, 'storeForPurchaseOrder'])->name('store-for-purchase-order');
        });

        /* Communication */
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::get('/{conversation}', [MessageController::class, 'show'])->name('show');
            Route::post('/{conversation}', [MessageController::class, 'store'])->name('store');
            Route::get('/{conversation}/poll', [MessageController::class, 'poll'])->name('poll');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
            Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        });

        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::post('/', [TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        });

        /* Buyer Profile */
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/', [LocationController::class, 'index'])->name('index');
            Route::post('/', [LocationController::class, 'store'])->name('store');
            Route::put('/{location}', [LocationController::class, 'update'])->name('update');
            Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
            Route::post('/{location}/primary', [LocationController::class, 'makePrimary'])->name('primary');
        });

        /* Organization (visible only for organization accounts; controllers still enforce it) */
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::post('/{member}/suspend', [MemberController::class, 'suspend'])->name('suspend');
            Route::post('/{member}/activate', [MemberController::class, 'activate'])->name('activate');
            Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('invitations')->name('invitations.')->group(function () {
            Route::get('/', [InvitationController::class, 'index'])->name('index');
            Route::post('/', [InvitationController::class, 'store'])->name('store');
            Route::post('/{invitation}/resend', [InvitationController::class, 'resend'])->name('resend');
            Route::delete('/{invitation}', [InvitationController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::post('/{role}/assign', [RoleController::class, 'assign'])->name('assign');
            Route::post('/{role}/unassign', [RoleController::class, 'unassign'])->name('unassign');
        });
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

        Route::prefix('role-requests')->name('role-requests.')->group(function () {
            Route::get('/', [RoleRequestController::class, 'index'])->name('index');
            Route::get('/create', [RoleRequestController::class, 'create'])->name('create');
            Route::post('/', [RoleRequestController::class, 'store'])->name('store');
            Route::delete('/{roleRequest}', [RoleRequestController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('ownership')->name('ownership.')->group(function () {
            Route::get('/', [OwnershipController::class, 'index'])->name('index');
            Route::post('/transfer', [OwnershipController::class, 'transfer'])->name('transfer');
            Route::post('/{transfer}/cancel', [OwnershipController::class, 'cancel'])->name('cancel');
            Route::post('/{transfer}/accept', [OwnershipController::class, 'accept'])->name('accept');
            Route::post('/{transfer}/reject', [OwnershipController::class, 'reject'])->name('reject');
        });

        /* Settings & Account */
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/security', [SecurityController::class, 'edit'])->name('security');
            Route::put('/security', [SecurityController::class, 'update'])->name('security.update');

            Route::get('/dashboard-mode', [DashboardModeController::class, 'edit'])->name('dashboard-mode');
            Route::put('/dashboard-mode', [DashboardModeController::class, 'update'])->name('dashboard-mode.update');

            Route::get('/conversion', [AccountConversionController::class, 'edit'])->name('conversion');
            Route::post('/conversion', [AccountConversionController::class, 'submit'])->name('conversion.submit');

            Route::get('/close-account', [AccountClosureController::class, 'edit'])->name('close-account');
            Route::post('/close-account', [AccountClosureController::class, 'request'])->name('close-account.request');
        });
    });
});
