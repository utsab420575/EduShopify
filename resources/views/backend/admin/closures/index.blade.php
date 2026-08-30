@extends('backend.layouts.admin')

@section('title', 'Account Closures')
@section('breadcrumb', 'Users & Accounts / Closures')

@section('body')

    <x-backend.page-header title="Account Closures" subtitle="Accounts awaiting deletion — review dependencies before finalizing." />

    <x-backend.table>
        @if($accounts->isEmpty())
            <x-slot:empty><x-backend.empty-state icon="fa-trash-can" title="No pending closures" /></x-slot:empty>
        @else
            <x-slot:head>
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested</th>
                    <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </x-slot:head>
            @foreach($accounts as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        <p class="text-sm font-medium text-gray-900">{{ $account->display_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $account->account_number }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $account->primaryOwner?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-sm text-gray-600">{{ $account->deletion_requested_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            {{-- Quick View Modal Button --}}
                            <button type="button"
                                    @click="$dispatch('open-modal-view-closure-{{ $account->id }}')"
                                    title="Quick Inspect"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            {{-- Finalize Closure --}}
                            <form method="POST" action="{{ route('admin.closures.finalize', $account) }}" onsubmit="return confirmSwal(this, 'Finalize Account Closure?', 'Permanently close and delete {{ addslashes($account->display_name) }}?', 'warning', 'Yes, Finalize Closure')">
                                @csrf
                                <button type="submit" title="Finalize Closure" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            </form>

                            {{-- Put on Hold --}}
                            <button type="button"
                                    title="Put on Hold"
                                    @click="$dispatch('open-modal-hold-closure-{{ $account->id }}')"
                                    class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-amber-600 hover:bg-amber-50 transition-colors">
                                <i class="fa-solid fa-pause"></i>
                            </button>

                            {{-- Direct Show Link --}}
                            <a href="{{ route('admin.closures.show', $account) }}"
                               title="Full Closure Review"
                               class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors">
                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif

        <x-slot:pagination>
            <x-backend.pagination :paginator="$accounts" />
        </x-slot:pagination>
    </x-backend.table>

    {{-- Modals for Closures --}}
    @foreach($accounts as $account)
        {{-- Quick View Modal --}}
        <x-backend.modal :id="'view-closure-'.$account->id" :title="'Closure Request — '.$account->display_name" width="max-w-2xl">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h4 class="text-base font-bold text-gray-900">{{ $account->display_name }}</h4>
                        <p class="text-xs text-red-600 font-medium font-mono">Account No: {{ $account->account_number }}</p>
                    </div>
                    <a href="{{ route('admin.closures.show', $account) }}"
                       class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors"
                       title="Open full page view in current tab">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i> See in page &rarr;
                    </a>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-xs text-gray-500">Primary Owner</dt><dd class="font-medium text-gray-900">{{ $account->primaryOwner?->name ?? '—' }} ({{ $account->primaryOwner?->email ?? '—' }})</dd></div>
                    <div><dt class="text-xs text-gray-500">Requested Date</dt><dd class="font-medium text-gray-900">{{ $account->deletion_requested_at?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Account Type</dt><dd class="font-medium text-gray-900">{{ ucfirst($account->account_type) }}</dd></div>
                    <div><dt class="text-xs text-gray-500">Current Status</dt><dd><x-backend.status-badge :status="$account->status" /></dd></div>
                </dl>
            </div>
        </x-backend.modal>

        {{-- Hold Closure Modal --}}
        <x-backend.modal :id="'hold-closure-'.$account->id" title="Put Closure on Hold">
            <form method="POST" action="{{ route('admin.closures.hold', $account) }}" class="space-y-4">
                @csrf
                <x-backend.textarea name="reason" label="Reason for putting on hold" placeholder="State why this closure request is being held..." required />
                <div class="flex justify-end gap-2">
                    <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Confirm Hold</button>
                </div>
            </form>
        </x-backend.modal>
    @endforeach

@endsection
