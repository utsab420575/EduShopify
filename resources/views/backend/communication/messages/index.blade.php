@extends($layout ?? 'backend.layouts.buyer')

@section('title', 'Messages & Real-Time Chat')
@section('breadcrumb', 'Communication / Messages')

@section('body')
<div class="h-[calc(100vh-140px)] min-h-[600px] flex flex-col bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden"
     x-data="chatApp({
         currentUserId: {{ $currentUser->id }},
         currentAccountId: {{ $currentAccount?->id ?? 'null' }},
         activeConversationId: {{ isset($activeConversation) ? $activeConversation->id : 'null' }},
         soundEnabled: {{ ($userPreferences->sound_enabled ?? true) ? 'true' : 'false' }},
         browserNotificationsEnabled: {{ ($userPreferences->browser_notifications_enabled ?? false) ? 'true' : 'false' }},
         reverbKey: '{{ config('broadcasting.connections.reverb.key') ?? env('REVERB_APP_KEY', '1m1w1dpziluc6nmp98gc') }}',
         reverbHost: '{{ config('broadcasting.connections.reverb.options.host') ?? env('REVERB_HOST', 'arounduz.uz') }}',
         reverbPort: '{{ config('broadcasting.connections.reverb.options.port') ?? env('REVERB_PORT', 8080) }}',
         reverbScheme: '{{ config('broadcasting.connections.reverb.options.scheme') ?? env('REVERB_SCHEME', 'http') }}',
         initialMessages: @json($initialMessages ?? [])
     })"
     x-init="initChat()"
     @keydown.escape="closeModals()">

    <!-- Main 2-Panel Chat Container -->
    <div class="flex-1 flex overflow-hidden">

        <!-- ── Left Panel: Conversation List ────────────────────────────── -->
        <div class="w-full md:w-80 lg:w-96 flex-shrink-0 border-r border-gray-200 flex flex-col bg-slate-50/50"
             :class="{ 'hidden md:flex': activeConversationId !== null, 'flex': activeConversationId === null }">

            <!-- Search & Filters -->
            <div class="p-4 border-b border-gray-200 bg-white space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-duotone fa-comments text-indigo-600"></i>
                        <span>Messages</span>
                    </h2>
                    <button type="button" @click="showPreferencesModal = true" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition" title="Messaging Settings">
                        <i class="fa-regular fa-gear"></i>
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <i class="fa-regular fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.300ms="fetchConversations()"
                           placeholder="Search conversations..."
                           class="w-full pl-9 pr-4 py-2 bg-gray-100/80 border-transparent focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 rounded-xl text-sm transition placeholder-gray-400">
                </div>

                <!-- Tabs: Active / Unread / Archived -->
                <div class="flex p-1 bg-gray-100 rounded-xl text-xs font-semibold text-gray-600">
                    <button type="button" @click="setFilter('active')" :class="activeFilter === 'active' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'" class="flex-1 py-1.5 rounded-lg transition text-center">
                        Inbox
                    </button>
                    <button type="button" @click="setFilter('unread')" :class="activeFilter === 'unread' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'" class="flex-1 py-1.5 rounded-lg transition text-center">
                        Unread
                    </button>
                    <button type="button" @click="setFilter('archived')" :class="activeFilter === 'archived' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'" class="flex-1 py-1.5 rounded-lg transition text-center">
                        Archived
                    </button>
                </div>
            </div>

            <!-- Conversations List Stream -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100" id="conversation-list-container">
                <template x-for="conv in conversations" :key="conv.id">
                    <div @click="selectConversation(conv.id)"
                         :class="{ 'bg-indigo-50/70 border-l-4 border-indigo-600': activeConversationId === conv.id, 'hover:bg-gray-100/70 bg-white': activeConversationId !== conv.id }"
                         class="p-4 cursor-pointer transition flex items-start gap-3 relative group">

                        <!-- Avatar & Presence Indicator -->
                        <div class="relative flex-shrink-0">
                            <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(getParticipantName(conv)) + '&background=e0e7ff&color=4338ca&bold=true'"
                                 class="w-12 h-12 rounded-full object-cover shadow-xs border border-gray-100"
                                 alt="">
                            <span x-show="isUserOnline(conv)" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full" title="Online"></span>
                        </div>

                        <!-- Info & Snippet -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <h3 class="text-sm font-semibold text-gray-900 truncate" :class="{ 'font-bold text-gray-900': conv.unread_count > 0 }" x-text="getParticipantName(conv)"></h3>
                                <span class="text-xs text-gray-400 whitespace-nowrap" x-text="formatTime(conv.last_message_at)"></span>
                            </div>

                            <p class="text-xs text-gray-500 truncate" :class="{ 'font-semibold text-gray-800': conv.unread_count > 0 }" x-text="conv.latest_snippet || 'No messages yet'"></p>

                            <!-- Context Pill -->
                            <div class="flex items-center gap-1.5 mt-1.5" x-show="conv.active_context">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    <i class="fa-regular fa-paperclip text-[9px]"></i>
                                    <span x-text="conv.active_context"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Unread Badge & Mute Icon -->
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span x-show="conv.unread_count > 0"
                                  class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-indigo-600 rounded-full shadow-xs"
                                  x-text="conv.unread_count"></span>
                            <i x-show="conv.is_muted" class="fa-solid fa-bell-slash text-xs text-gray-400" title="Muted"></i>
                        </div>
                    </div>
                </template>

                <div x-show="conversations.length === 0 && !loadingConversations" class="p-8 text-center text-gray-400">
                    <i class="fa-light fa-comments text-4xl mb-3"></i>
                    <p class="text-sm font-medium">No conversations found</p>
                    <p class="text-xs text-gray-400 mt-1">Start messaging from a supplier page, RFQ, or quotation.</p>
                </div>
            </div>
        </div>

        <!-- ── Right Panel: Chat Thread & Composer ──────────────────────── -->
        <div class="flex-1 flex flex-col bg-white overflow-hidden"
             :class="{ 'hidden md:flex': activeConversationId === null, 'flex': activeConversationId !== null }">

            <!-- Active Chat Header -->
            <template x-if="activeConversation">
                <div class="h-16 px-5 border-b border-gray-200 bg-white flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Mobile Back Button -->
                        <button type="button" @click="activeConversationId = null" class="md:hidden p-2 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                            <i class="fa-regular fa-arrow-left"></i>
                        </button>

                        <div class="relative flex-shrink-0">
                            <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(getParticipantName(activeConversation)) + '&background=e0e7ff&color=4338ca&bold=true'"
                                 class="w-10 h-10 rounded-full object-cover border border-gray-100"
                                 alt="">
                            <span x-show="isUserOnline(activeConversation)" class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-gray-900 truncate" x-text="getParticipantName(activeConversation)"></h2>
                            <p class="text-xs text-gray-500 truncate flex items-center gap-1.5">
                                <span :class="isUserOnline(activeConversation) ? 'text-emerald-600 font-medium' : 'text-gray-400'"
                                      x-text="isUserOnline(activeConversation) ? 'Online' : 'Offline'"></span>
                                <span class="text-gray-300">•</span>
                                <span x-text="activeConversation.accounts && activeConversation.accounts[0] ? activeConversation.accounts[0].display_name : 'Direct Conversation'"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Conversation Context Badges & Action Dropdown -->
                    <div class="flex items-center gap-2">
                        <!-- Context Badges -->
                        <div class="hidden lg:flex items-center gap-1.5">
                            <template x-for="ctx in (activeConversation.contexts || [])" :key="ctx.id">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <i class="fa-regular fa-tag text-[10px]"></i>
                                    <span x-text="formatContextLabel(ctx)"></span>
                                </span>
                            </template>
                        </div>

                        <!-- Mute / Archive Quick Actions -->
                        <button type="button" @click="toggleMute()" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-lg transition" :title="activeConversation.is_muted ? 'Unmute' : 'Mute'">
                            <i :class="activeConversation.is_muted ? 'fa-solid fa-bell-slash text-indigo-600' : 'fa-regular fa-bell'"></i>
                        </button>
                        <button type="button" @click="toggleArchive()" class="p-2 text-gray-500 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition" :title="activeConversation.is_archived ? 'Unarchive' : 'Archive'">
                            <i :class="activeConversation.is_archived ? 'fa-solid fa-box-archive text-amber-600' : 'fa-regular fa-box-archive'"></i>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Chat Stream / Messages Feed -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-slate-50/40" id="messages-scroll-area" @scroll.passive="handleScroll($event)">

                <template x-if="activeConversationId === null">
                    <div class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl mb-4 shadow-xs">
                            <i class="fa-duotone fa-comments"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-800">Your Messages</h3>
                        <p class="text-xs text-gray-500 mt-1 max-w-sm">Select a conversation from the sidebar to view chat history and start messaging in real-time.</p>
                    </div>
                </template>

                <!-- Loading Older Messages Spinner -->
                <div x-show="loadingOlderMessages" class="text-center py-2">
                    <i class="fa-regular fa-spinner-third fa-spin text-indigo-600 text-lg"></i>
                    <span class="text-xs text-gray-400 ml-2">Loading older messages...</span>
                </div>

                <!-- Messages Loop -->
                <template x-for="(msg, index) in messages" :key="msg.id">
                    <div class="flex flex-col" :class="msg.is_mine ? 'items-end' : 'items-start'">

                        <!-- System Message Style -->
                        <template x-if="msg.is_system">
                            <div class="w-full flex justify-center my-2">
                                <span class="px-3 py-1 bg-gray-200/80 text-gray-600 rounded-full text-xs font-medium" x-text="msg.body"></span>
                            </div>
                        </template>

                        <!-- Standard Message Bubble -->
                        <template x-if="!msg.is_system">
                            <div class="max-w-[85%] md:max-w-[70%] group relative">

                                <!-- Sender Name (for incoming team messages) -->
                                <div x-show="!msg.is_mine" class="text-[11px] font-medium text-gray-500 mb-1 px-1 flex items-center gap-1.5">
                                    <span x-text="msg.sender_name"></span>
                                    <span class="text-gray-300">•</span>
                                    <span class="text-[10px] text-gray-400" x-text="msg.sender_account_name"></span>
                                </div>

                                <!-- Quoted Reply Header -->
                                <template x-if="msg.reply_to">
                                    <div class="px-3 py-1.5 rounded-t-xl text-xs border-b border-white/20 mb-[-4px]"
                                         :class="msg.is_mine ? 'bg-indigo-700 text-indigo-100' : 'bg-gray-200 text-gray-700'">
                                        <p class="font-bold text-[10px] flex items-center gap-1">
                                            <i class="fa-solid fa-reply fa-flip-horizontal text-[9px]"></i>
                                            <span x-text="msg.reply_to.sender_name"></span>
                                        </p>
                                        <p class="truncate text-[11px] opacity-90" x-text="msg.reply_to.body"></p>
                                    </div>
                                </template>

                                <!-- Bubble Body -->
                                <div class="px-4 py-2.5 rounded-2xl text-sm shadow-xs break-words relative transition"
                                     :class="msg.is_mine ? 'bg-indigo-600 text-white rounded-br-xs' : 'bg-white text-gray-900 border border-gray-200 rounded-bl-xs'">

                                    <!-- Deleted State -->
                                    <template x-if="msg.is_deleted">
                                        <p class="italic opacity-70 flex items-center gap-1.5 text-xs">
                                            <i class="fa-regular fa-ban text-[11px]"></i>
                                            <span>This message was deleted</span>
                                        </p>
                                    </template>

                                    <!-- Active Content Body -->
                                    <template x-if="!msg.is_deleted">
                                        <div>
                                            <p class="whitespace-pre-line leading-relaxed" x-text="msg.body"></p>

                                            <!-- Attachments (Images & Files) -->
                                            <template x-if="msg.attachments && msg.attachments.length > 0">
                                                <div class="mt-2 space-y-2">
                                                    <!-- Image Gallery -->
                                                    <div class="grid grid-cols-2 gap-1.5" x-show="msg.attachments.some(a => a.is_image)">
                                                        <template x-for="att in msg.attachments.filter(a => a.is_image)" :key="att.id">
                                                            <a :href="att.url" target="_blank" class="block rounded-lg overflow-hidden border border-white/20 hover:opacity-90 transition">
                                                                <img :src="att.url" class="w-full h-32 object-cover" alt="">
                                                            </a>
                                                        </template>
                                                    </div>

                                                    <!-- Documents / Files -->
                                                    <template x-for="att in msg.attachments.filter(a => !a.is_image)" :key="att.id">
                                                        <a :href="att.url" target="_blank"
                                                           class="flex items-center gap-2.5 p-2 rounded-xl text-xs transition border"
                                                           :class="msg.is_mine ? 'bg-indigo-700/60 hover:bg-indigo-700 border-indigo-500 text-white' : 'bg-gray-50 hover:bg-gray-100 border-gray-200 text-gray-800'">
                                                            <i class="fa-solid fa-file-arrow-down text-base opacity-80"></i>
                                                            <div class="min-w-0 flex-1">
                                                                <p class="font-semibold truncate" x-text="att.name"></p>
                                                                <p class="text-[10px] opacity-75" x-text="att.size"></p>
                                                            </div>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Footer: Time, Edited Badge, Receipts (Sent/Delivered/Seen) -->
                                    <div class="flex items-center justify-end gap-1.5 mt-1 text-[10px]"
                                         :class="msg.is_mine ? 'text-indigo-200' : 'text-gray-400'">
                                        <span x-show="msg.is_edited && !msg.is_deleted" class="italic">edited</span>
                                        <span x-text="msg.created_at_time"></span>

                                        <!-- Status Ticks for Sent Messages -->
                                        <template x-if="msg.is_mine && !msg.is_deleted">
                                            <span>
                                                <i x-show="msg.is_seen" class="fa-solid fa-check-double text-sky-300" title="Seen"></i>
                                                <i x-show="!msg.is_seen && msg.is_delivered" class="fa-solid fa-check-double text-indigo-300" title="Delivered"></i>
                                                <i x-show="!msg.is_seen && !msg.is_delivered" class="fa-solid fa-check text-indigo-300" title="Sent"></i>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Action Dropdown Button on Hover -->
                                <div x-show="!msg.is_deleted" class="absolute top-0 opacity-0 group-hover:opacity-100 transition flex items-center gap-1"
                                     :class="msg.is_mine ? '-left-16' : '-right-16'">
                                    <button type="button" @click="setReply(msg)" class="p-1.5 bg-white border border-gray-200 text-gray-500 hover:text-indigo-600 rounded-lg shadow-xs text-xs" title="Reply">
                                        <i class="fa-solid fa-reply"></i>
                                    </button>
                                    <template x-if="msg.is_mine">
                                        <button type="button" @click="openEditModal(msg)" class="p-1.5 bg-white border border-gray-200 text-gray-500 hover:text-amber-600 rounded-lg shadow-xs text-xs" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    </template>
                                    <template x-if="msg.is_mine">
                                        <button type="button" @click="deleteMessage(msg)" class="p-1.5 bg-white border border-gray-200 text-gray-500 hover:text-rose-600 rounded-lg shadow-xs text-xs" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Typing Bubble -->
                <div x-show="typingUser" class="flex items-center gap-2 text-xs text-gray-500 py-1 px-3 bg-white rounded-full border border-gray-200 w-fit shadow-xs animate-pulse">
                    <i class="fa-regular fa-ellipsis fa-fade text-indigo-600"></i>
                    <span x-text="typingUser + ' is typing...'"></span>
                </div>
            </div>

            <!-- ── Bottom Panel: Composer & Attachments ────────────────────── -->
            <template x-if="activeConversationId !== null">
                <div class="border-t border-gray-200 bg-white p-3 md:p-4 space-y-2 flex-shrink-0">

                    <!-- Reply Preview Banner -->
                    <div x-show="replyTarget" class="flex items-center justify-between p-2 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg text-xs">
                        <div class="min-w-0 flex-1">
                            <span class="font-bold text-indigo-900" x-text="'Replying to ' + (replyTarget ? replyTarget.sender_name : '')"></span>
                            <p class="truncate text-gray-600" x-text="replyTarget ? replyTarget.body : ''"></p>
                        </div>
                        <button type="button" @click="replyTarget = null" class="p-1 text-gray-400 hover:text-gray-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Selected Files Preview Chips -->
                    <div x-show="selectedFiles.length > 0" class="flex flex-wrap gap-2 py-1">
                        <template x-for="(file, i) in selectedFiles" :key="i">
                            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 rounded-lg text-xs text-gray-700 border border-gray-200">
                                <i class="fa-regular fa-paperclip text-gray-400"></i>
                                <span class="max-w-[140px] truncate font-medium" x-text="file.name"></span>
                                <button type="button" @click="removeFile(i)" class="text-gray-400 hover:text-rose-600 ml-1">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Input Box & Actions -->
                    <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                        <!-- Attachment Upload Button -->
                        <label class="p-2.5 text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-xl cursor-pointer transition flex-shrink-0" title="Attach image or document">
                            <i class="fa-regular fa-paperclip text-lg"></i>
                            <input type="file" multiple @change="handleFileSelect($event)" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt">
                        </label>

                        <!-- Message Textarea -->
                        <div class="flex-1 min-w-0 relative">
                            <textarea x-model="messageText"
                                      @keydown.enter.exact.prevent="sendMessage()"
                                      @input="notifyTyping()"
                                      placeholder="Type your message... (Enter to send, Shift+Enter for new line)"
                                      rows="1"
                                      class="w-full resize-none max-h-32 px-4 py-2.5 bg-gray-50 border border-gray-200 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 rounded-xl text-sm transition placeholder-gray-400"
                                      id="message-input-textarea"></textarea>
                        </div>

                        <!-- Send Button -->
                        <button type="submit"
                                :disabled="sending || (messageText.trim() === '' && selectedFiles.length === 0)"
                                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl text-sm transition flex items-center gap-2 shadow-xs flex-shrink-0">
                            <span x-show="!sending">Send</span>
                            <i x-show="!sending" class="fa-solid fa-paper-plane-top text-xs"></i>
                            <i x-show="sending" class="fa-regular fa-spinner-third fa-spin text-sm"></i>
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>

    <!-- ── Edit Message Modal ───────────────────────────────────────────── -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Edit Message</h3>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <textarea x-model="editText" rows="4" class="w-full p-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl">Cancel</button>
                <button type="button" @click="submitEdit()" class="px-4 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- ── Messaging Preferences Modal ──────────────────────────────────── -->
    <div x-show="showPreferencesModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-regular fa-sliders text-indigo-600"></i>
                    <span>Messaging Preferences</span>
                </h3>
                <button type="button" @click="showPreferencesModal = false" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-4">
                <!-- Sound Toggle -->
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Notification Sound</p>
                        <p class="text-xs text-gray-400">Play an audio chime on incoming messages</p>
                    </div>
                    <input type="checkbox" x-model="prefSound" class="w-5 h-5 text-indigo-600 rounded">
                </label>

                <!-- Desktop Notifications -->
                <label class="flex items-center justify-between cursor-pointer">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Desktop Notifications</p>
                        <p class="text-xs text-gray-400">Receive browser alerts for new messages</p>
                    </div>
                    <input type="checkbox" x-model="prefBrowser" @change="requestBrowserPermission()" class="w-5 h-5 text-indigo-600 rounded">
                </label>

                <!-- Unread Email Digest -->
                <div class="pt-2 border-t border-gray-100 space-y-2">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Unread Message Email Digest</p>
                            <p class="text-xs text-gray-400">Send an email reminder if messages remain unread</p>
                        </div>
                        <input type="checkbox" x-model="prefEmail" class="w-5 h-5 text-indigo-600 rounded">
                    </label>

                    <div x-show="prefEmail" class="pl-2 pt-1">
                        <label class="text-xs font-semibold text-gray-600">Send reminder after delay:</label>
                        <select x-model="prefEmailDelay" class="mt-1 w-full text-xs p-2 border border-gray-300 rounded-lg bg-white">
                            <option value="1">After 1 hour</option>
                            <option value="12">After 12 hours</option>
                            <option value="24">After 24 hours (Recommended)</option>
                            <option value="48">After 48 hours</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" @click="showPreferencesModal = false" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-xl">Close</button>
                <button type="button" @click="savePreferences()" class="px-4 py-2 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl">Save Preferences</button>
            </div>
        </div>
    </div>
