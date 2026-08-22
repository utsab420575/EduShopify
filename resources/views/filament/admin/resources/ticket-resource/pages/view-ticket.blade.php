<x-filament-panels::page>

    <style>
    .tk-meta { display:flex; flex-wrap:wrap; gap:1.5rem; background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; }
    .dark .tk-meta { background:#0f172a; border-color:#1e293b; }
    .tk-meta-item { display:flex; flex-direction:column; gap:0.15rem; }
    .tk-meta-item .k { font-size:0.68rem; font-weight:700; letter-spacing:0.05em; text-transform:uppercase; color:#94a3b8; }
    .tk-meta-item .v { font-size:0.875rem; font-weight:600; color:#0f172a; }
    .dark .tk-meta-item .v { color:#f1f5f9; }

    .tk-thread { display:flex; flex-direction:column; gap:1rem; }
    .tk-msg { display:flex; gap:0.75rem; background:#fff; border:1px solid #e2e8f0; border-radius:0.75rem; padding:1rem 1.25rem; }
    .dark .tk-msg { background:#0f172a; border-color:#1e293b; }
    .tk-msg.staff { border-color:#c7d2fe; background:#eef2ff; }
    .dark .tk-msg.staff { background:rgba(99,102,241,0.08); border-color:rgba(99,102,241,0.3); }
    .tk-msg.internal { border-style:dashed; border-color:#fcd34d; background:#fffbeb; }
    .dark .tk-msg.internal { background:rgba(251,191,36,0.06); border-color:rgba(251,191,36,0.3); }
    .tk-avatar { width:2rem; height:2rem; border-radius:9999px; display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; flex-shrink:0; background:#e0e7ff; color:#4338ca; }
    .tk-msg-body { flex:1; min-width:0; }
    .tk-msg-head { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem; }
    .tk-msg-name { font-size:0.8125rem; font-weight:700; color:#0f172a; }
    .dark .tk-msg-name { color:#f1f5f9; }
    .tk-msg-time { font-size:0.7rem; color:#94a3b8; }
    .tk-msg-tag { font-size:0.6rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; padding:0.05rem 0.4rem; border-radius:0.25rem; background:#fef3c7; color:#92400e; }
    .tk-msg-text { font-size:0.875rem; color:#334155; white-space:pre-line; }
    .dark .tk-msg-text { color:#cbd5e1; }
    </style>

    <div class="tk-meta">
        <div class="tk-meta-item"><span class="k">Account</span><span class="v">{{ $record->account?->display_name }}</span></div>
        <div class="tk-meta-item"><span class="k">Opened by</span><span class="v">{{ $record->createdBy?->name }}</span></div>
        <div class="tk-meta-item"><span class="k">Priority</span><span class="v">{{ ucfirst($record->priority) }}</span></div>
        <div class="tk-meta-item"><span class="k">Status</span><span class="v">{{ ucfirst($record->status) }}</span></div>
        <div class="tk-meta-item"><span class="k">Assigned to</span><span class="v">{{ $record->assignedAdmin?->name ?? 'Unassigned' }}</span></div>
    </div>

    <div class="tk-thread">
        @forelse($this->getMessages() as $msg)
            <div @class(['tk-msg', 'staff' => $msg->sender_account_id === null, 'internal' => $msg->is_internal_note])>
                <span class="tk-avatar">{{ mb_substr($msg->sender_account_id === null ? 'S' : ($msg->senderUser?->name ?? '?'), 0, 1) }}</span>
                <div class="tk-msg-body">
                    <div class="tk-msg-head">
                        <span class="tk-msg-name">{{ $msg->sender_account_id === null ? ($msg->senderUser?->name . ' (Staff)') : $msg->senderUser?->name }}</span>
                        <span class="tk-msg-time">{{ $msg->created_at->format('M j, Y g:i A') }}</span>
                        @if($msg->is_internal_note)
                            <span class="tk-msg-tag">Internal note</span>
                        @endif
                    </div>
                    <p class="tk-msg-text">{{ $msg->message }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">No messages yet.</p>
        @endforelse
    </div>

</x-filament-panels::page>
