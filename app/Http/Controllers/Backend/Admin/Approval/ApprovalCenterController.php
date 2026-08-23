<?php

namespace App\Http\Controllers\Backend\Admin\Approval;

use App\Http\Controllers\Controller;
use App\Models\AccountCapability;
use App\Models\AccountConversionRequest;
use App\Models\AttributeSuggestion;
use App\Models\Brand;
use App\Models\CategorySuggestion;
use App\Models\Listing;
use App\Models\ReviewReport;
use App\Models\RoleRequest;
use App\Models\SupplierDocument;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Centralized, permission-aware work queue (spec Part 5). Purely aggregates
 * existing queues and routes to their canonical review screens — no
 * duplicate business data or decisions live here.
 */
class ApprovalCenterController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->string('tab', 'listings')->toString();

        $queues = [];

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
        ]);
    }
}
