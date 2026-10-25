<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Support\SupplierWorkspace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel → manage a supplier's Business Profile and Catalog listings
 * (/admin/suppliers/{supplierAccount}/manage/...), reusing the Supplier
 * workspace controllers and views.
 *
 * Runs inside the admin route group (EnsurePlatformAdmin already passed) and
 * additionally requires platform.suppliers.manage. It points the request's
 * SupplierWorkspace at that account, so InteractsWithSupplierAccount
 * resolves to it, supplier_route() builds admin URLs, and pages render in the
 * admin layout.
 */
class ManageSupplierAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->can('platform.suppliers.manage'), 403, 'You do not have permission to manage supplier accounts.');

        $account = Account::with(['capabilities.capabilityType', 'supplierProfile'])
            ->findOrFail((int) $request->route('supplierAccount'));

        abort_unless($account->hasCapability('supplier'), 404, 'This account is not a supplier.');

        app(SupplierWorkspace::class)->manage($account);

        // Every supplier_route() on this request fills {supplierAccount} itself…
        URL::defaults(['supplierAccount' => $account->id]);

        // …and the shared controllers never receive it as an extra argument
        // (their signatures are the supplier ones, e.g. update($request, Service $service)).
        $request->route()->forgetParameter('supplierAccount');

        return $next($request);
    }
}
