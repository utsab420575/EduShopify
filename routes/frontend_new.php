<?php

use App\Http\Controllers\FrontendNew\BlogController;
use App\Http\Controllers\FrontendNew\CategoryController;
use App\Http\Controllers\FrontendNew\CompareController;
use App\Http\Controllers\FrontendNew\HandoffController;
use App\Http\Controllers\FrontendNew\HomeController;
use App\Http\Controllers\FrontendNew\ProductController;
use App\Http\Controllers\FrontendNew\ResourceController;
use App\Http\Controllers\FrontendNew\RfqController;
use App\Http\Controllers\FrontendNew\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Frontend V2 Routes
|--------------------------------------------------------------------------
|
| Fresh implementation reproducing docs/AI/Frontend/*.html exactly, built
| independently of the legacy frontend (resources/views/frontend/,
| routes/frontend.php, app/Http/Controllers/Frontend/) — do not merge
| the two. Prefixed with /v2 during side-by-side development.
|
*/

Route::prefix('v2')->name('v2.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/supplier/{supplier:slug}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::post('/supplier/{supplier:slug}/save', [SupplierController::class, 'toggleSave'])->name('suppliers.save');
    Route::get('/product/{listing:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::post('/product/{listing:slug}/save', [ProductController::class, 'toggleSave'])->name('products.save');
    Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
    Route::get('/blog/{blogPost:slug}', [BlogController::class, 'show'])->name('blogs.show');
    Route::post('/blog/{blogPost:slug}/like', [BlogController::class, 'togglePostLike'])->name('blogs.like');
    Route::post('/blog/{blogPost:slug}/comment', [BlogController::class, 'storeComment'])->name('blogs.comment');
    Route::post('/blog/comment/{comment}/like', [BlogController::class, 'toggleCommentLike'])->name('blogs.comment.like');
    Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');

    /* ── Product Comparison (public, guest-accessible — no DB storage, ── */
    /* ── localStorage-driven; see App\Services\Catalog\ProductComparisonService) ── */
    Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
    Route::post('/compare/data', [CompareController::class, 'data'])->name('compare.data');
    Route::get('/compare/search', [CompareController::class, 'search'])->name('compare.search');

    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
    Route::get('/rfqs/{rfq_number}', [RfqController::class, 'show'])->name('rfqs.show');

    Route::prefix('handoff')->name('handoff.')->group(function () {
        Route::get('/submit-quotation/{rfq_number}', [HandoffController::class, 'submitQuotation'])->name('submit-quotation');
        Route::get('/post-rfq', [HandoffController::class, 'postRfq'])->name('post-rfq');
        Route::get('/compare-rfq', [HandoffController::class, 'compareRfq'])->name('compare-rfq');
        Route::get('/request-quote/{listing}', [HandoffController::class, 'requestQuoteListing'])->name('request-quote-listing');
        Route::get('/request-quote-supplier/{supplier}', [HandoffController::class, 'requestQuoteSupplier'])->name('request-quote-supplier');
        Route::get('/contact-supplier/{supplier}', [HandoffController::class, 'contactSupplier'])->name('contact-supplier');
    });
});
