<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Supplier Workspace Routes (Business Profile + Catalog Listings)
|--------------------------------------------------------------------------
|
| Registered twice, with the same controllers and views:
|   routes/backend/supplier.php → /supplier/...                      (name: supplier.*)
|   routes/backend/admin.php    → /admin/suppliers/{supplierAccount}/manage/...
|                                 (name: admin.supplier-manage.*, ManageSupplierAccount)
|
| Views/controllers build these URLs with supplier_route('company.profile', ...)
| so each entry point stays on its own side. See App\Support\SupplierWorkspace.
|
*/

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
    Route::post('/profile/social-links', [\App\Http\Controllers\Backend\Supplier\Company\SocialLinkController::class, 'store'])->name('profile.social-links.store');
    Route::put('/profile/social-links/{socialLink}', [\App\Http\Controllers\Backend\Supplier\Company\SocialLinkController::class, 'update'])->name('profile.social-links.update');
    Route::delete('/profile/social-links/{socialLink}', [\App\Http\Controllers\Backend\Supplier\Company\SocialLinkController::class, 'destroy'])->name('profile.social-links.destroy');
    Route::put('/profile/media', [\App\Http\Controllers\Backend\Supplier\Company\MediaController::class, 'update'])->name('profile.media.update');

    Route::post('/profile/gallery', [\App\Http\Controllers\Backend\Supplier\Company\GalleryController::class, 'store'])->name('profile.gallery.store');
    Route::put('/profile/gallery/{image}', [\App\Http\Controllers\Backend\Supplier\Company\GalleryController::class, 'update'])->name('profile.gallery.update');
    Route::delete('/profile/gallery/{image}', [\App\Http\Controllers\Backend\Supplier\Company\GalleryController::class, 'destroy'])->name('profile.gallery.destroy');

    Route::post('/profile/videos', [\App\Http\Controllers\Backend\Supplier\Company\VideoController::class, 'store'])->name('profile.videos.store');
    Route::put('/profile/videos/{video}', [\App\Http\Controllers\Backend\Supplier\Company\VideoController::class, 'update'])->name('profile.videos.update');
    Route::delete('/profile/videos/{video}', [\App\Http\Controllers\Backend\Supplier\Company\VideoController::class, 'destroy'])->name('profile.videos.destroy');

    Route::post('/profile/locations', [\App\Http\Controllers\Backend\Supplier\Company\LocationController::class, 'store'])->name('profile.locations.store');
    Route::put('/profile/locations/{location}', [\App\Http\Controllers\Backend\Supplier\Company\LocationController::class, 'update'])->name('profile.locations.update');
    Route::delete('/profile/locations/{location}', [\App\Http\Controllers\Backend\Supplier\Company\LocationController::class, 'destroy'])->name('profile.locations.destroy');
    Route::post('/profile/locations/{location}/primary', [\App\Http\Controllers\Backend\Supplier\Company\LocationController::class, 'makePrimary'])->name('profile.locations.primary');

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
    Route::post('/profile/services/reorder', [\App\Http\Controllers\Backend\Supplier\Company\ServiceController::class, 'reorder'])->name('profile.services.reorder');
    Route::put('/profile/services/{service}', [\App\Http\Controllers\Backend\Supplier\Company\ServiceController::class, 'update'])->name('profile.services.update');
    Route::delete('/profile/services/{service}', [\App\Http\Controllers\Backend\Supplier\Company\ServiceController::class, 'destroy'])->name('profile.services.destroy');

    Route::post('/profile/achievements/{achievement}/request', [\App\Http\Controllers\Backend\Supplier\Company\AchievementController::class, 'request'])->name('profile.achievements.request');
    Route::delete('/profile/achievements/{accountAchievement}', [\App\Http\Controllers\Backend\Supplier\Company\AchievementController::class, 'undo'])->name('profile.achievements.undo');

    Route::post('/profile/certifications', [\App\Http\Controllers\Backend\Supplier\Company\CertificationController::class, 'store'])->name('profile.certifications.store');
    Route::put('/profile/certifications/{certification}', [\App\Http\Controllers\Backend\Supplier\Company\CertificationController::class, 'update'])->name('profile.certifications.update');
    Route::delete('/profile/certifications/{certification}', [\App\Http\Controllers\Backend\Supplier\Company\CertificationController::class, 'destroy'])->name('profile.certifications.destroy');
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
