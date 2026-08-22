@extends('backend.layouts.buyer')

@section('title', 'Invitations')
@section('breadcrumb', 'Organization / Invitations')

@section('body')

    @if(session('inviteLink'))
        <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 mb-6" x-data="{ copied: false }">
            <i class="fa-solid fa-link mt-0.5"></i>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">Share this invitation link — it's shown only once.</p>
                <p class="text-xs truncate" x-ref="link">{{ session('inviteLink') }}</p>
            </div>
            <button type="button" @click="navigator.clipboard.writeText($refs.link.innerText); copied = true; setTimeout(() => copied = false, 2000)"
                    class="text-xs font-medium px-3 py-1.5 rounded-lg border border-blue-300 hover:bg-blue-100 whitespace-nowrap">
                <span x-show="!copied">Copy Link</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
        </div>
    @endif

    <div x-data="{ showForm: false }">
        <x-backend.page-header title="Invitations" subtitle="Invite people to join your organization.">
            <x-slot:actions>
                <button @click="showForm = !showForm" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Invite Member
                </button>
            </x-slot:actions>
        </x-backend.page-header>

        <div x-show="showForm" x-transition x-cloak class="mb-6">
            <x-backend.form-card title="New Invitation">
                <form method="POST" action="{{ route('buyer.invitations.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @csrf
                    <x-backend.input name="invited_email" type="email" label="Email" required />
                    <x-backend.input name="invited_name" label="Name (optional)" />
                    <x-backend.input name="invited_phone" label="Phone (optional)" />
                    <div class="sm:col-span-3 flex justify-end gap-2">
                        <button type="button" @click="showForm = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send Invitation</button>
                    </div>
                </form>
            </x-backend.form-card>
        </div>
    </div>

    <x-backend.table>
        @if($invitations->isEmpty())
            <x-slot:empty>
                <x-backend.empty-state icon="fa-envelope" title="No invitations sent yet" />
            </x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Invitee</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Invited</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expires</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($invitations as $invitation)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $invitation->invited_email }}</p>
                        @if($invitation->invited_name)<p class="text-xs text-gray-400">{{ $invitation->invited_name }}</p>@endif
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $invitation->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $invitation->expires_at?->format('d M Y') }}</td>
                    <td class="px-5 py-3.5"><x-backend.status-badge :status="$invitation->status" /></td>
                    <td class="px-5 py-3.5 text-right">
                        @if($invitation->status === 'pending')
                            <div class="flex items-center justify-end gap-1.5">
                                <form method="POST" action="{{ route('buyer.invitations.resend', $invitation) }}">
                                    @csrf
                                    <button type="submit" title="Resend" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100"><i class="fa-solid fa-paper-plane"></i></button>
                                </form>
                                <form method="POST" action="{{ route('buyer.invitations.cancel', $invitation) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Cancel" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50"><i class="fa-solid fa-xmark"></i></button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        @endif
    </x-backend.table>

@endsection
