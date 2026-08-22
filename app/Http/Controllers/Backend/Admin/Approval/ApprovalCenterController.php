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
        $tab = $request->string('tab', 'capabilities')->toString();

        $queues = [];

        if ($user->can('platform.capabilities.review')) {
            $queues['capabilities'] = [
                'label' => 'Capability Applications',
                'count' => AccountCapability::where('status', 'pending')->count(),
            ];
            $queues['documents'] = [
                'label' => 'Supplier Documents',
                'count' => SupplierDocument::where('status', 'pending')->count(),
            ];
        }
        if ($user->can('platform.listings.moderate')) {
            $queues['listings'] = ['label' => 'Listings', 'count' => Listing::where('approval_status', 'pending')->count()];
        }
        if ($user->can('platform.categories.manage')) {
            $queues['categories'] = ['label' => 'Category Suggestions', 'count' => CategorySuggestion::where('status', 'pending')->count()];
        }
        if ($user->can('platform.attributes.manage')) {
            $queues['attributes'] = ['label' => 'Attribute Suggestions', 'count' => AttributeSuggestion::where('status', 'pending')->count()];
        }
        if ($user->can('platform.brands.manage')) {
            $queues['brands'] = ['label' => 'Brand / Unit Requests', 'count' => Brand::where('approval_status', 'pending')->count() + Unit::where('approval_status', 'pending')->count()];
        }
        if ($user->can('platform.access_control.manage')) {
            $queues['role_requests'] = ['label' => 'Role Requests', 'count' => RoleRequest::where('status', 'pending')->count()];
        }
        if ($user->can('platform.conversions.review')) {
            $queues['conversions'] = ['label' => 'Account Conversions', 'count' => AccountConversionRequest::where('status', 'pending')->count()];
        }
        if ($user->can('platform.reviews.moderate')) {
            $queues['reports'] = ['label' => 'Review Reports', 'count' => ReviewReport::where('status', 'pending')->count()];
        }

        if (! array_key_exists($tab, $queues)) {
            $tab = array_key_first($queues) ?? 'capabilities';
        }

        $items = match ($tab) {
            'capabilities' => AccountCapability::where('status', 'pending')->with(['account', 'capabilityType'])->latest('applied_at')->limit(20)->get(),
            'documents' => SupplierDocument::where('status', 'pending')->with(['supplierAccount.supplierProfile', 'documentType'])->latest()->limit(20)->get(),
            'listings' => Listing::where('approval_status', 'pending')->with('supplierAccount.supplierProfile')->latest()->limit(20)->get(),
            'categories' => CategorySuggestion::where('status', 'pending')->with('supplierAccount.supplierProfile')->latest()->limit(20)->get(),
            'attributes' => AttributeSuggestion::where('status', 'pending')->with('supplierAccount.supplierProfile')->latest()->limit(20)->get(),
            'brands' => Brand::where('approval_status', 'pending')->with('supplierAccount.supplierProfile')->latest()->limit(20)->get(),
            'role_requests' => RoleRequest::where('status', 'pending')->with('account', 'requestedBy')->latest()->limit(20)->get(),
            'conversions' => AccountConversionRequest::where('status', 'pending')->with('account', 'submittedBy')->latest()->limit(20)->get(),
            'reports' => ReviewReport::where('status', 'pending')->with(['review', 'reportedByAccount'])->latest()->limit(20)->get(),
            default => collect(),
        };

        return view('backend.admin.approvals.index', [
            'queues' => $queues,
            'tab' => $tab,
            'items' => $items,
        ]);
    }
}
