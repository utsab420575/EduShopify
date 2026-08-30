<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Award;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\Setting;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\BuyerLayoutComposer;
use App\View\Composers\FrontendLayoutComposer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerMorphMap();

        // Enterprise RBAC Gate:
        // 1. Super Admin: Universal platform root bypass
        // 2. Tenant Owners: Full bypass for tenant-scoped abilities (cannot access platform.*)
        // 3. Employees / Staff: Must have explicit role-based or direct permissions
        Gate::before(function ($user, $ability) {
            // Root Platform Super Admin
            if ($user->hasRole('super_admin') || ($user->accountMember?->account?->is_system_account && $user->accountMember?->is_primary_owner)) {
                return true;
            }

            // Tenant Company Owner (Supplier Owner or Buyer Owner)
            if ($user->accountMember?->isOwner()) {
                // Tenant Owners get full access to non-platform tenant abilities
                if (! str_starts_with($ability, 'platform.')) {
                    return true;
                }
            }

            // Employees and other staff fall through to standard Spatie role/permission checks
            return null;
        });

        // Registered on both the layout itself and every Buyer page: Blade
        // evaluates a child view's own @section content in the child's own
        // variable scope, before the parent layout (and its composer) ever
        // renders — so the layout-only registration left $user/$account
        // undefined inside page bodies even though the sidebar/topbar had them.
        View::composer(['backend.layouts.buyer', 'backend.buyer.*'], BuyerLayoutComposer::class);
        View::composer(['backend.layouts.supplier', 'backend.supplier.*'], \App\View\Composers\SupplierLayoutComposer::class);
        View::composer(['backend.layouts.admin', 'backend.admin.*'], AdminLayoutComposer::class);
        View::composer('backend.communication.*', function ($view) {
            $user = Auth::user();
            if ($user?->isSupplier()) {
                (new \App\View\Composers\SupplierLayoutComposer)->compose($view);
            } elseif ($user?->isAdmin()) {
                (new AdminLayoutComposer)->compose($view);
            } else {
                (new BuyerLayoutComposer)->compose($view);
            }
        });

        // Runtime theme (design.md §0.4-0.6): CSS variables read from
        // settings('theme', ...), editable by Admin at System & Settings ›
        // Theme, applied across every backend portal via the shared master.
        View::composer('backend.layouts.master', function ($view) {
            $view->with('themeSettings', Setting::group('theme'));
        });

        View::composer('frontend.layouts.master', FrontendLayoutComposer::class);

        // Public frontend components live under resources/views/frontend/components/
        // (frontend_workflow.md Part 19), not the default components/ root, so they
        // need an explicit anonymous-component namespace: <x-frontend::marketplace.listing-card>.
        Blade::anonymousComponentNamespace('frontend.components', 'frontend');
    }

    /**
     * Some tables store a polymorphic subject as a short alias rather than a
     * class name, because the column is a database enum:
     *
     *   conversations.context_type  enum('rfq','quotation','listing','purchase_order','general','support')
     *   saved_items.item_type       enum('listing','supplier','rfq','quotation')
     *   tickets.related_type        varchar (rfq / quotation / award / purchase_order)
     *
     * This map lets Eloquent resolve those aliases to models.
     *
     * Notes:
     *  - morphMap, not enforceMorphMap: activity_log, media and notifications
     *    morph to models that are deliberately not listed here, and enforcing
     *    would throw for them.
     *  - 'general' and 'support' conversations carry no subject, so they are
     *    absent on purpose; context() resolves to null for those rows.
     *  - Account is listed twice on purpose. saved_items.item_type uses the
     *    alias 'supplier', so it must resolve on read; but getMorphClass()
     *    returns the FIRST key matching the class, so 'account' is listed first
     *    and is what gets written everywhere else.
     */
    protected function registerMorphMap(): void
    {
        Relation::morphMap([
            'rfq'            => Rfq::class,
            'quotation'      => Quotation::class,
            'listing'        => Listing::class,
            'purchase_order' => PurchaseOrder::class,
            'award'          => Award::class,
            'account'        => Account::class,
            'supplier'       => Account::class,
        ]);
    }
}
