<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * conversation_contexts previously relied only on MessagingService::attachContext()'s
 * application-level firstOrCreate() to prevent duplicate business-context rows on the
 * same conversation — not race-safe under concurrent requests. context_id is never
 * actually null for a row created through the normal flow (attachContext() is only
 * called when both context_type and context_id are truthy — see
 * startOrGetDirectConversation()), so a plain composite unique index is sufficient
 * for every real code path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_contexts', function (Blueprint $table) {
            $table->unique(['conversation_id', 'context_type', 'context_id'], 'conv_contexts_unique');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_contexts', function (Blueprint $table) {
            $table->dropUnique('conv_contexts_unique');
        });
    }
};
