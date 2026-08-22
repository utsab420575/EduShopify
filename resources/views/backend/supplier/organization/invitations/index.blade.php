@extends('backend.layouts.supplier')

@section('title', 'Invitations')
@section('breadcrumb', 'Organization / Invitations')

@section('body')

    <x-backend.page-header title="Invitations" subtitle="Invite new colleagues and employees to join your supplier account." />

    @if(session('inviteLink'))
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-bold text-indigo-900 mb-1">Direct Invitation Link Generated:</h4>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ session('inviteLink') }}" class="flex-1 text-xs border border-indigo-300 rounded-lg p-2 bg-white select-all font-mono">
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Send Invitation">
                <form method="POST" action="{{ route('supplier.invitations.store') }}" class="space-y-4">
                    @csrf
                    <x-backend.input type="email" name="invited_email" label="Colleague's Email" required placeholder="colleague@yourcompany.com" />
                    <x-backend.input name="invited_name" label="Colleague's Name (Optional)" placeholder="e.g. John Doe" />
                    <x-backend.input name="invited_phone" label="Phone (Optional)" />
                    <button type="submit" class="btn-primary w-full text-xs font-bold py-2.5 rounded-lg flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-paper-plane"></i> Send Invite
                    </button>
                </form>
            </x-backend.form-card>
        </div>

        <div class="xl:col-span-8 space-y-6">
            <x-backend.form-card title="Pending &amp; Sent Invitations">
                @if($invitations->isEmpty())
                    <x-backend.empty-state icon="fa-envelope-open" title="No invitations" description="Invite team members on the left to collaborate on quotations and listings." />
                @else
                    <div class="overflow-x-auto -mx-5 -mb-5">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 bg-gray-50 border-y border-gray-100">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Invited Email</th>
                                    <th class="px-3 py-3 font-semibold">Status</th>
                                    <th class="px-3 py-3 font-semibold">Expires</th>
                                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @foreach($invitations as $inv)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 font-medium text-gray-900">
                                            {{ $inv->invited_email }}
                                            @if($inv->invited_name) <span class="text-gray-400 font-normal">({{ $inv->invited_name }})</span> @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <x-backend.status-badge :status="$inv->status" />
                                        </td>
                                        <td class="px-3 py-3 text-gray-500">
                                            {{ $inv->expires_at?->format('d M Y') }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            @if($inv->status === 'pending')
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('supplier.invitations.resend', $inv) }}">
                                                        @csrf
                                                        <button type="submit" class="text-indigo-600 hover:underline text-[11px]">Resend Link</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('supplier.invitations.cancel', $inv) }}">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:underline text-[11px]">Cancel</button>
                                                    </form>
                                                </div>
                                            @endif
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
