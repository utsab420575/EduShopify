{{--
    "Contact Supplier" quick-chat popup for the public supplier profile page
    (/v2/supplier/{slug}). Reuses the exact same backend the full Messages
    dashboard uses (routes/backend/shared.php's unprefixed messages.* group,
    UnifiedMessageController, MessagingService) — no new backend endpoints —
    so a conversation started here is the same persistent conversation the
    buyer/supplier dashboards see, and real-time delivery uses the same
    Reverb broadcast (conversation.{id} presence channel, .MessageSent) the
    full chat page listens on. Only authenticated users get this popup (see
    the @auth/@else split around every "Contact Supplier" button); guests
    keep the existing v2.handoff.contact-supplier redirect-to-login flow,
    since there's no conversation to open before authentication.

    UI is a floating widget (bottom-right, fixed width, no full-screen
    backdrop) matching the industry-standard messenger-chat-plugin pattern
    (Facebook Messenger / Intercom / Drift) — not a centered modal dialog —
    per explicit feedback that a full-width overlay wasn't wanted here.
--}}
<div x-show="open" x-cloak x-transition
     class="fixed z-[200] bottom-4 right-4 sm:bottom-6 sm:right-6"
     style="width:min(92vw,380px)"
     @keydown.escape.window="closeChat()">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
         style="height:min(70vh,560px)">

        <div class="flex items-center justify-between gap-3 px-4 py-3 shrink-0" style="background:#10b981">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-full bg-white/20 text-white flex items-center justify-center font-bold text-sm shrink-0" x-text="recipientName.charAt(0).toUpperCase()"></div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white truncate" x-text="recipientName"></p>
                    <p class="text-[11px] text-white/75">Direct message</p>
                </div>
            </div>
            <button type="button" @click="closeChat()" class="text-white/80 hover:text-white cursor-pointer shrink-0">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50" x-ref="chatScroll">
            <template x-if="loading && messages.length === 0">
                <div class="flex items-center justify-center h-full text-gray-400 text-sm">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading conversation…
                </div>
            </template>

            <template x-if="error">
                <div class="flex items-center justify-center h-full text-center px-4">
                    <p class="text-sm text-red-500"><i class="fa-solid fa-triangle-exclamation mr-1"></i> <span x-text="error"></span></p>
                </div>
            </template>

            <template x-if="!loading && !error && messages.length === 0">
                <div class="flex items-center justify-center h-full text-gray-400 text-sm text-center px-6">
                    Say hello — your message starts a direct conversation with <span class="font-medium text-gray-500" x-text="recipientName"></span>.
                </div>
            </template>

            <template x-for="message in messages" :key="message.id">
                <div class="flex" :class="message.is_mine ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[80%] rounded-2xl px-3.5 py-2 text-sm"
                         :class="message.is_mine ? 'bg-emerald-500 text-white rounded-br-sm' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-sm'">
                        <p class="whitespace-pre-wrap break-words" x-text="message.body"></p>
                        <p class="text-[10px] mt-1" :class="message.is_mine ? 'text-emerald-50/80' : 'text-gray-400'" x-text="message.created_at_time"></p>
                    </div>
                </div>
            </template>
        </div>

        <div class="border-t border-gray-100 px-4 py-3 shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                <textarea x-model="messageText" rows="1"
                          @keydown.enter.exact.prevent="sendMessage()"
                          placeholder="Type your message…"
                          :disabled="loading || !conversationId"
                          class="flex-1 resize-none max-h-24 px-3.5 py-2.5 bg-gray-50 border border-gray-200 focus:bg-white focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 rounded-xl text-sm transition placeholder-gray-400 disabled:opacity-60"></textarea>
                <button type="submit" :disabled="sending || !messageText.trim() || !conversationId"
                        class="w-10 h-10 shrink-0 rounded-xl text-white flex items-center justify-center transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        style="background:#10b981">
                    <i x-show="!sending" class="fa-solid fa-paper-plane text-sm"></i>
                    <i x-show="sending" class="fa-solid fa-spinner fa-spin text-sm"></i>
                </button>
            </form>
            <a x-show="conversationUrl" :href="conversationUrl" class="block text-center text-[11px] text-gray-400 hover:text-emerald-600 mt-2 transition-colors">
                Open full conversation <i class="fa-solid fa-arrow-up-right-from-square ml-0.5 text-[9px]"></i>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function supplierContactChat(config) {
        return {
            recipientAccountId: config.recipientAccountId,
            recipientName: config.recipientName || 'Supplier',
            open: false,
            loading: false,
            sending: false,
            error: null,
            conversationId: null,
            conversationUrl: null,
            messages: [],
            messageText: '',
            echoInstance: null,
            scriptsLoaded: false,
            realtimeConnected: false,
            pollTimer: null,

            openChat() {
                this.open = true;
                if (this.conversationId) {
                    this.refreshMessages();
                    if (!this.realtimeConnected) {
                        this.connectRealtime();
                    }
                    this.startPolling();
                    return;
                }
                this.startConversation();
            },

            // Belt-and-braces alongside the Echo/.MessageSent listener:
            // guarantees a reply shows up within a few seconds even if the
            // websocket subscription is slow to establish, drops silently
            // (proxy/extension/firewall), or the join races a message sent
            // right as the popup opens. Uses the same after_id mechanism
            // UnifiedMessageController::show() already serves.
            startPolling() {
                this.stopPolling();
                this.pollTimer = setInterval(() => {
                    if (this.open && this.conversationId) {
                        this.pollForNewMessages();
                    }
                }, 4000);
            },

            stopPolling() {
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;
                }
            },

            pollForNewMessages() {
                const lastId = this.messages.length ? this.messages[this.messages.length - 1].id : 0;
                fetch(`/messages/${this.conversationId}?after_id=${lastId}`, { headers: { Accept: 'application/json' } })
                    .then((res) => res.json())
                    .then((data) => {
                        const incoming = data.messages || [];
                        let appended = false;
                        incoming.forEach((m) => {
                            if (!this.messages.some((existing) => existing.id === m.id)) {
                                this.messages.push(m);
                                appended = true;
                            }
                        });
                        if (appended) {
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    })
                    .catch(() => {});
            },

            closeChat() {
                // Deliberately does NOT leave the presence channel — this
                // widget can be reopened any number of times in the same
                // page visit, and openChat()'s "already started" branch
                // only re-fetches messages over HTTP, it doesn't rejoin.
                // Leaving here meant the very first close silently killed
                // real-time for the rest of the visit (the actual bug
                // behind "can't see live message" after using it once).
                // Staying joined while hidden also means a reply that
                // arrives while closed is already in `messages` the
                // instant it reopens.
                this.open = false;
                this.stopPolling();
            },

            csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            },

            jsonHeaders() {
                return {
                    'X-CSRF-TOKEN': this.csrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                };
            },

            startConversation() {
                this.loading = true;
                this.error = null;
                fetch('/messages/start', {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({
                        recipient_account_id: this.recipientAccountId,
                        context_type: 'general',
                    }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        if (!data.conversation_id) throw new Error('start-failed');
                        this.conversationId = data.conversation_id;
                        this.conversationUrl = '/messages/' + this.conversationId;
                        return this.loadMessages();
                    })
                    .then(() => this.connectRealtime())
                    .then(() => this.startPolling())
                    .catch(() => {
                        this.error = 'Could not start the conversation. Please try again.';
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            refreshMessages() {
                this.loading = true;
                this.loadMessages().finally(() => {
                    this.loading = false;
                });
            },

            loadMessages() {
                return fetch('/messages/' + this.conversationId, { headers: { Accept: 'application/json' } })
                    .then((res) => res.json())
                    .then((data) => {
                        this.messages = data.messages || [];
                        this.$nextTick(() => this.scrollToBottom());
                    });
            },

            loadScripts() {
                if (this.scriptsLoaded) return Promise.resolve();
                const load = (src) => new Promise((resolve, reject) => {
                    const s = document.createElement('script');
                    s.src = src;
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
                return load('https://js.pusher.com/8.2.0/pusher.min.js')
                    .then(() => load('https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js'))
                    .then(() => {
                        this.scriptsLoaded = true;
                    });
            },

            connectRealtime() {
                return this.loadScripts()
                    .then(() => {
                        if (!this.echoInstance) {
                            window.Pusher = Pusher;
                            this.echoInstance = new Echo({
                                broadcaster: 'reverb',
                                key: {{ Illuminate\Support\Js::from(config('broadcasting.connections.reverb.key') ?? env('REVERB_APP_KEY', '1m1w1dpziluc6nmp98gc')) }},
                                wsHost: {{ Illuminate\Support\Js::from(config('broadcasting.connections.reverb.options.host') ?? env('REVERB_HOST', '127.0.0.1')) }},
                                wsPort: {{ (int) (config('broadcasting.connections.reverb.options.port') ?? env('REVERB_PORT', 8080)) }},
                                wssPort: {{ (int) (config('broadcasting.connections.reverb.options.port') ?? env('REVERB_PORT', 8080)) }},
                                forceTLS: {{ (config('broadcasting.connections.reverb.options.scheme') ?? env('REVERB_SCHEME', 'http')) === 'https' ? 'true' : 'false' }},
                                enabledTransports: ['ws', 'wss'],
                                // Presence channel (conversation.{id}, routes/channels.php)
                                // needs an authenticated /broadcasting/auth handshake —
                                // without this CSRF header that POST 419s and .join()
                                // fails silently, so neither side ever receives
                                // .MessageSent (this was the live-messaging bug).
                                authEndpoint: '/broadcasting/auth',
                                auth: {
                                    headers: {
                                        'X-CSRF-TOKEN': this.csrfToken(),
                                    },
                                },
                            });
                        }
                        this.echoInstance.join('conversation.' + this.conversationId)
                            .here(() => {
                                this.realtimeConnected = true;
                            })
                            .listen('.MessageSent', (e) => {
                                if (e.conversation_id === this.conversationId && !this.messages.some((m) => m.id === e.message.id)) {
                                    this.messages.push(e.message);
                                    this.$nextTick(() => this.scrollToBottom());
                                }
                            })
                            .error((error) => {
                                console.warn('Chat real-time subscription failed:', error);
                            });
                    })
                    .catch((error) => {
                        // Real-time is a progressive enhancement only — reopening
                        // the popup still refreshes messages via loadMessages(),
                        // and openChat() retries this if realtimeConnected never
                        // got set (e.g. a transient CDN/network hiccup).
                        console.warn('Chat real-time connection unavailable:', error);
                    });
            },

            sendMessage() {
                const text = this.messageText.trim();
                if (!text || !this.conversationId || this.sending) return;

                this.sending = true;
                fetch('/messages/' + this.conversationId, {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({ body: text }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.success && data.message && !this.messages.some((m) => m.id === data.message.id)) {
                            this.messages.push(data.message);
                            this.messageText = '';
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    })
                    .finally(() => {
                        this.sending = false;
                    });
            },

            scrollToBottom() {
                const el = this.$refs.chatScroll;
                if (el) el.scrollTop = el.scrollHeight;
            },
        };
    }
</script>
@endpush
