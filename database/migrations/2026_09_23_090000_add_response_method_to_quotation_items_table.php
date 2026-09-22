<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // Which of the 4 response methods the supplier used for this
            // item — informational (drives the buyer-facing badge and the
            // "document" quantity/price display special-case), not a
            // constraint: nullable/no default so existing rows stay legacy.
            $table->enum('response_method', ['marketplace', 'custom', 'copy_spec', 'document'])
                ->nullable()->after('is_optional_addon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('response_method');
        });
    }
};
