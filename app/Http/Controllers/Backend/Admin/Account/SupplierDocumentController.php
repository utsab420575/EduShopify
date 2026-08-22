<?php

namespace App\Http\Controllers\Backend\Admin\Account;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\SupplierDocument;
use App\Services\SupplierDocumentService;
use Illuminate\Http\Request;

class SupplierDocumentController extends Controller
{
    use InteractsWithAdmin;

    public function verify(Account $account, SupplierDocument $document, SupplierDocumentService $service)
    {
        $this->authorize('platform.supplier_documents.verify');
        abort_unless($document->supplier_account_id === $account->id, 404);

        $service->verify($document, $this->admin());

        activity('moderation')->causedBy($this->admin())->performedOn($document)->log('Supplier document verified');

        return back()->with('success', 'Document verified.');
    }

    public function reject(Request $request, Account $account, SupplierDocument $document, SupplierDocumentService $service)
    {
        $this->authorize('platform.supplier_documents.verify');
        abort_unless($document->supplier_account_id === $account->id, 404);

        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $service->reject($document, $this->admin(), $request->string('reason'));

        activity('moderation')->causedBy($this->admin())->performedOn($document)
            ->withProperties(['reason' => $request->string('reason')])
            ->log('Supplier document rejected');

        return back()->with('success', 'Document rejected.');
    }
}
