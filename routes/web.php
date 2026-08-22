<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Livewire\Auth\AccountRegister;
use App\Livewire\Auth\SupplierApplication;
use App\Livewire\Buyer\BuyerApplicationReview;
use App\Livewire\Buyer\BuyerProfileOnboarding;
use App\Http\Controllers\Subscription\CheckoutController;
use App\Http\Controllers\Subscription\PricingController;
use App\Http\Controllers\Subscription\WebhookController;
use App\Services\Account\PublicHandoffResolver;
use App\Services\BuyerOnboardingStateService;
use App\Services\SupplierOnboardingStateService;
use App\Support\FrontendIntent;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

use App\Http\Middleware\EnsureOnboardingComplete;

Route::get('/', [HomeController::class, 'index'])
    ->middleware([EnsureOnboardingComplete::class])
    ->name('home');

Route::get('/system-refresh', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'System refreshed successfully!';
});

/* ── Public Auth: Registration & Login ── */
Route::middleware('guest')->group(function () {

    // Unified multi-step Livewire registration wizard (Account Type → Capability → Details)
    Route::get('/register', AccountRegister::class)->name('register');

    // Login page
    Route::view('/login', 'auth.login')->name('login');

    // Login handler
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // User has not verified email yet — redirect to verification notice
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // Check if user status is active
            if (! $user->isActive()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is not accessible. Please contact support.'])->onlyInput('email');
            }

            // Activate Spatie team context
            $account = $user->activateTeamContext();

            if (! $account) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is not accessible. Please contact support.'])->onlyInput('email');
            }

            // Admin
            if ($account->is_system_account) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // A public-frontend CTA (Post RFQ, Request Quote, Submit Quotation, Save...)
            // sent this user here — resolve it now instead of the default destination.
            if ($request->session()->has('frontend_intent')) {
                $intent = FrontendIntent::pull();
                return redirect(app(PublicHandoffResolver::class)->resolve($user, $intent['action'], $intent['params']));
            }

            // Buyer — route through onboarding state resolver
            if ($account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists()) {
                $route = app(BuyerOnboardingStateService::class)->resolve($user);
                return redirect()->intended($route);
            }

            // Supplier (legacy path preserved)
            if ($account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists()) {
                return redirect()->intended(route('supplier.dashboard'));
            }

            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
    });
});

/* ── Logout ── */
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

/* ── Email Verification ── */
Route::middleware('auth')->group(function () {

    // Show verification notice / redirect if already verified
    Route::get('/email/verify', function (Request $request) {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            $account = $user->activateTeamContext();

            if ($account?->is_system_account) {
                return redirect()->route('admin.dashboard');
            }

            // Delegate buyer routing to state resolver
            if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists()) {
                return redirect(app(BuyerOnboardingStateService::class)->resolve($user));
            }

            // Supplier legacy path
            if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists()) {
                // Supplier onboarding state comes from the capability, not the profile.
                return redirect(app(SupplierOnboardingStateService::class)->resolveRoute($user));
            }

            return redirect('/');
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    /**
     * Idempotent email verification handler.
     * If already verified, skip all side effects.
     * If newly verified: activate user/account, assign primary_owner role.
     */
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $user = $request->user();

        // Idempotency guard — skip side effects if already verified
        if (! $user->hasVerifiedEmail()) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
                // Mark email verified
                $user->markEmailAsVerified();

                // Activate user
                if ($user->status === 'pending_verification') {
                    $user->update(['status' => 'active']);
                }

                // Activate account (only from draft — never reactivate suspended/deleted)
                $account = $user->accountMember?->account;
                if ($account && $account->status === 'draft') {
                    $account->update(['status' => 'active']);
                }

                // Assign primary_owner role in Spatie Teams context
                if ($account) {
                    app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
                    $user->unsetRelation('roles')->unsetRelation('permissions');

                    if (! $user->hasRole('primary_owner')) {
                        $user->assignRole('primary_owner');
                    }
                }
            });
        }

        $user->refresh();
        $account = $user->activateTeamContext();

        if ($request->session()->has('frontend_intent')) {
            $intent = FrontendIntent::pull();
            return redirect(app(PublicHandoffResolver::class)->resolve($user, $intent['action'], $intent['params']));
        }

        // Route buyer to onboarding
        if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'buyer'))->exists()) {
            return redirect(app(BuyerOnboardingStateService::class)->resolve($user));
        }

        // Supplier routing
        if ($account && $account->capabilities()->whereHas('capabilityType', fn($q) => $q->where('code', 'supplier'))->exists()) {
            return redirect(app(SupplierOnboardingStateService::class)->resolveRoute($user));
        }

        return redirect('/');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

