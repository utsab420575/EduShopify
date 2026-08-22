<?php

namespace App\Http\Controllers\Backend\Supplier\Communication;

use App\Http\Controllers\Backend\Supplier\Concerns\InteractsWithSupplierAccount;
use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    use InteractsWithSupplierAccount;

    public function index(Request $request)
    {
        $account = $this->currentAccount();

        $inquiries = ContactInquiry::where('supplier_account_id', $account->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with('listing')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.supplier.communication.inquiries.index', [
            'inquiries'     => $inquiries,
            'status'        => $request->string('status')->toString(),
            'statusOptions' => [
                'new'      => 'New',
                'read'     => 'Read',
                'replied'  => 'Replied',
                'closed'   => 'Closed',
            ],
        ]);
    }

    public function show(ContactInquiry $inquiry)
    {
        abort_if($inquiry->supplier_account_id !== $this->currentAccount()->id, 403);

        if ($inquiry->status === 'new') {
            $inquiry->update(['status' => 'read']);
        }

        return view('backend.supplier.communication.inquiries.show', [
            'inquiry' => $inquiry,
        ]);
    }

    public function markReplied(ContactInquiry $inquiry)
    {
        abort_if($inquiry->supplier_account_id !== $this->currentAccount()->id, 403);

        $inquiry->update(['status' => 'replied', 'replied_at' => now()]);

        return back()->with('success', 'Inquiry marked as replied.');
    }

    public function close(ContactInquiry $inquiry)
    {
        abort_if($inquiry->supplier_account_id !== $this->currentAccount()->id, 403);

        $inquiry->update(['status' => 'closed', 'closed_at' => now()]);

        return back()->with('success', 'Inquiry closed.');
    }
}
