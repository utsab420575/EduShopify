<?php

use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\HandoffController;
use App\Http\Controllers\Frontend\InquiryController;
use App\Http\Controllers\Frontend\ListingController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PublicRfqController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SupplierDirectoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
|
| The public marketplace, per docs/ai/workflows/frontend_workflow.md and
| docs/ai/design_frontend.md. Entirely separate from Backend/Admin/Buyer/
| Supplier per ARCHITECTURE.md Rule 2 — no auth middleware here except on
| the handoff endpoints, which redirect rather than render.
|
*/

Route::get('/catalog', [CatalogController::class, 'index'])->name('frontend.catalog.index');
Route::get('/products', [CatalogController::class, 'products'])->name('frontend.products.index');
Route::get('/services', [CatalogController::class, 'services'])->name('frontend.services.index');
Route::get('/listing/{listing:slug}', [ListingController::class, 'show'])->name('frontend.listings.show');

Route::get('/categories', [CategoryController::class, 'index'])->name('frontend.categories.index');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('frontend.categories.show');

Route::get('/suppliers', [SupplierDirectoryController::class, 'index'])->name('frontend.suppliers.index');
Route::get('/supplier/{supplier:slug}', [SupplierDirectoryController::class, 'show'])->name('frontend.suppliers.show');

Route::get('/opportunities', [PublicRfqController::class, 'index'])->name('frontend.rfqs.index');
Route::get('/opportunities/{rfq_number}', [PublicRfqController::class, 'show'])->name('frontend.rfqs.show');

Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('frontend.search.suggestions');

Route::post('/inquire/{listing:slug}', [InquiryController::class, 'listing'])->name('frontend.inquiries.listing')
    ->middleware('throttle:10,1');
Route::post('/inquire/supplier/{supplier:slug}', [InquiryController::class, 'supplier'])->name('frontend.inquiries.supplier')
    ->middleware('throttle:10,1');

Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('frontend.pages.how-it-works');
Route::get('/pricing', [PageController::class, 'pricing'])->name('frontend.pages.pricing');
Route::get('/about', [PageController::class, 'about'])->name('frontend.pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('frontend.pages.contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('frontend.pages.contact.submit')
    ->middleware('throttle:10,1');
Route::get('/faqs', [PageController::class, 'faqs'])->name('frontend.pages.faqs');
Route::get('/terms', [PageController::class, 'terms'])->name('frontend.pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('frontend.pages.privacy');

/* ── Protected-action handoff (frontend_workflow.md Parts 50-53) ── */
Route::prefix('handoff')->name('frontend.handoff.')->group(function () {
    Route::get('/post-rfq', [HandoffController::class, 'postRfq'])->name('post-rfq');
    Route::get('/request-quote/{listing:slug}', [HandoffController::class, 'requestQuoteListing'])->name('request-quote-listing');
    Route::get('/request-quote/supplier/{supplier:slug}', [HandoffController::class, 'requestQuoteSupplier'])->name('request-quote-supplier');
    Route::get('/submit-quotation/{rfq_number}', [HandoffController::class, 'submitQuotation'])->name('submit-quotation');
    Route::get('/save-listing/{listing:slug}', [HandoffController::class, 'saveListing'])->name('save-listing');
    Route::get('/save-supplier/{supplier:slug}', [HandoffController::class, 'saveSupplier'])->name('save-supplier');
});
