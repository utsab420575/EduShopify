<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountCapability;
use App\Models\AccountConversionRequest;
use App\Models\Award;
use App\Models\AttributeSuggestion;
use App\Models\Brand;
use App\Models\CategorySuggestion;
use App\Models\Listing;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\Rfq;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SupplierDocument;
use App\Models\Ticket;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->can('platform.accounts.view')) {
            $data['userMetrics'] = [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'pending_verification' => User::where('status', 'pending_verification')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
            ];

            $data['accountMetrics'] = [
                'total' => Account::where('is_system_account', false)->count(),
                'individual' => Account::where('is_system_account', false)->where('account_type', 'individual')->count(),
                'organization' => Account::where('is_system_account', false)->where('account_type', 'organization')->count(),
                'pending_approval' => Account::where('status', 'pending_approval')->count(),
                'suspended' => Account::where('status', 'suspended')->count(),
                'deletion_pending' => Account::where('status', 'deletion_pending')->count(),
            ];
        }

        if ($user->can('platform.capabilities.review')) {
            $data['capabilityMetrics'] = [
                'buyer_active' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'buyer'))->where('status', 'active')->count(),
                'buyer_pending' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'buyer'))->where('status', 'pending')->count(),
                'buyer_revision' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'buyer'))->where('status', 'revision_required')->count(),
                'buyer_suspended' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'buyer'))->where('status', 'suspended')->count(),
                'supplier_active' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->where('status', 'active')->count(),
                'supplier_pending' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->where('status', 'pending')->count(),
                'supplier_revision' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->where('status', 'revision_required')->count(),
                'supplier_rejected' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->where('status', 'rejected')->count(),
                'supplier_suspended' => AccountCapability::whereHas('capabilityType', fn ($q) => $q->where('code', 'supplier'))->where('status', 'suspended')->count(),
            ];
        }

        if ($user->can('platform.supplier_documents.verify')) {
            $data['documentMetrics'] = [
                'pending' => SupplierDocument::where('status', 'pending')->count(),
                'rejected' => SupplierDocument::where('status', 'rejected')->count(),
                'expiring_soon' => SupplierDocument::where('status', 'verified')->whereNotNull('expires_at')->whereBetween('expires_at', [now(), now()->addDays(30)])->count(),
            ];
        }

        if ($user->can('platform.listings.moderate') || $user->can('platform.categories.manage') || $user->can('platform.brands.manage') || $user->can('platform.attributes.manage')) {
            $data['catalogMetrics'] = [
                'listings_pending' => Listing::where('approval_status', 'pending')->count(),
                'listings_rejected' => Listing::where('approval_status', 'rejected')->count(),
                'listings_approved' => Listing::where('approval_status', 'approved')->count(),
                'category_suggestions' => CategorySuggestion::where('status', 'pending')->count(),
                'attribute_suggestions' => AttributeSuggestion::where('status', 'pending')->count(),
                'brand_requests' => Brand::where('approval_status', 'pending')->count(),
                'unit_requests' => Unit::where('approval_status', 'pending')->count(),
            ];
        }

        if ($user->can('platform.rfqs.moderate')) {
            $rfqRequiresApproval = \App\Models\Setting::get('rfq', 'rfq_requires_admin_approval', false);

            $data['procurementMetrics'] = [
                'open_rfqs' => Rfq::where('status', 'open')->count(),
                'rfqs_pending_approval' => $rfqRequiresApproval ? Rfq::where('status', 'pending_approval')->count() : 0,
                'quotations_submitted' => Quotation::where('status', 'submitted')->count(),
                'revision_requests_pending' => \App\Models\QuotationRevisionRequest::where('status', 'pending')->count(),
                'awards_awaiting_response' => Award::where('status', 'pending_supplier_response')->count(),
                'awards_accepted' => Award::where('status', 'accepted')->count(),
                'po_issued' => PurchaseOrder::where('status', 'issued')->count(),
                'po_completed' => PurchaseOrder::where('status', 'completed')->count(),
                'po_cancelled_disputed' => PurchaseOrder::whereIn('status', ['cancelled', 'disputed'])->count(),
            ];
        }

        if ($user->can('platform.subscriptions.manage')) {
            $data['billingMetrics'] = [
                'active' => Subscription::where('status', 'active')->count(),
                'trialing' => Subscription::where('status', 'trialing')->count(),
                'pending' => Subscription::where('status', 'pending')->count(),
                'past_due' => Subscription::where('status', 'past_due')->count(),
                'expired' => Subscription::where('status', 'expired')->count(),
                'suspended' => Subscription::where('status', 'suspended')->count(),
                'recent_paid' => SubscriptionPayment::where('status', 'paid')->where('paid_at', '>=', now()->subDays(7))->count(),
                'failed' => SubscriptionPayment::where('status', 'failed')->count(),
            ];
        }

        if ($user->can('platform.tickets.manage') || $user->can('platform.reviews.moderate') || $user->can('platform.communication.manage')) {
            $data['supportMetrics'] = [
                'open_tickets' => Ticket::whereNotIn('status', ['resolved', 'closed'])->count(),
                'unassigned_tickets' => Ticket::whereNotIn('status', ['resolved', 'closed'])->whereNull('assigned_admin_user_id')->count(),
                'new_inquiries' => \App\Models\ContactInquiry::where('status', 'new')->count(),
                'reviews_pending' => Review::where('status', 'pending')->count(),
                'reviews_flagged' => Review::where('status', 'flagged')->count(),
                'reports_pending' => ReviewReport::where('status', 'pending')->count(),
            ];
        }

        $data['conversionsPending'] = $user->can('platform.conversions.review') ? AccountConversionRequest::where('status', 'pending')->count() : 0;
        $data['roleRequestsPending'] = $user->can('platform.access_control.manage') ? \App\Models\RoleRequest::where('status', 'pending')->count() : 0;

        $data['recentActivity'] = $user->can('platform.activity_logs.view')
            ? \Spatie\Activitylog\Models\Activity::with('causer')->latest()->limit(10)->get()
            : collect();

        return view('backend.admin.dashboard.index', $data);
    }
}
