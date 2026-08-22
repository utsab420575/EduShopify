<?php

namespace App\Http\Controllers\Backend\Admin\Communication;

use App\Http\Controllers\Backend\Admin\Concerns\InteractsWithAdmin;
use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    use InteractsWithAdmin;

    public function index(Request $request)
    {
        $this->authorize('platform.communication.manage');

        $inquiries = ContactInquiry::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('email', 'like', '%'.$request->string('search').'%')
                    ->orWhere('subject', 'like', '%'.$request->string('search').'%');
            }))
            ->with('supplierAccount.supplierProfile')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.admin.communication.inquiries.index', [
            'inquiries' => $inquiries,
            'status' => $request->string('status')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(ContactInquiry $inquiry)
    {
        $this->authorize('platform.communication.manage');

        if ($inquiry->status === 'new') {
            $inquiry->update(['status' => 'read', 'handled_by_user_id' => $this->admin()->id, 'handled_at' => now()]);
        }

        $inquiry->load('supplierAccount.supplierProfile', 'listing');

        return view('backend.admin.communication.inquiries.show', ['inquiry' => $inquiry]);
    }

    public function updateStatus(Request $request, ContactInquiry $inquiry)
    {
        $this->authorize('platform.communication.manage');

        $request->validate(['status' => ['required', 'in:new,read,replied,closed']]);

        $inquiry->update([
            'status' => $request->string('status'),
            'handled_by_user_id' => $this->admin()->id,
            'handled_at' => $inquiry->handled_at ?? now(),
            'replied_at' => $request->string('status') === 'replied' ? now() : $inquiry->replied_at,
            'closed_at' => $request->string('status') === 'closed' ? now() : $inquiry->closed_at,
        ]);

        return back()->with('success', 'Inquiry status updated.');
    }
}
