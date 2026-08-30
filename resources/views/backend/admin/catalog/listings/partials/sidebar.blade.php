{{-- Supplier Organization + Moderation & Audit Timeline. See listings/_panel.blade.php for expected variables. --}}
<x-backend.form-card title="Supplier Organization">
    @if($listing->supplierAccount)
        @php($profile = $listing->supplierAccount->supplierProfile)
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shadow-xs">
                    {{ strtoupper(substr($profile?->display_name ?? $listing->supplierAccount->display_name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $profile?->display_name ?? $listing->supplierAccount->display_name }}</p>
                    <p class="text-[11px] text-gray-500">{{ $profile?->country?->name ?? 'International Supplier' }}</p>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100 space-y-1.5 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span class="text-gray-400">Account ID:</span>
                    <span class="font-mono font-medium text-gray-800">#{{ $listing->supplierAccount->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Status:</span>
                    <span class="font-semibold text-emerald-600">{{ ucfirst($listing->supplierAccount->status ?? 'Active') }}</span>
                </div>
            </div>

            <a href="{{ route('admin.suppliers.show', $listing->supplierAccount) }}" class="btn-primary w-full text-center text-xs font-semibold py-2 rounded-lg block">
                View Supplier Account Profile &rarr;
            </a>
        </div>
    @else
        <p class="text-xs text-gray-400">No supplier account linked.</p>
    @endif
</x-backend.form-card>

<x-backend.form-card title="Moderation & Audit Timeline">
    <dl class="space-y-2.5 text-xs">
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-400">Created Date</span>
            <span class="font-medium text-gray-800">{{ $listing->created_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-400">Last Updated</span>
            <span class="font-medium text-gray-800">{{ $listing->updated_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-400">Published Date</span>
            <span class="font-medium text-gray-800">{{ $listing->published_at ? $listing->published_at->format('d M Y') : 'Not Published' }}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-gray-100">
            <span class="text-gray-400">Approved By</span>
            <span class="font-medium text-gray-800">{{ $listing->approvedBy?->name ?? '—' }}</span>
        </div>
        @if($listing->approved_at)
            <div class="flex justify-between py-1 border-b border-gray-100">
                <span class="text-gray-400">Approved At</span>
                <span class="font-medium text-gray-800">{{ $listing->approved_at->format('d M Y, h:i A') }}</span>
            </div>
        @endif
    </dl>

    @if($listing->rejection_reason)
        <div class="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs">
            <p class="font-bold text-rose-800 mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Rejection / Suspension Reason:</p>
            <p class="text-rose-700 leading-relaxed">{{ $listing->rejection_reason }}</p>
        </div>
    @endif
</x-backend.form-card>
