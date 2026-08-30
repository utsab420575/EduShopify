<?php

namespace App\Support\Approvals;

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
use App\Models\User;

/**
 * Single source of truth for the Approval Center's queues (label, icon,
 * gating permission, pending-count query, listing query). Shared by the
 * sidebar composer (badge counts) and the controller (routing + listing)
 * so the two can't drift out of sync.
 */
class ApprovalQueueRegistry
{
    public static function definitions(): array
    {
        return [
            'listings' => [
                'label'      => 'Product / Service Listings',
                'icon'       => 'fa-boxes-stacked',
                'permission' => 'platform.listings.moderate',
                'count'      => fn () => Listing::where('approval_status', 'pending')->count(),
                'query'      => fn () => Listing::where('approval_status', 'pending')
                                    ->with(['supplierAccount.supplierProfile', 'mainCategory', 'media', 'variants'])
                                    ->latest(),
            ],
            'capabilities' => [
                'label'      => 'Capability Applications',
                'icon'       => 'fa-award',
                'permission' => 'platform.capabilities.review',
                'count'      => fn () => AccountCapability::where('status', 'pending')->count(),
                'query'      => fn () => AccountCapability::where('status', 'pending')
                                    ->with(['account', 'capabilityType', 'appliedBy'])
                                    ->latest('applied_at'),
            ],
            'documents' => [
                'label'      => 'Supplier Documents',
                'icon'       => 'fa-file-shield',
                'permission' => 'platform.capabilities.review',
                'count'      => fn () => SupplierDocument::where('status', 'pending')->count(),
                'query'      => fn () => SupplierDocument::where('status', 'pending')
                                    ->with(['supplierAccount.supplierProfile', 'documentType', 'uploadedBy'])
                                    ->latest(),
            ],
            'categories' => [
                'label'      => 'Category Suggestions',
                'icon'       => 'fa-folder-tree',
                'permission' => 'platform.categories.manage',
                'count'      => fn () => CategorySuggestion::where('status', 'pending')->count(),
                'query'      => fn () => CategorySuggestion::where('status', 'pending')
                                    ->with('supplierAccount.supplierProfile')
                                    ->latest(),
            ],
            'attributes' => [
                'label'      => 'Attribute Suggestions',
                'icon'       => 'fa-sliders',
                'permission' => 'platform.attributes.manage',
                'count'      => fn () => AttributeSuggestion::where('status', 'pending')->count(),
                'query'      => fn () => AttributeSuggestion::where('status', 'pending')
                                    ->with('supplierAccount.supplierProfile')
                                    ->latest(),
            ],
            'custom_attribute_values' => [
                'label'      => 'Custom Attribute Values',
                'icon'       => 'fa-tag',
                'permission' => 'platform.attributes.manage',
                'count'      => fn () => CustomAttributeValueQueue::pendingCount(),
                'query'      => fn () => CustomAttributeValueQueue::query(),
            ],
            'brands' => [
                'label'      => 'Brand & Unit Requests',
                'icon'       => 'fa-tags',
                'permission' => 'platform.brands.manage',
                'count'      => fn () => Brand::where('approval_status', 'pending')->count() + Unit::where('approval_status', 'pending')->count(),
                'query'      => fn () => Brand::where('approval_status', 'pending')
                                    ->with('supplierAccount.supplierProfile')
                                    ->latest(),
            ],
            'role_requests' => [
                'label'      => 'Role Requests',
                'icon'       => 'fa-user-shield',
                'permission' => 'platform.access_control.manage',
                'count'      => fn () => RoleRequest::where('status', 'pending')->count(),
                'query'      => fn () => RoleRequest::where('status', 'pending')
                                    ->with(['account', 'requestedBy', 'requestedRole'])
                                    ->latest(),
            ],
            'conversions' => [
                'label'      => 'Account Conversions',
                'icon'       => 'fa-arrow-right-arrow-left',
                'permission' => 'platform.conversions.review',
                'count'      => fn () => AccountConversionRequest::where('status', 'pending')->count(),
                'query'      => fn () => AccountConversionRequest::where('status', 'pending')
                                    ->with(['account', 'submittedBy'])
                                    ->latest(),
            ],
            'reports' => [
                'label'      => 'Review Reports',
                'icon'       => 'fa-flag',
                'permission' => 'platform.reviews.moderate',
                'count'      => fn () => ReviewReport::where('status', 'pending')->count(),
                'query'      => fn () => ReviewReport::where('status', 'pending')
                                    ->with(['review', 'reportedByAccount'])
                                    ->latest(),
            ],
        ];
    }

    /** Queue definitions the given user is permitted to see, in display order. */
    public static function forUser(User $user): array
    {
        return array_filter(
            static::definitions(),
            fn (array $queue) => $user->can($queue['permission'])
        );
    }
}
