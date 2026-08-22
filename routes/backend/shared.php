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
