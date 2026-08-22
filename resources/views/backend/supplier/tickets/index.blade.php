@extends('backend.layouts.supplier')

@section('title', 'Support Tickets')
@section('breadcrumb', 'Communication / Support Tickets')

@section('body')

    <x-backend.page-header title="Support Tickets" subtitle="Submit technical, billing, or marketplace queries to the EduShopify support team." />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- New ticket form --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Open a Support Ticket">
                <form method="POST" action="{{ route('supplier.tickets.store') }}" class="space-y-4">
                    @csrf
                    <x-backend.input name="subject" label="Subject" required placeholder="Brief description of the issue" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                            <option value="technical">Technical Issue</option>
                            <option value="billing">Billing &amp; Subscription</option>
                            <option value="verification">Verification &amp; Documents</option>
                            <option value="catalog">Catalog &amp; Listings</option>
                            <option value="other">Other Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                        <select name="priority" class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <x-backend.textarea name="message" label="Detailed Message" required placeholder="Describe your issue with all relevant details..." />

                    <button type="submit" class="btn-primary w-full text-sm font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 mt-4 shadow-sm">
                        <i class="fa-solid fa-paper-plane"></i> Submit Ticket
                    </button>
                </form>
            </x-backend.form-card>
        </div>

        {{-- Ticket List --}}
        <div class="xl:col-span-8 space-y-6">
            <x-backend.form-card title="My Support Tickets">
                @if($tickets->isEmpty())
                    <x-backend.empty-state icon="fa-life-ring" title="No support tickets" description="If you have questions or encounter issues, open a ticket on the left." />
                @else
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 bg-gray-50 border-y border-gray-100">
                                <tr>
                                    <th class="px-5 py-3.5 font-semibold">Subject</th>
                                    <th class="px-3 py-3.5 font-semibold">Category</th>
                                    <th class="px-3 py-3.5 font-semibold">Priority</th>
                                    <th class="px-3 py-3.5 font-semibold">Status</th>
                                    <th class="px-5 py-3.5 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @foreach($tickets as $ticket)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3.5">
                                            <a href="{{ route('supplier.tickets.show', $ticket) }}" class="font-bold text-gray-900 hover:text-indigo-600">
                                                {{ $ticket->subject }}
                                            </a>
                                            <p class="text-[10px] text-gray-400 mt-0.5">#{{ $ticket->id }} &middot; {{ $ticket->created_at->format('d M Y') }}</p>
                                        </td>
                                        <td class="px-3 py-3.5 uppercase text-[10px] text-gray-500">
                                            {{ $ticket->category }}
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $ticket->priority }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5">
                                            <x-backend.status-badge :status="$ticket->status" />
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('supplier.tickets.show', $ticket) }}" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg inline-block">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-backend.form-card>
        </div>

    </div>

@endsection
