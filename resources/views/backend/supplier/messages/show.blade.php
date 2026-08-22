@extends('backend.layouts.supplier')

@section('title', 'Conversation')
@section('breadcrumb', 'Communication / Messages')

@section('body')

    <x-backend.page-header title="{{ $otherAccount?->buyerProfile?->organization_name ?? $otherAccount?->display_name ?? 'Buyer' }}" subtitle="Direct conversation thread" />

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden flex flex-col h-[600px]"
         x-data="{
             messages: {{ json_encode($initialMessages) }},
             body: '',
             scrollToBottom() {
                 this.$nextTick(() => {
                     const el = document.getElementById('chat-scroll');
                     if (el) el.scrollTop = el.scrollHeight;
                 });
             },
             init() {
                 this.scrollToBottom();
                 setInterval(() => {
                     fetch('{{ route('supplier.messages.poll', $conversation) }}')
                         .then(r => r.json())
                         .then(d => {
                             if (d.length !== this.messages.length) {
                                 this.messages = d;
                                 this.scrollToBottom();
                             }
                         });
                 }, 4000);
             }
         }">

        {{-- Messages Scroll Area --}}
        <div id="chat-scroll" class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50/50">
            <template x-for="msg in messages" :key="msg.id">
                <div class="flex flex-col" :class="msg.is_mine ? 'items-end' : 'items-start'">
                    <div class="max-w-md p-3 rounded-2xl text-xs leading-relaxed"
                         :class="msg.is_mine ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white border border-gray-200 text-gray-800 rounded-tl-none shadow-sm'">
                        <p x-text="msg.body"></p>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 px-1" x-text="msg.created_at"></span>
                </div>
            </template>
        </div>

        {{-- Send Input Form --}}
        <div class="p-3 bg-white border-t border-gray-100">
            <form method="POST" action="{{ route('supplier.messages.store', $conversation) }}" class="flex gap-2">
                @csrf
                <input type="text" name="body" required placeholder="Type your message..." class="flex-1 text-sm border border-gray-300 rounded-xl px-4 py-2.5 bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <button type="submit" class="btn-primary text-sm font-semibold px-5 py-2.5 rounded-xl flex items-center gap-1.5 shadow-sm shrink-0">
                    <i class="fa-solid fa-paper-plane"></i> Send
                </button>
            </form>
        </div>

    </div>

@endsection
