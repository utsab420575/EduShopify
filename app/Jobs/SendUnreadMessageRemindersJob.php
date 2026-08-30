<?php

namespace App\Jobs;

use App\Mail\UnreadMessagesDigestMail;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserMessagingPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendUnreadMessageRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): int
    {
        // Find all users who opted in for unread email reminders
        $preferences = UserMessagingPreference::where('unread_email_enabled', true)->get();
        $sentCount = 0;

        foreach ($preferences as $pref) {
            $user = User::find($pref->user_id);
            if (! $user || ! $user->isActive()) {
                continue;
            }

            $delayHours = $pref->unread_email_delay_hours ?: 24;
            $threshold = now()->subHours($delayHours);

            // Get all conversations user participates in
            $conversations = Conversation::whereHas('accounts', function ($q) use ($user) {
                $q->whereHas('users', fn ($uq) => $uq->where('users.id', $user->id));
            })->with(['userStates' => fn ($q) => $q->where('user_id', $user->id)])->get();

            $digestItems = [];
            $allUnreadMessageIds = [];
            $totalUnread = 0;

            foreach ($conversations as $conv) {
                $userState = $conv->userStates->first();
                $lastReadAt = $userState?->last_read_at;

                $unreadMessagesQuery = Message::where('conversation_id', $conv->id)
                    ->where('sender_user_id', '!=', $user->id)
                    ->when($lastReadAt, fn ($q) => $q->where('created_at', '>', $lastReadAt))
                    ->where('created_at', '<=', $threshold);

                $unreadMessages = $unreadMessagesQuery->with(['senderUser', 'senderAccount'])->orderBy('created_at')->get();

                if ($unreadMessages->isNotEmpty()) {
                    $latest = $unreadMessages->last();
                    $unreadCount = $unreadMessages->count();
                    $totalUnread += $unreadCount;

                    foreach ($unreadMessages as $m) {
                        $allUnreadMessageIds[] = $m->id;
                    }

                    $digestItems[] = [
                        'conversation_id' => $conv->id,
                        'sender_name'     => $latest->senderUser?->name ?? 'User',
                        'sender_account'  => $latest->senderAccount?->display_name ?? 'Account',
                        'unread_count'    => $unreadCount,
                        'latest_message'  => Str::limit($latest->body ?: 'Sent an attachment', 100),
                        'latest_time'     => $latest->created_at->format('d M, h:i A'),
                        'url'             => route('messages.show', $conv->id),
                    ];
                }
            }

            if (empty($digestItems)) {
                continue;
            }

            // Calculate deterministic hash of unread messages to prevent repeated sending of unchanged digest
            sort($allUnreadMessageIds);
            $currentDigestHash = md5(implode(',', $allUnreadMessageIds));

            if ($pref->last_digest_hash === $currentDigestHash) {
                // Digest unchanged since last reminder
                continue;
            }

            // Send digest email
            Mail::to($user->email)->queue(new UnreadMessagesDigestMail($user, $digestItems, $totalUnread));

            // Update preference tracking
            $pref->update([
                'last_reminder_sent_at' => now(),
                'last_digest_hash'      => $currentDigestHash,
            ]);

            $sentCount++;
        }

        return $sentCount;
    }
}
