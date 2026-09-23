<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quotation_activities MODIFY COLUMN activity_type ENUM('drafted', 'submitted', 'viewed_by_buyer', 'buyer_messaged', 'supplier_replied', 'buyer_requested_revision', 'quotation_updated', 'accepted', 'rejected', 'expired') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quotation_activities MODIFY COLUMN activity_type ENUM('submitted', 'viewed_by_buyer', 'buyer_messaged', 'supplier_replied', 'buyer_requested_revision', 'quotation_updated', 'accepted', 'rejected', 'expired') NOT NULL");
        }
    }
};
