@extends('backend.layouts.supplier')

@section('title', 'Contact Inquiries')
@section('breadcrumb', 'Communication / Contact Inquiries')

@section('body')

    <x-backend.page-header title="Contact Inquiries" subtitle="Inbound enquiries from buyers and visitors who contacted you through your public profile." />

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('supplier.contact-inquiries.index') }}" class="flex flex-wrap items-center gap-3">
            <select name="status" onchange="this.form.submit()"
                    class="text-sm rounded-lg border border-gray-300 px-3 py-2 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if($status)
                <a href="{{ route('supplier.contact-inquiries.index') }}" class="text-xs text-gray-500 hover:text-gray-700">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($inquiries->isEmpty())
            <div class="p-8 text-center">
                <x-backend.empty-state icon="fa-envelope-open-text" title="No inquiries found"
                    description="Inbound contact messages from your public supplier profile will appear here." />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Inquiry #</th>
                            <th class="px-3 py-3.5 font-semibold">From</th>
                            <th class="px-3 py-3.5 font-semibold">Subject</th>
                            <th class="px-3 py-3.5 font-semibold">Listing</th>
                            <th class="px-3 py-3.5 font-semibold">Received</th>
                            <th class="px-3 py-3.5 font-semibold">Status</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($inquiries as $inquiry)
                            <tr class="hover:bg-gray-50 {{ $inquiry->status === 'new' ? 'font-semibold' : '' }}">
                                <td class="px-5 py-3.5 text-xs font-mono text-gray-500">{{ $inquiry->inquiry_number }}</td>
                                <td class="px-3 py-3.5">
                                    <p class="text-sm font-medium text-gray-900">{{ $inquiry->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $inquiry->organization }}</p>
                                </td>
                                <td class="px-3 py-3.5 text-sm text-gray-700 max-w-xs truncate">{{ $inquiry->subject }}</td>
                                <td class="px-3 py-3.5 text-xs text-gray-500">
                                    {{ $inquiry->listing?->title ?? '—' }}
                                </td>
                                <td class="px-3 py-3.5 text-xs text-gray-400">{{ $inquiry->created_at->format('d M Y') }}</td>
                                <td class="px-3 py-3.5">
                                    <x-backend.status-badge :status="$inquiry->status" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('supplier.contact-inquiries.show', $inquiry) }}"
                                       class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($inquiries->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $inquiries->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection
