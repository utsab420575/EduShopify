<?php

namespace App\Http\Controllers\Backend\Admin\Approval;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Support\Approvals\ApprovalQueueRegistry;
use App\Support\Approvals\CustomAttributeValueQueue;
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
        $queues = ApprovalQueueRegistry::forUser(Auth::user());

        $firstKey = array_key_first($queues);

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

        // This queue has two related, independently-paginated lists (pending
        // vs. decided) plus a duplicate-check modal — it outgrew the generic
        // single-list table, so it gets its own view rather than being
        // forced through the shared @switch in approvals/index.blade.php.
        if ($queue === 'custom_attribute_values') {
            return $this->showCustomAttributeValues($request, $queues);
        }

        $items = ($queues[$queue]['query'])()
            ->paginate(15)
            ->withQueryString();

        return view('backend.admin.approvals.index', [
            'queues'      => $queues,
            'activeKey'   => $queue,
            'activeQueue' => $queues[$queue],
            'items'       => $items,
        ]);
    }

    private function showCustomAttributeValues(Request $request, array $queues): View
    {
        $pendingSearch = trim((string) $request->string('pending_search'));
        $pendingSort = $request->string('pending_sort')->toString();
        $pendingSort = in_array($pendingSort, CustomAttributeValueQueue::PENDING_SORTS, true) ? $pendingSort : '';
        $pendingDirection = $request->string('pending_direction')->toString() === 'asc' ? 'asc' : 'desc';

        $decidedSearch = trim((string) $request->string('decided_search'));
        $decidedSort = $request->string('decided_sort')->toString();
        $decidedSort = in_array($decidedSort, CustomAttributeValueQueue::DECIDED_SORTS, true) ? $decidedSort : '';
        $decidedDirection = $request->string('decided_direction')->toString() === 'desc' ? 'desc' : 'asc';
        $decidedStatus = $request->string('decided_status')->toString();
        $decidedStatus = in_array($decidedStatus, ['ignored', 'approved'], true) ? $decidedStatus : '';

        $pending = CustomAttributeValueQueue::query($pendingSearch, $pendingSort, $pendingDirection)
            ->paginate(10, ['*'], 'pending_page')
            ->withQueryString();

        $decided = CustomAttributeValueQueue::withUsageCounts(
            CustomAttributeValueQueue::decidedQuery($decidedSearch, $decidedSort, $decidedDirection, $decidedStatus)
                ->paginate(10, ['*'], 'decided_page')
                ->withQueryString()
        );

        // Existing values for every attribute referenced on this page, for
        // the "does something like this already exist?" duplicate-check
        // modal shown before an admin approves/promotes a value.
        $attributeIds = collect($pending->items())->pluck('attribute_id')
            ->merge(collect($decided->items())->pluck('attribute_id'))
            ->unique();

        $existingValuesByAttribute = AttributeValue::whereIn('attribute_id', $attributeIds)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('attribute_id')
            ->map(fn ($values) => $values->pluck('value')->values());

        return view('backend.admin.approvals.custom-attribute-values', [
            'queues'                    => $queues,
            'activeKey'                 => 'custom_attribute_values',
            'activeQueue'               => $queues['custom_attribute_values'],
            'pending'                   => $pending,
            'decided'                   => $decided,
            'existingValuesByAttribute' => $existingValuesByAttribute,
            'pendingSearch'             => $pendingSearch,
            'pendingSort'               => $pendingSort,
            'pendingDirection'          => $pendingDirection,
            'decidedSearch'             => $decidedSearch,
            'decidedSort'               => $decidedSort,
            'decidedDirection'          => $decidedDirection,
            'decidedStatus'             => $decidedStatus,
        ]);
    }
}
