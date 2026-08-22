<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_capabilities', function (Blueprint $table) {
            // 1. Add capability_type_id column first (must be nullable initially to avoid constraints on existing data, though here we migrate fresh)
            $table->unsignedBigInteger('capability_type_id')->nullable()->after('account_id');
            $table->foreign('capability_type_id')->references('id')->on('capability_types')->cascadeOnDelete();
        });

        Schema::table('account_capabilities', function (Blueprint $table) {
            // 2. Add the new unique constraint containing account_id. MySQL can now use this for foreign keys.
            $table->unique(['account_id', 'capability_type_id'], 'acc_caps_acct_cap_type_unique');
        });

        Schema::table('account_capabilities', function (Blueprint $table) {
            // 3. Now we can safely drop the old unique constraint and the capability column
            $table->dropUnique(['account_id', 'capability']);
            $table->dropColumn('capability');

            // 4. Change capability_type_id to non-nullable (since it is fully set up now)
            $table->unsignedBigInteger('capability_type_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('account_capabilities', function (Blueprint $table) {
            $table->enum('capability', ['buyer', 'supplier'])->nullable()->after('account_id');
        });

        Schema::table('account_capabilities', function (Blueprint $table) {
            $table->unique(['account_id', 'capability']);
        });

        Schema::table('account_capabilities', function (Blueprint $table) {
            $table->dropUnique('acc_caps_acct_cap_type_unique');
            $table->dropForeign(['capability_type_id']);
            $table->dropColumn('capability_type_id');
            $table->dropColumn('capability');
        });
    }
};