</div>

<!-- Load Laravel Echo and Pusher Client for Real-Time Reverb -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<script>
function chatApp(config) {
    return {
        currentUserId: config.currentUserId,
        currentAccountId: config.currentAccountId,
        activeConversationId: config.activeConversationId,
        conversations: [],
        activeConversation: null,
        messages: config.initialMessages || [],
        searchQuery: '',
        activeFilter: 'active',
        messageText: '',
        selectedFiles: [],
        replyTarget: null,
        typingUser: null,
        typingTimeout: null,
        onlineUsers: [],
        loadingConversations: false,
        loadingOlderMessages: false,
        sending: false,
        showEditModal: false,
        editingMessage: null,
        editText: '',
        showPreferencesModal: false,
        prefSound: config.soundEnabled,
        prefBrowser: config.browserNotificationsEnabled,
        prefEmail: false,
        prefEmailDelay: 24,
        echoInstance: null,
        activeChannel: null,

        initChat() {
            this.initEcho();
            this.fetchConversations();
            if (this.activeConversationId) {
                this.loadConversation(this.activeConversationId);
            }
        },

        initEcho() {
            try {
                window.Pusher = Pusher;
                this.echoInstance = new Echo({
                    broadcaster: 'reverb',
                    key: config.reverbKey,
                    wsHost: config.reverbHost,
                    wsPort: parseInt(config.reverbPort) || 8080,
                    wssPort: parseInt(config.reverbPort) || 8080,
                    forceTLS: (config.reverbScheme === 'https'),
                    enabledTransports: ['ws', 'wss'],
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        }
                    }
                });

                // Listen on private user channel for message alerts and business notifications
                this.echoInstance.private('user.' + this.currentUserId)
                    .listen('.MessageSent', (e) => {
                        this.handleIncomingGlobalMessage(e);
                    })
                    .listen('.ConversationRead', (e) => {
                        const conv = this.conversations.find(c => c.id === e.conversation_id);
                        if (conv) conv.unread_count = 0;
                    })
                    .notification((notification) => {
                        this.handleBusinessNotification(notification);
                    });
            } catch (err) {
                console.warn('Real-time connection initialized in fallback mode:', err);
            }
        },

        fetchConversations() {
            this.loadingConversations = true;
            fetch(`/messages?filter=${this.activeFilter}&search=${encodeURIComponent(this.searchQuery)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.conversations = data.conversations || [];
                this.loadingConversations = false;
            })
            .catch(() => { this.loadingConversations = false; });
        },

        selectConversation(id) {
            this.activeConversationId = id;
            this.loadConversation(id);
        },

        loadConversation(id) {
            if (this.activeChannel) {
                this.echoInstance.leave('conversation.' + this.activeChannel);
            }

            fetch(`/messages/${id}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.activeConversation = data.conversation;
                this.messages = data.messages || [];

                // Reset unread count locally
                const conv = this.conversations.find(c => c.id === id);
                if (conv) conv.unread_count = 0;

                this.scrollToBottom();
                this.joinConversationPresenceChannel(id);
                this.acknowledgeUnseenMessages();
            });
        },

        joinConversationPresenceChannel(id) {
            this.activeChannel = id;
            if (!this.echoInstance) return;

            this.echoInstance.join('conversation.' + id)
                .here((users) => {
                    this.onlineUsers = users;
                })
                .joining((user) => {
                    if (!this.onlineUsers.some(u => u.id === user.id)) {
                        this.onlineUsers.push(user);
                    }
                })
                .leaving((user) => {
                    this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id);
                })
                .listenForWhisper('typing', (e) => {
                    if (e.userId !== this.currentUserId) {
                        this.typingUser = e.userName;
                        clearTimeout(this.typingTimeout);
                        this.typingTimeout = setTimeout(() => { this.typingUser = null; }, 3000);
                    }
                })
                .listen('.MessageSent', (e) => {
                    if (e.conversation_id === this.activeConversationId) {
                        if (!this.messages.some(m => m.id === e.message.id)) {
                            this.messages.push(e.message);
                            this.scrollToBottom();
                            this.acknowledgeDelivered(e.message.id);
                        }
                    }
                })
                .listen('.MessageEdited', (e) => {
                    const msg = this.messages.find(m => m.id === e.message_id);
                    if (msg) {
                        msg.body = e.body;
                        msg.is_edited = true;
                        msg.edited_at = e.edited_at;
                    }
                })
                .listen('.MessageDeleted', (e) => {
                    const msg = this.messages.find(m => m.id === e.message_id);
                    if (msg) {
                        msg.is_deleted = true;
                        msg.body = null;
                        msg.attachments = [];
                    }
                })
                .listen('.MessageDelivered', (e) => {
                    const msg = this.messages.find(m => m.id === e.message_id);
                    if (msg) msg.is_delivered = true;
                })
                .listen('.MessageSeen', (e) => {
                    this.messages.forEach(m => {
                        if (m.is_mine) {
                            m.is_delivered = true;
                            m.is_seen = true;
                        }
                    });
                });
        },

        sendMessage() {
            const text = this.messageText.trim();
            if (text === '' && this.selectedFiles.length === 0) return;
            if (!this.activeConversationId) return;

            this.sending = true;
            const formData = new FormData();
            formData.append('body', text);
            if (this.replyTarget) {
                formData.append('reply_to_message_id', this.replyTarget.id);
            }
            this.selectedFiles.forEach((file, index) => {
                formData.append(`attachments[${index}]`, file);
            });

            fetch(`/messages/${this.activeConversationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.sending = false;
                if (data.success && data.message) {
                    if (!this.messages.some(m => m.id === data.message.id)) {
                        this.messages.push(data.message);
                    }
                    this.messageText = '';
                    this.selectedFiles = [];
                    this.replyTarget = null;
                    this.scrollToBottom();
                }
            })
            .catch(() => { this.sending = false; });
        },

        notifyTyping() {
            if (this.echoInstance && this.activeConversationId) {
                this.echoInstance.join('conversation.' + this.activeConversationId)
                    .whisper('typing', {
                        userId: this.currentUserId,
                        userName: 'User'
                    });
            }
        },

        acknowledgeDelivered(messageId) {
            fetch(`/messages/item/${messageId}/delivered`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
        },

        acknowledgeUnseenMessages() {
            if (!this.activeConversationId) return;
            fetch(`/messages/${this.activeConversationId}/seen`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
        },

        handleIncomingGlobalMessage(e) {
            // If in another chat, play chime and bump unread count
            if (e.conversation_id !== this.activeConversationId) {
                const conv = this.conversations.find(c => c.id === e.conversation_id);
                if (conv) {
                    conv.unread_count = (conv.unread_count || 0) + 1;
                    conv.latest_snippet = e.body_preview;
                    conv.last_message_at = new Date().toISOString();
                }

                if (this.prefSound && !conv?.is_muted) {
                    this.playNotificationSound();
                }

                if (this.prefBrowser && Notification.permission === 'granted') {
                    new Notification('New message from ' + e.sender_account_name, {
                        body: e.body_preview,
                        icon: '/favicon.ico'
                    });
                }
            }
        },

        handleBusinessNotification(notification) {
            // Live toast for persistent business notifications
            if (this.prefSound) {
                this.playNotificationSound();
            }
        },

        playNotificationSound() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1); // A5
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.3);
            } catch (e) {}
        },

        requestBrowserPermission() {
            if (this.prefBrowser && 'Notification' in window && Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
        },

        toggleMute() {
            fetch(`/messages/${this.activeConversationId}/mute`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (this.activeConversation) {
                    this.activeConversation.is_muted = data.is_muted;
                }
            });
        },

        toggleArchive() {
            fetch(`/messages/${this.activeConversationId}/archive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (this.activeConversation) {
                    this.activeConversation.is_archived = data.is_archived;
                }
                this.fetchConversations();
            });
        },

        openEditModal(msg) {
            this.editingMessage = msg;
            this.editText = msg.body;
            this.showEditModal = true;
        },

        submitEdit() {
            if (!this.editingMessage) return;
            fetch(`/messages/item/${this.editingMessage.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ body: this.editText })
            })
            .then(res => res.json())
            .then(data => {
                this.editingMessage.body = this.editText;
                this.editingMessage.is_edited = true;
                this.editingMessage.edited_at = data.edited_at;
                this.showEditModal = false;
            });
        },

        deleteMessage(msg) {
            if (!confirm('Are you sure you want to delete this message?')) return;
            fetch(`/messages/item/${msg.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                msg.is_deleted = true;
                msg.body = null;
                msg.attachments = [];
            });
        },

        setReply(msg) {
            this.replyTarget = msg;
            document.getElementById('message-input-textarea')?.focus();
        },

        setFilter(filter) {
            this.activeFilter = filter;
            this.fetchConversations();
        },

        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            this.selectedFiles = this.selectedFiles.concat(files);
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },

        savePreferences() {
            fetch('/messages/settings/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    sound_enabled: this.prefSound,
                    browser_notifications_enabled: this.prefBrowser,
                    unread_email_enabled: this.prefEmail,
                    unread_email_delay_hours: parseInt(this.prefEmailDelay) || 24
                })
            })
            .then(res => res.json())
            .then(() => {
                this.showPreferencesModal = false;
            });
        },

        getParticipantName(conv) {
            if (!conv) return 'User';
            if (conv.other_account) {
                return conv.other_account.supplier_profile?.display_name || conv.other_account.buyer_profile?.display_name || conv.other_account.display_name;
            }
            if (conv.accounts && conv.accounts.length > 0) {
                const other = conv.accounts[0];
                return other.supplier_profile?.display_name || other.buyer_profile?.display_name || other.display_name;
            }
            return conv.title || 'Direct Conversation';
        },

        isUserOnline(conv) {
            return this.onlineUsers.length > 1;
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        formatContextLabel(ctx) {
            if (!ctx) return '';
            return `${ctx.context_type.toUpperCase()} #${ctx.context_id}`;
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const area = document.getElementById('messages-scroll-area');
                if (area) area.scrollTop = area.scrollHeight;
            });
        },

        handleScroll(event) {
            // Scroll handling for loading older messages
        },

        closeModals() {
            this.showEditModal = false;
            this.showPreferencesModal = false;
        }
    };
}
</script>
@endsection
