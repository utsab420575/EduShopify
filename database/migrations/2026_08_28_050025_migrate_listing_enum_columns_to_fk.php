<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new FK columns (nullable first so existing rows don't break)
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('pricing_type_id')->nullable()->after('description');
            $table->unsignedBigInteger('sales_mode_id')->nullable()->after('pricing_type_id');
            $table->unsignedBigInteger('listing_type_id')->nullable()->after('listing_type');
        });

        // Step 2: Backfill from old enum values → new IDs
        $pricingTypes = DB::table('pricing_types')->pluck('id', 'code');
        $salesModes   = DB::table('sales_modes')->pluck('id', 'code');
        $listingTypes = DB::table('listing_types')->pluck('id', 'code');

        DB::table('listings')->lazyById()->each(function ($listing) use ($pricingTypes, $salesModes, $listingTypes) {
            DB::table('listings')->where('id', $listing->id)->update([
                'pricing_type_id' => $pricingTypes[$listing->pricing_type] ?? null,
                'sales_mode_id'   => $salesModes[$listing->sales_mode]     ?? null,
                'listing_type_id' => $listingTypes[$listing->listing_type]  ?? null,
            ]);
        });

        // Step 3: Add foreign keys
        Schema::table('listings', function (Blueprint $table) {
            $table->foreign('pricing_type_id')->references('id')->on('pricing_types')->nullOnDelete();
            $table->foreign('sales_mode_id')->references('id')->on('sales_modes')->nullOnDelete();
            $table->foreign('listing_type_id')->references('id')->on('listing_types')->nullOnDelete();
        });

        // Step 4: Drop the old enum columns
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'sales_mode', 'listing_type']);
        });
    }

    public function down(): void
    {
        // Restore old enum columns
        Schema::table('listings', function (Blueprint $table) {
            $table->enum('listing_type', ['product', 'service'])->nullable()->after('created_by_user_id');
            $table->enum('pricing_type', ['fixed', 'quote_only', 'rfq_enabled'])->nullable()->after('description');
            $table->enum('sales_mode', ['rfq_only', 'direct_purchase', 'both'])->nullable()->after('pricing_type');
        });

        // Backfill from FK IDs → old enum strings
        $pricingTypes = DB::table('pricing_types')->pluck('code', 'id');
        $salesModes   = DB::table('sales_modes')->pluck('code', 'id');
        $listingTypes = DB::table('listing_types')->pluck('code', 'id');

        DB::table('listings')->lazyById()->each(function ($listing) use ($pricingTypes, $salesModes, $listingTypes) {
            DB::table('listings')->where('id', $listing->id)->update([
                'pricing_type' => $pricingTypes[$listing->pricing_type_id] ?? 'quote_only',
                'sales_mode'   => $salesModes[$listing->sales_mode_id]     ?? 'rfq_only',
                'listing_type' => $listingTypes[$listing->listing_type_id]  ?? 'product',
            ]);
        });

        // Drop FK columns
        Schema::table('listings', function (Blueprint $table) {
            $table->dropForeign(['pricing_type_id']);
            $table->dropForeign(['sales_mode_id']);
            $table->dropForeign(['listing_type_id']);
            $table->dropColumn(['pricing_type_id', 'sales_mode_id', 'listing_type_id']);
        });
    }
};