/* ── Buyer Onboarding (Authenticated + Verified) ── */
Route::middleware(['auth', 'verified'])->prefix('buyer/onboarding')->name('buyer.onboarding.')->group(function () {

    // Profile completion (draft + save draft + complete)
    Route::get('/profile', BuyerProfileOnboarding::class)->name('profile');

    // Review & submit
    Route::get('/review', BuyerApplicationReview::class)->name('review');
});

/* ── Buyer Dashboard (Authenticated + Verified) ──
   Rebuilt per docs/ai/ARCHITECTURE.md + design.md + buyer_dashboard_workflow.md:
   standard Controllers/Requests/Blade, no Livewire. See routes/backend/buyer.php. ── */
require __DIR__.'/backend/shared.php';
require __DIR__.'/backend/buyer.php';
require __DIR__.'/backend/supplier.php';
require __DIR__.'/backend/admin.php';

/* ── Supplier Onboarding (Authenticated + Verified) ── */
Route::middleware(['auth', 'verified'])->prefix('supplier/onboarding')->name('supplier.onboarding.')->group(function () {
    Route::get('/profile', \App\Livewire\Auth\SupplierProfileOnboarding::class)->name('profile');
    Route::get('/documents', \App\Livewire\Auth\SupplierDocumentOnboarding::class)->name('documents');
    Route::get('/review', \App\Livewire\Auth\SupplierApplicationReview::class)->name('review');
    Route::get('/revision', \App\Livewire\Auth\SupplierProfileOnboarding::class)->name('revision');
    Route::get('/plan', \App\Livewire\Auth\SupplierPlanOnboarding::class)->name('plan');

    // Reuses the same status-aware view as `supplier.pending` (rejected/revision/pending
    // are all derived from the live capability status inside that view).
    Route::get('/rejected', function () {
        return view('auth.supplier-pending');
    })->name('rejected');

    Route::get('/subscription-pending', function () {
        return view('auth.supplier-subscription-pending');
    })->name('subscription-pending');
});

/* ── Supplier: pending approval, pricing & subscription checkout (Authenticated + Verified) ──
   Reachable both during onboarding and afterwards (e.g. renewing a lapsed subscription), so
   these use the flatter 'supplier.*' route names referenced across middleware, emails, and
   the public pricing page — kept separate from the 'supplier.onboarding.*' wizard steps. ── */
Route::middleware(['auth', 'verified'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/pending', function () {
        return view('auth.supplier-pending');
    })->name('pending');

    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
    Route::post('/subscribe/{plan:slug}', [CheckoutController::class, 'checkout'])->name('subscribe');
    Route::get('/subscribe/success', [CheckoutController::class, 'success'])->name('subscribe.success');
    Route::get('/subscribe/cancel', [CheckoutController::class, 'cancel'])->name('subscribe.cancel');

    /* ── Supplier Dashboard proper ──
       Rebuilt per docs/ai/ARCHITECTURE.md + design.md + supplier_dashboard_workflow.md:
       standard Controllers/Requests/Blade, no Livewire.
       All supplier.dashboard, supplier.catalog.*, supplier.opportunities.*,
       supplier.quotations.*, supplier.awards.*, supplier.purchase-orders.*,
       supplier.messages.*, supplier.tickets.*, supplier.notifications.*,
       supplier.reviews.*, supplier.subscription.*, supplier.settings.*,
       supplier.company.*, supplier.members.*, supplier.invitations.*,
       supplier.roles.*, supplier.ownership.* — see routes/backend/supplier.php ── */
});

/* ── Public Frontend Marketplace ──
   Discovery + marketplace pages per docs/ai/workflows/frontend_workflow.md
   + docs/ai/design_frontend.md. See routes/frontend.php.
   Registered AFTER every reserved `/supplier/*` path above (backend dashboard,
   onboarding, pending/pricing/subscribe) — frontend.php declares a catch-all
   GET /supplier/{supplier:slug} public profile route, which would otherwise
   shadow single-segment dashboard routes like /supplier/opportunities,
   /supplier/awards, /supplier/pricing, etc. and 404 with a SupplierProfile
   ModelNotFoundException since Laravel matches routes in registration order. ── */
require __DIR__.'/frontend.php';

/* ── Stripe Webhook (no auth, no CSRF) ── */
Route::post('/stripe/webhook', [WebhookController::class, 'handle'])->name('stripe.webhook');
