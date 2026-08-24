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
}
