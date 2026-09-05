<?php

namespace App\Http\Controllers\Backend\Supplier\Company;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\Supplier\Company\StoreServiceRequest;
use App\Models\Service;

class ServiceController extends Controller
{
    use InteractsWithSupplierAccount;

    public function store(StoreServiceRequest $request)
    {
        $data = $request->validated();

        $this->currentAccount()->services()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'icon_id' => $data['icon_id'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return redirect()->route('supplier.company.profile', ['section' => 'services'])
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

        return redirect()->route('supplier.company.profile', ['section' => 'services'])
            ->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        abort_unless($this->currentAccount()->services()->whereKey($service->id)->exists(), 404);

        $service->delete();

        return redirect()->route('supplier.company.profile', ['section' => 'services'])
            ->with('success', 'Service removed.');
    }
}
