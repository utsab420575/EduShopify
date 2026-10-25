<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\ReorderServicesRequest;
use App\Http\Requests\Backend\Supplier\Company\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreServiceRequest $request)
    {
        $data = $request->validated();
        $account = $this->currentAccount();

        // No explicit order → append to the end of the drag-sorted list.
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        if ($sortOrder <= 0) {
            $sortOrder = (int) $account->services()->max('sort_order') + 1;
        }

        $account->services()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'icon_id' => $data['icon_id'] ?? null,
            'sort_order' => $sortOrder,
        ]);

        return redirect()->supplierRoute('company.profile', ['section' => 'services'])
            ->with('success', 'Service added.');
    }

    public function update(StoreServiceRequest $request, Service $service)
    {
        abort_unless($this->currentAccount()->services()->whereKey($service->id)->exists(), 404);

        $data = $request->validated();

        $service->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'icon_id' => $data['icon_id'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->supplierRoute('company.profile', ['section' => 'services'])
            ->with('success', 'Service updated.');
    }

    /**
     * Drag-and-drop reorder (AJAX). Receives every service id top to bottom
     * and rewrites sort_order as 1..n. Rejected unless the ids are exactly
     * this account's services, so a stale or tampered list can't reorder
     * (or leave gaps in) anyone else's.
     */
    public function reorder(ReorderServicesRequest $request): JsonResponse
    {
        $ids = array_map('intval', $request->validated('ids'));
        $services = $this->currentAccount()->services();

        $ownedIds = (clone $services)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submitted = collect($ids)->sort()->values()->all();

        if ($ownedIds !== $submitted) {
            return response()->json(['message' => 'The service list has changed. Please reload the page and try again.'], 422);
        }

        DB::transaction(function () use ($ids, $services) {
            foreach ($ids as $position => $id) {
                (clone $services)->whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        return response()->json([
            'message' => 'Service order saved.',
            'order' => collect($ids)->mapWithKeys(fn ($id, $position) => [$id => $position + 1]),
        ]);
    }

    public function destroy(Service $service)
    {
        abort_unless($this->currentAccount()->services()->whereKey($service->id)->exists(), 404);

        $service->delete();

        return redirect()->supplierRoute('company.profile', ['section' => 'services'])
            ->with('success', 'Service removed.');
    }
}
