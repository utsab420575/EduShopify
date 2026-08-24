<?php

namespace App\Http\Controllers\Backend\Admin\Approval;

use App\Http\Controllers\Controller;
use App\Support\Approvals\ApprovalQueueRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Centralized, permission-aware work queue (spec Part 5). Purely aggregates
 * existing queues and routes to their canonical review screens — no
 * duplicate business data or decisions live here. Navigation lives in the
 * sidebar's Approval Center submenu; each queue has its own URL.
 */
class ApprovalCenterController extends Controller
{
    /** Redirects to the first queue the user can see (spec: /approvals has no content of its own). */
    public function index(Request $request): View|RedirectResponse
    {
<<<<<<< HEAD
        $queues = ApprovalQueueRegistry::forUser(Auth::user());
=======
        $user = Auth::user();
        $tab = $request->string('tab', 'listings')->toString();
>>>>>>> 65fab0cae4ad61d182eed68d9dbf650abbef22f7

        $firstKey = array_key_first($queues);

<<<<<<< HEAD
        if (! $firstKey) {
            return view('backend.admin.approvals.index', [
                'queues'      => [],
                'activeKey'   => null,
                'activeQueue' => null,
                'items'       => collect(),
            ]);
        }

        return redirect()->route('admin.approvals.show', $firstKey);
    }

    public function show(Request $request, string $queue): View
    {
        $queues = ApprovalQueueRegistry::forUser(Auth::user());

        abort_unless(array_key_exists($queue, $queues), 404);

        $items = ($queues[$queue]['query'])()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.approvals.index', [
            'queues'      => $queues,
            'activeKey'   => $queue,
            'activeQueue' => $queues[$queue],
            'items'       => $items,
=======
        if ($user->can('platform.listings.moderate')) {
            $queues['listings'] = [
                'label' => 'Product / Service Listings',
                'icon'  => 'fa-boxes-stacked',
                'count' => Listing::where('approval_status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.capabilities.review')) {
            $queues['capabilities'] = [
                'label' => 'Capability Applications',
                'icon'  => 'fa-award',
                'count' => AccountCapability::where('status', 'pending')->count(),
            ];
            $queues['documents'] = [
                'label' => 'Supplier Documents',
                'icon'  => 'fa-file-shield',
                'count' => SupplierDocument::where('status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.categories.manage')) {
            $queues['categories'] = [
                'label' => 'Category Suggestions',
                'icon'  => 'fa-folder-tree',
                'count' => CategorySuggestion::where('status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.attributes.manage')) {
            $queues['attributes'] = [
                'label' => 'Attribute Suggestions',
                'icon'  => 'fa-sliders',
                'count' => AttributeSuggestion::where('status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.brands.manage')) {
            $queues['brands'] = [
                'label' => 'Brand & Unit Requests',
                'icon'  => 'fa-tags',
                'count' => Brand::where('approval_status', 'pending')->count() + Unit::where('approval_status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.access_control.manage')) {
            $queues['role_requests'] = [
                'label' => 'Role Requests',
                'icon'  => 'fa-user-shield',
                'count' => RoleRequest::where('status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.conversions.review')) {
            $queues['conversions'] = [
                'label' => 'Account Conversions',
                'icon'  => 'fa-arrow-right-arrow-left',
                'count' => AccountConversionRequest::where('status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.reviews.moderate')) {
            $queues['reports'] = [
                'label' => 'Review Reports',
                'icon'  => 'fa-flag',
                'count' => ReviewReport::where('status', 'pending')->count(),
            ];
        }

        if (! array_key_exists($tab, $queues)) {
            $tab = array_key_first($queues) ?? 'listings';
        }

        $items = match ($tab) {
            'listings'     => Listing::where('approval_status', 'pending')
                                ->with(['supplierAccount.supplierProfile', 'mainCategory', 'media', 'variants'])
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'capabilities' => AccountCapability::where('status', 'pending')
                                ->with(['account', 'capabilityType', 'appliedBy'])
                                ->latest('applied_at')
                                ->paginate(15)
                                ->withQueryString(),
            'documents'    => SupplierDocument::where('status', 'pending')
                                ->with(['supplierAccount.supplierProfile', 'documentType'])
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'categories'   => CategorySuggestion::where('status', 'pending')
                                ->with('supplierAccount.supplierProfile')
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'attributes'   => AttributeSuggestion::where('status', 'pending')
                                ->with('supplierAccount.supplierProfile')
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'brands'       => Brand::where('approval_status', 'pending')
                                ->with('supplierAccount.supplierProfile')
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'role_requests' => RoleRequest::where('status', 'pending')
                                ->with(['account', 'requestedBy', 'requestedRole'])
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'conversions'  => AccountConversionRequest::where('status', 'pending')
                                ->with(['account', 'submittedBy'])
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            'reports'      => ReviewReport::where('status', 'pending')
                                ->with(['review', 'reportedByAccount'])
                                ->latest()
                                ->paginate(15)
                                ->withQueryString(),
            default        => collect(),
        };

        return view('backend.admin.approvals.index', [
            'queues' => $queues,
            'tab'    => $tab,
            'items'  => $items,
>>>>>>> 65fab0cae4ad61d182eed68d9dbf650abbef22f7
        ]);
    }
}
