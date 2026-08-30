<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_custom_value_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('attribute_custom_value_reviews', 'supplier_account_id')) {
                $table->unsignedBigInteger('supplier_account_id')->nullable()->after('custom_value');
                $table->foreign('supplier_account_id')->references('id')->on('accounts')->nullOnDelete();
            }

            if (! Schema::hasColumn('attribute_custom_value_reviews', 'first_listing_id')) {
                $table->unsignedBigInteger('first_listing_id')->nullable()->after('supplier_account_id');
                $table->foreign('first_listing_id')->references('id')->on('listings')->nullOnDelete();
            }

            if (! Schema::hasColumn('attribute_custom_value_reviews', 'submitted_by_user_id')) {
                $table->unsignedBigInteger('submitted_by_user_id')->nullable()->after('first_listing_id');
                $table->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('attribute_custom_value_reviews', 'usage_count')) {
                $table->unsignedInteger('usage_count')->default(1)->after('submitted_by_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attribute_custom_value_reviews', function (Blueprint $table) {
            $cols = ['supplier_account_id', 'first_listing_id', 'submitted_by_user_id', 'usage_count'];
            $existing = array_intersect($cols, Schema::getColumnListing('attribute_custom_value_reviews'));
            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
