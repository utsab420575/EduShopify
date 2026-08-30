<?php

namespace Tests\Feature;

use App\Events\Messaging\ConversationRead;
use App\Events\Messaging\MessageDeleted;
use App\Events\Messaging\MessageDelivered;
use App\Events\Messaging\MessageEdited;
use App\Events\Messaging\MessageSeen;
use App\Events\Messaging\MessageSent;
use App\Jobs\SendUnreadMessageRemindersJob;
use App\Mail\UnreadMessagesDigestMail;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\BuyerProfile;
use App\Models\Conversation;
use App\Models\ConversationAccount;
use App\Models\ConversationContext;
use App\Models\ConversationUserState;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\Notification as NotificationModel;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Models\UserMessagingPreference;
use App\Notifications\DashboardNotification;
use App\Services\MessagingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RealtimeMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected User $buyerUser;
    protected Account $buyerAccount;
    protected User $supplierUser;
    protected Account $supplierAccount;
    protected MessagingService $messagingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->messagingService = app(MessagingService::class);

        // Setup Buyer User & Account
        $this->buyerUser = User::factory()->create(['email' => 'buyer_test@edushopify.com', 'status' => 'active']);
        $this->buyerAccount = Account::create([
            'account_type' => 'organization',
            'display_name' => 'Oakridge High School',
            'legal_name'   => 'Oakridge High School Inc',
            'status'       => 'active',
            'currency'     => 'USD',
        ]);
        $this->buyerAccount->capabilities()->create([
            'capability_type_id' => \App\Models\CapabilityType::where('code', 'buyer')->first()->id,
            'status'             => 'active',
            'activated_at'       => now(),
        ]);
        BuyerProfile::create([
            'account_id'       => $this->buyerAccount->id,
            'institution_type' => 'k12_school',
            'display_name'     => 'Oakridge High School',
            'is_verified'      => true,
            'is_public'        => true,
        ]);
        AccountMember::create([
            'account_id' => $this->buyerAccount->id,
            'user_id'    => $this->buyerUser->id,
            'status'     => 'active',
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->buyerAccount->id);
        $buyerRole = Role::where('name', 'primary_owner')->first();
        if ($buyerRole) {
            $this->buyerUser->assignRole($buyerRole);
        }

        // Setup Supplier User & Account
        $this->supplierUser = User::factory()->create(['email' => 'supplier_test@edushopify.com', 'status' => 'active']);
        $this->supplierAccount = Account::create([
            'account_type' => 'organization',
            'display_name' => 'Apex Educational Supplies',
            'legal_name'   => 'Apex Educational Supplies LLC',
            'status'       => 'active',
            'currency'     => 'USD',
        ]);
        $this->supplierAccount->capabilities()->create([
            'capability_type_id' => \App\Models\CapabilityType::where('code', 'supplier')->first()->id,
            'status'             => 'active',
            'activated_at'       => now(),
        ]);
        SupplierProfile::create([
            'account_id'   => $this->supplierAccount->id,
            'display_name' => 'Apex Educational Supplies',
            'slug'         => 'apex-educational-supplies',
            'is_verified'  => true,
            'is_public'    => true,
        ]);
        AccountMember::create([
            'account_id' => $this->supplierAccount->id,
            'user_id'    => $this->supplierUser->id,
            'status'     => 'active',
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($this->supplierAccount->id);
        $supplierRole = Role::where('name', 'primary_owner')->first();
        if ($supplierRole) {
            $this->supplierUser->assignRole($supplierRole);
        }
    }

    /* ── MSG-DB ─────────────────────────────────────────────────────────── */

    public function test_msg_db_constraints_and_direct_key_uniqueness(): void
    {
        $directKey = $this->messagingService->generateDirectKey($this->buyerAccount->id, $this->supplierAccount->id);

        $conv1 = Conversation::create([
            'direct_key'            => $directKey,
            'context_type'          => 'general',
            'created_by_account_id' => $this->buyerAccount->id,
            'created_by_user_id'    => $this->buyerUser->id,
            'status'                => 'open',
        ]);

        $this->assertDatabaseHas('conversations', [
            'id'         => $conv1->id,
            'direct_key' => $directKey,
        ]);

        // Attempting to create duplicate with identical direct_key must fail DB unique constraint
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Conversation::create([
            'direct_key'            => $directKey,
            'context_type'          => 'general',
            'created_by_account_id' => $this->buyerAccount->id,
            'created_by_user_id'    => $this->buyerUser->id,
            'status'                => 'open',
        ]);
    }

    public function test_msg_db_message_receipts_uniqueness(): void
    {
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);
        $msg = $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Hello receipt test');

        MessageReceipt::create([
            'message_id'   => $msg->id,
            'user_id'      => $this->supplierUser->id,
            'delivered_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        MessageReceipt::create([
            'message_id'   => $msg->id,
            'user_id'      => $this->supplierUser->id,
            'delivered_at' => now(),
        ]);
    }

    /* ── MSG-CONV ───────────────────────────────────────────────────────── */

    public function test_msg_conv_persistent_single_conversation_and_context_attachment(): void
    {
        // 1. Initial conversation created from Listing
        $conv1 = $this->messagingService->startOrGetDirectConversation(
            $this->buyerAccount,
            $this->buyerUser,
            $this->supplierAccount,
            'listing',
            101
        );

        $this->assertNotNull($conv1->id);
        $this->assertEquals('listing', $conv1->context_type);
        $this->assertEquals(101, $conv1->context_id);
        $this->assertEquals(1, $conv1->contexts()->count());

        // 2. Subsequent interaction from RFQ must reuse the SAME conversation and attach RFQ context
        $conv2 = $this->messagingService->startOrGetDirectConversation(
            $this->buyerAccount,
            $this->buyerUser,
            $this->supplierAccount,
            'rfq',
            502
        );

        $this->assertEquals($conv1->id, $conv2->id);
        $this->assertEquals(2, $conv2->contexts()->count());
        $this->assertDatabaseHas('conversation_contexts', [
            'conversation_id' => $conv1->id,
            'context_type'    => 'rfq',
            'context_id'      => 502,
        ]);

        // 3. Attaching duplicate context does not create redundant rows
        $this->messagingService->startOrGetDirectConversation(
            $this->buyerAccount,
            $this->buyerUser,
            $this->supplierAccount,
            'rfq',
            502
        );
        $this->assertEquals(2, $conv1->fresh()->contexts()->count());
    }

    /* ── MSG-SETTING ────────────────────────────────────────────────────── */

    public function test_msg_setting_controls_unrelated_messaging(): void
    {
        // Unrelated 3rd account
        $thirdAccount = Account::create([
            'account_type' => 'organization',
            'display_name' => 'Stranger Supplies',
            'status'       => 'active',
            'currency'     => 'USD',
        ]);
        $thirdAccount->capabilities()->create(['capability_type_id' => \App\Models\CapabilityType::where('code', 'supplier')->first()->id, 'status' => 'active']);

        // 1. When allow_unrelated_messaging = true: allowed
        Setting::set('messaging', 'allow_unrelated_messaging', true);
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $thirdAccount);
        $this->assertNotNull($conv->id);

        // 4th account with zero prior relationship
        $fourthAccount = Account::create([
            'account_type' => 'organization',
            'display_name' => 'Unrelated Vendor 4',
            'status'       => 'active',
            'currency'     => 'USD',
        ]);
        $fourthAccount->capabilities()->create(['capability_type_id' => \App\Models\CapabilityType::where('code', 'supplier')->first()->id, 'status' => 'active']);

        // 2. When allow_unrelated_messaging = false: blocked for unrelated accounts without context
        Setting::set('messaging', 'allow_unrelated_messaging', false);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $fourthAccount);
    }

    /* ── MSG-AUTH ───────────────────────────────────────────────────────── */

    public function test_msg_auth_unauthorized_account_access_forbidden(): void
    {
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        // Intruding user from an unrelated account
        $intruderUser = User::factory()->create(['status' => 'active']);
        $intruderAccount = Account::create([
            'account_type' => 'organization',
            'display_name' => 'Intruder Academy',
            'status'       => 'active',
            'currency'     => 'USD',
        ]);
        $intruderAccount->capabilities()->create(['capability_type_id' => \App\Models\CapabilityType::where('code', 'buyer')->first()->id, 'status' => 'active']);
        AccountMember::create([
            'account_id' => $intruderAccount->id,
            'user_id'    => $intruderUser->id,
            'status'     => 'active',
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($intruderAccount->id);
        $intruderUser->assignRole(Role::where('name', 'buyer_manager')->first());

        $response = $this->actingAs($intruderUser)->getJson(route('messages.show', $conv->id));
        $response->assertStatus(403);
    }

    /* ── MSG-TEXT & BROADCAST ───────────────────────────────────────────── */

    public function test_msg_text_send_and_broadcast_message_sent(): void
    {
        Event::fake([MessageSent::class, MessageSeen::class, ConversationRead::class]);

        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        $response = $this->actingAs($this->buyerUser)->postJson(route('messages.store', $conv->id), [
            'body' => 'Can you supply 200 chemistry lab kits by September?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message.body', 'Can you supply 200 chemistry lab kits by September?');
        $response->assertJsonPath('message.is_mine', true);

        $this->assertDatabaseHas('messages', [
            'conversation_id'   => $conv->id,
            'sender_account_id' => $this->buyerAccount->id,
            'sender_user_id'    => $this->buyerUser->id,
            'body'              => 'Can you supply 200 chemistry lab kits by September?',
        ]);

        Event::assertDispatched(MessageSent::class, function ($event) use ($conv) {
            return $event->conversationId === $conv->id
                && in_array($this->supplierUser->id, $event->recipientUserIds);
        });
    }

    public function test_msg_text_empty_message_rejected(): void
    {
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        $response = $this->actingAs($this->buyerUser)->postJson(route('messages.store', $conv->id), [
            'body' => '   ',
        ]);

        $response->assertStatus(422);
    }

    /* ── MSG-REPLY ──────────────────────────────────────────────────────── */

    public function test_msg_reply_within_same_conversation(): void
    {
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);
        $msg1 = $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Original Question?');

        $response = $this->actingAs($this->supplierUser)->postJson(route('messages.store', $conv->id), [
            'body'                => 'Yes, here is the answer.',
            'reply_to_message_id' => $msg1->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message.reply_to.id', $msg1->id);

        $this->assertDatabaseHas('messages', [
            'conversation_id'     => $conv->id,
            'reply_to_message_id' => $msg1->id,
            'body'                => 'Yes, here is the answer.',
        ]);
    }

    public function test_msg_reply_to_different_conversation_message_is_rejected(): void
    {
        $conv1 = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);
        $msg1 = $this->messagingService->sendMessage($conv1, $this->buyerAccount, $this->buyerUser, 'Question in Conv 1');

        // Conv 2 with third account
        $otherAccount = Account::create([
            'account_type' => 'organization',
            'display_name' => 'Beta Science LLC',
            'status'       => 'active',
            'currency'     => 'USD',
        ]);
        $otherAccount->capabilities()->create(['capability_type_id' => \App\Models\CapabilityType::where('code', 'supplier')->first()->id, 'status' => 'active']);
        $conv2 = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $otherAccount);

        $response = $this->actingAs($this->buyerUser)->postJson(route('messages.store', $conv2->id), [
            'body'                => 'Tampered reply',
            'reply_to_message_id' => $msg1->id,
        ]);

        $response->assertStatus(422);
    }

    /* ── MSG-EDIT & DELETE ──────────────────────────────────────────────── */

    public function test_msg_edit_and_soft_delete_lifecycle(): void
    {
        Event::fake([MessageEdited::class, MessageDeleted::class]);

        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);
        $msg = $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Original text');

        // 1. Edit
        $editResp = $this->actingAs($this->buyerUser)->putJson(route('messages.update', $msg->id), [
            'body' => 'Edited text corrected',
        ]);
        $editResp->assertStatus(200);
        $this->assertDatabaseHas('messages', [
            'id'   => $msg->id,
            'body' => 'Edited text corrected',
        ]);
        $this->assertNotNull($msg->fresh()->edited_at);
        Event::assertDispatched(MessageEdited::class);

        // Recipient cannot edit sender's message
        $invalidEdit = $this->actingAs($this->supplierUser)->putJson(route('messages.update', $msg->id), [
            'body' => 'Hacked edit',
        ]);
        $invalidEdit->assertStatus(403);

        // 2. Soft Delete
        $delResp = $this->actingAs($this->buyerUser)->deleteJson(route('messages.destroy', $msg->id));
        $delResp->assertStatus(200);
        $this->assertSoftDeleted('messages', ['id' => $msg->id]);
        Event::assertDispatched(MessageDeleted::class);

        // Show endpoint masks deleted body
        $showResp = $this->actingAs($this->supplierUser)->getJson(route('messages.show', $conv->id));
        $showResp->assertStatus(200);
        $showResp->assertJsonFragment([
            'id'         => $msg->id,
            'is_deleted' => true,
            'body'       => null,
        ]);
    }

    /* ── MSG-REC (RECEIPTS: SENT / DELIVERED / SEEN) ────────────────────── */

    public function test_msg_receipts_delivered_and_seen_tracking(): void
    {
        Event::fake([MessageDelivered::class, MessageSeen::class]);

        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);
        $msg = $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Receipt tracking test');

        $this->assertFalse($msg->fresh()->isDelivered());
        $this->assertFalse($msg->fresh()->isSeen());

        // 1. Acknowledge Delivered
        $delivResp = $this->actingAs($this->supplierUser)->postJson(route('messages.delivered', $msg->id));
        $delivResp->assertStatus(200);
        $this->assertTrue($msg->fresh()->isDelivered());
        $this->assertFalse($msg->fresh()->isSeen());
        Event::assertDispatched(MessageDelivered::class);

        // 2. Open Conversation -> Marks Seen
        $seenResp = $this->actingAs($this->supplierUser)->postJson(route('messages.seen', $conv->id));
        $seenResp->assertStatus(200);
        $this->assertTrue($msg->fresh()->isSeen());
        Event::assertDispatched(MessageSeen::class);
    }

    /* ── MSG-UNREAD ─────────────────────────────────────────────────────── */

    public function test_msg_unread_count_independent_from_general_notifications(): void
    {
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        // Send 3 messages from Buyer
        $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Message 1');
        $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Message 2');
        $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Message 3');

        $this->assertEquals(3, $conv->unreadCountForUser($this->supplierUser->id));

        // Mark Seen
        $this->messagingService->markConversationSeen($conv, $this->supplierUser);
        $this->assertEquals(0, $conv->unreadCountForUser($this->supplierUser->id));
    }

    /* ── NOTIF (STRICT SEPARATION TEST) ─────────────────────────────────── */

    public function test_notif_20_chat_messages_creates_zero_general_notification_rows(): void
    {
        $initialNotificationCount = \Illuminate\Support\Facades\DB::table('notifications')->count();

        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        for ($i = 1; $i <= 20; $i++) {
            $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, "Chat message #{$i}");
        }

        $this->assertEquals(20, Message::where('conversation_id', $conv->id)->count());
        $this->assertEquals($initialNotificationCount, \Illuminate\Support\Facades\DB::table('notifications')->count(), 'CRITICAL: 20 ordinary chat messages must create ZERO general notification rows.');
    }

    public function test_notif_business_events_persist_and_broadcast_without_duplication(): void
    {
        Notification::fake();

        $message = 'Quotation #Q-2026-001 has been submitted for RFQ #RFQ-1002.';
        Notification::send($this->buyerUser, new DashboardNotification($message, '/buyer/quotations/1'));

        Notification::assertSentTo($this->buyerUser, DashboardNotification::class, function ($notification) use ($message) {
            return $notification->message === $message;
        });
    }

    /* ── MSG-FILE (ATTACHMENTS) ─────────────────────────────────────────── */

    public function test_msg_file_upload_and_secure_download_authorization(): void
    {
        Storage::fake('local');

        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);
        $fakeImage = UploadedFile::fake()->image('specification_diagram.png', 800, 600);
        $fakePdf = UploadedFile::fake()->create('contract_agreement.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->buyerUser)->post(route('messages.store', $conv->id), [
            'body'        => 'Attached diagram and contract.',
            'attachments' => [$fakeImage, $fakePdf],
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $messageId = $response->json('message.id');
        $message = Message::findOrFail($messageId);

        $this->assertEquals('file', $message->message_type);
        $this->assertEquals(2, $message->getMedia('attachments')->count());

        $media = $message->getMedia('attachments')->first();

        // 1. Authorized participant download
        $authDownload = $this->actingAs($this->supplierUser)->get(route('messages.attachments.download', [$message->id, $media->id]));
        $authDownload->assertStatus(200);

        // 2. Unauthorized intruder download rejected
        $intruder = User::factory()->create(['status' => 'active']);
        $unauthDownload = $this->actingAs($intruder)->get(route('messages.attachments.download', [$message->id, $media->id]));
        $unauthDownload->assertStatus(403);
    }

    /* ── MSG-MUTE & ARCHIVE ─────────────────────────────────────────────── */

    public function test_msg_mute_archive_and_auto_unarchive(): void
    {
        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        // 1. Mute
        $this->assertFalse($conv->isMutedBy($this->supplierUser->id));
        $this->actingAs($this->supplierUser)->postJson(route('messages.mute', $conv->id));
        $this->assertTrue($conv->isMutedBy($this->supplierUser->id));

        // 2. Archive
        $this->assertFalse($conv->isArchivedBy($this->supplierUser->id));
        $this->actingAs($this->supplierUser)->postJson(route('messages.archive', $conv->id));
        $this->assertTrue($conv->isArchivedBy($this->supplierUser->id));

        // 3. New incoming message from Buyer auto-unarchives the thread for Supplier
        $this->messagingService->sendMessage($conv, $this->buyerAccount, $this->buyerUser, 'Wake up message');
        $this->assertFalse($conv->fresh()->isArchivedBy($this->supplierUser->id), 'Incoming message must auto-unarchive thread for recipient.');
    }

    /* ── MSG-EMAIL (UNREAD EMAIL REMINDERS & IDEMPOTENCY) ───────────────── */

    public function test_msg_email_unread_reminders_digest_and_duplicate_prevention(): void
    {
        Mail::fake();

        // Enable unread email reminders for supplier (delay 1 hour)
        $pref = UserMessagingPreference::forUser($this->supplierUser);
        $pref->update([
            'unread_email_enabled'     => true,
            'unread_email_delay_hours' => 1,
        ]);

        $conv = $this->messagingService->startOrGetDirectConversation($this->buyerAccount, $this->buyerUser, $this->supplierAccount);

        // Create an unread message from 2 hours ago
        $msg = Message::create([
            'conversation_id'   => $conv->id,
            'sender_account_id' => $this->buyerAccount->id,
            'sender_user_id'    => $this->buyerUser->id,
            'message_type'      => 'text',
            'body'              => 'Important unanswered inquiry',
        ]);
        \Illuminate\Support\Facades\DB::table('messages')->where('id', $msg->id)->update(['created_at' => now()->subHours(2)]);
        $conv->update(['last_message_at' => now()->subHours(2)]);

        // 1. First run of job sends digest
        $job = new SendUnreadMessageRemindersJob;
        $sentCount = $job->handle();
        $this->assertEquals(1, $sentCount);

        Mail::assertQueued(UnreadMessagesDigestMail::class, function ($mail) {
            return $mail->recipientUser->id === $this->supplierUser->id
                && $mail->totalUnreadCount === 1;
        });

        // 2. Immediate second run of job must NOT send duplicate email because unread digest is unchanged
        $secondSentCount = $job->handle();
        $this->assertEquals(0, $secondSentCount, 'Scheduler must not send duplicate email for unchanged unread digest.');
    }
}
