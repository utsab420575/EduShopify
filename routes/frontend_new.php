<?php

use App\Http\Controllers\FrontendNew\HomeController;
use App\Http\Controllers\FrontendNew\ProductController;
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
    Route::get('/product/{listing:slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/supplier/{supplier:slug}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/rfqs', [RfqController::class, 'index'])->name('rfqs.index');
});
