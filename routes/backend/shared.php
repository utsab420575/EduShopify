<?php

use App\Http\Controllers\Backend\Shared\Account\InvitationAcceptanceController;
use App\Http\Controllers\Backend\Shared\LocationLookupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shared Backend Routes
|--------------------------------------------------------------------------
|
| Functionality shared across Buyer/Supplier/Admin backend portals per
| ARCHITECTURE.md section 10. Currently: reference-data lookups used by
| country → state → city cascading selects.
|
*/

Route::middleware(['auth'])->prefix('lookup')->name('lookup.')->group(function () {
    Route::get('/countries/{country}/states', [LocationLookupController::class, 'states'])->name('states');
    Route::get('/states/{state}/cities', [LocationLookupController::class, 'cities'])->name('cities');
});

/*
| Invitation acceptance — reachable by guests (a brand-new employee has no
| account yet) as well as authenticated users. Deliberately separate from
| the account-registration wizard: joins an EXISTING account.
*/
Route::prefix('invitations')->name('invitations.')->group(function () {
    Route::get('/{token}', [InvitationAcceptanceController::class, 'show'])->name('accept.show');
    Route::post('/{token}', [InvitationAcceptanceController::class, 'accept'])->name('accept.submit');
});

/*
|--------------------------------------------------------------------------
| Real-Time Messaging & Notification Routes (Shared)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'index'])->name('index');
    Route::post('/start', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'start'])->name('start');
    Route::get('/settings/preferences', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'preferences'])->name('preferences');
    Route::post('/settings/preferences', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'updatePreferences'])->name('preferences.update');
    Route::get('/item/{message}/attachments/{media}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'downloadAttachment'])->name('attachments.download');
    Route::put('/item/{message}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'update'])->name('update');
    Route::delete('/item/{message}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'destroy'])->name('destroy');
    Route::post('/item/{message}/delivered', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'delivered'])->name('delivered');
    Route::get('/{conversation}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'show'])->name('show');
    Route::post('/{conversation}', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'store'])->name('store');
    Route::post('/{conversation}/seen', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'seen'])->name('seen');
    Route::post('/{conversation}/mute', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'toggleMute'])->name('mute');
    Route::post('/{conversation}/archive', [\App\Http\Controllers\Backend\Communication\UnifiedMessageController::class, 'toggleArchive'])->name('archive');
});

