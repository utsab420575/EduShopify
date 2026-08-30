<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add direct_key to conversations ────────────────────────────────
        if (Schema::hasTable('conversations') && ! Schema::hasColumn('conversations', 'direct_key')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->string('direct_key', 64)->nullable()->unique()->after('id');
            });
        }

        // ── 2. Backfill direct_key for existing two-account conversations ─────
        if (Schema::hasTable('conversations') && Schema::hasTable('conversation_accounts')) {
            $conversations = DB::table('conversations')
                ->select('id', 'context_type', 'context_id', 'created_at')
                ->orderByDesc('id')
                ->get();

            $assignedKeys = [];

            foreach ($conversations as $conv) {
                $accounts = DB::table('conversation_accounts')
                    ->where('conversation_id', $conv->id)
                    ->pluck('account_id')
                    ->sort()
                    ->values()
                    ->all();

                if (count($accounts) === 2) {
                    $key = hash('sha256', $accounts[0].':'.$accounts[1]);

                    if (! isset($assignedKeys[$key])) {
                        $assignedKeys[$key] = $conv->id;
                        DB::table('conversations')->where('id', $conv->id)->update(['direct_key' => $key]);
                    } else {
                        // Duplicate conversation found between the same account pair
                        Log::warning("Messaging Migration: Duplicate conversation ID {$conv->id} found between accounts {$accounts[0]} and {$accounts[1]}. Latest conversation ID {$assignedKeys[$key]} received direct_key; older conversation preserved with direct_key = NULL.");
                    }
                }
            }
        }

        // ── 3. Create conversation_contexts table ─────────────────────────────
        if (! Schema::hasTable('conversation_contexts')) {
            Schema::create('conversation_contexts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id');
                $table->string('context_type', 50); // listing, rfq, quotation, purchase_order, support
                $table->unsignedBigInteger('context_id')->nullable();
                $table->unsignedBigInteger('added_by_user_id')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'context_type', 'context_id'], 'conv_contexts_lookup_idx');
                $table->foreign('conversation_id')->references('id')->on('conversations')->cascadeOnDelete();
                $table->foreign('added_by_user_id')->references('id')->on('users')->nullOnDelete();
            });

            // Backfill existing specific contexts (listing, rfq, quotation, purchase_order)
            if (Schema::hasTable('conversations')) {
                $existingContexts = DB::table('conversations')
                    ->whereNotNull('context_id')
                    ->whereNotIn('context_type', ['general', 'support'])
                    ->select('id as conversation_id', 'context_type', 'context_id', 'created_by_user_id as added_by_user_id', 'created_at', 'updated_at')
                    ->get();

                foreach ($existingContexts as $ctx) {
                    DB::table('conversation_contexts')->insert([
                        'conversation_id'   => $ctx->conversation_id,
                        'context_type'      => $ctx->context_type,
                        'context_id'        => $ctx->context_id,
                        'added_by_user_id'  => $ctx->added_by_user_id,
                        'created_at'        => $ctx->created_at ?? now(),
                        'updated_at'        => $ctx->updated_at ?? now(),
                    ]);
                }
            }
        }

        // ── 4. Add reply_to_message_id to messages ────────────────────────────
        if (Schema::hasTable('messages') && ! Schema::hasColumn('messages', 'reply_to_message_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->unsignedBigInteger('reply_to_message_id')->nullable()->after('conversation_id');
                $table->foreign('reply_to_message_id')->references('id')->on('messages')->nullOnDelete();
            });
        }

        // ── 5. Create message_receipts table ──────────────────────────────────
        if (! Schema::hasTable('message_receipts')) {
            Schema::create('message_receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('seen_at')->nullable();
                $table->timestamps();

                $table->unique(['message_id', 'user_id'], 'msg_receipts_user_unique');
                $table->index(['message_id', 'delivered_at']);
                $table->index(['message_id', 'seen_at']);

                $table->foreign('message_id')->references('id')->on('messages')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // ── 6. Create user_messaging_preferences table ────────────────────────
        if (! Schema::hasTable('user_messaging_preferences')) {
            Schema::create('user_messaging_preferences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->boolean('sound_enabled')->default(true);
                $table->boolean('browser_notifications_enabled')->default(false);
                $table->boolean('unread_email_enabled')->default(false);
                $table->unsignedInteger('unread_email_delay_hours')->nullable()->default(24);
                $table->timestamp('last_reminder_sent_at')->nullable();
                $table->string('last_digest_hash')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_messaging_preferences');
        Schema::dropIfExists('message_receipts');

        if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'reply_to_message_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['reply_to_message_id']);
                $table->dropColumn('reply_to_message_id');
            });
        }

        Schema::dropIfExists('conversation_contexts');

        if (Schema::hasTable('conversations') && Schema::hasColumn('conversations', 'direct_key')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropUnique(['direct_key']);
                $table->dropColumn('direct_key');
            });
        }
    }
};
