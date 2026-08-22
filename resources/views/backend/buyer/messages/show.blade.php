@extends('backend.layouts.buyer')

@section('title', 'Messages')
@section('breadcrumb', 'Communication / Messages')

@section('body')

    <div class="bg-white rounded-xl border border-gray-200 flex flex-col" style="height: calc(100vh - 220px);" x-data="messageThread({{ $conversation->id }})">

        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 shrink-0">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($otherAccount?->supplierProfile?->display_name ?? '?') }}&background=eef2ff&color=4f46e5" class="w-9 h-9 rounded-full" alt="">
            <div class="min-w-0">
                <a href="{{ route('buyer.suppliers.show', $otherAccount) }}" class="text-sm font-semibold text-gray-900 hover:underline">{{ $otherAccount?->supplierProfile?->display_name }}</a>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3" x-ref="scroller">
            <template x-for="message in messages" :key="message.id">
                <div>
                    <div x-show="message.is_system" class="text-center">
                        <span class="inline-block text-xs text-gray-400 bg-gray-50 rounded-full px-3 py-1" x-text="message.body"></span>
                    </div>
                    <div x-show="!message.is_system" class="flex" :class="message.is_mine ? 'justify-end' : 'justify-start'">
                        <div class="max-w-[70%] rounded-xl px-3.5 py-2 text-sm" :class="message.is_mine ? 'text-white' : 'bg-gray-100 text-gray-800'" :style="message.is_mine ? 'background:var(--theme-primary)' : ''">
                            <p x-text="message.body"></p>
                            <p class="text-[10px] mt-1 opacity-70" x-text="message.created_at"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <form method="POST" action="{{ route('buyer.messages.store', $conversation) }}" class="flex items-center gap-2 px-5 py-4 border-t border-gray-100 shrink-0">
            @csrf
            <input type="text" name="body" required placeholder="Type a message..." class="focus-accent flex-1 px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Send</button>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('messageThread', (conversationId) => ({
            messages: @json($initialMessages),
            poll: null,
            init() {
                this.scrollDown();
                this.poll = setInterval(() => this.refresh(), 5000);
                this.$watch('messages', () => this.$nextTick(() => this.scrollDown()));
            },
            refresh() {
                fetch('{{ url('/buyer/messages') }}/' + conversationId + '/poll')
                    .then(r => r.json())
                    .then(data => { this.messages = data; });
            },
            scrollDown() {
                if (this.$refs.scroller) this.$refs.scroller.scrollTop = this.$refs.scroller.scrollHeight;
            },
        }));
    });
</script>
@endpush
