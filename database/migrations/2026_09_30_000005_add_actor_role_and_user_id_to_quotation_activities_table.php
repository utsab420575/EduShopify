<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_activities', function (Blueprint $table) {
            $table->string('actor_role', 20)->default('supplier')->after('quotation_id');
            $table->foreignId('user_id')->nullable()->after('actor_role')->constrained('users')->nullOnDelete();
            $table->index(['quotation_id', 'actor_role']);
        });

        // Expand activity_type column to support 'shortlisted', 'awarded', etc.
        try {
            DB::statement("ALTER TABLE quotation_activities MODIFY COLUMN activity_type VARCHAR(50) NOT NULL");
        } catch (\Throwable $e) {
            // In case of sqlite or non-mysql drivers during test
        }

        // Backfill existing rows with appropriate actor_role
        DB::table('quotation_activities')
            ->whereIn('activity_type', ['viewed_by_buyer', 'buyer_messaged', 'buyer_requested_revision', 'accepted', 'rejected', 'shortlisted', 'awarded'])
            ->update(['actor_role' => 'buyer']);

        DB::table('quotation_activities')
            ->whereIn('activity_type', ['drafted', 'submitted', 'unsubmitted', 'quotation_updated', 'supplier_replied'])
            ->update(['actor_role' => 'supplier']);

        DB::table('quotation_activities')
            ->whereIn('activity_type', ['expired'])
            ->update(['actor_role' => 'system']);
    }

    public function down(): void
    {
        Schema::table('quotation_activities', function (Blueprint $table) {
            $table->dropIndex(['quotation_id', 'actor_role']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['actor_role', 'user_id']);
        });
    }
};
