<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops redundant location fields from supplier_profiles (country_id,
     * state_id, city_id, address). The account_locations table is now the
     * single source of truth for supplier physical facilities/head office.
     */
    public function up(): void
    {
        // 1. Backfill any missing account_locations from supplier_profiles before dropping
        $profiles = DB::table('supplier_profiles')
            ->whereNotNull('country_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('account_locations')
                    ->whereColumn('account_locations.account_id', 'supplier_profiles.account_id');
            })
            ->get();

        foreach ($profiles as $profile) {
            DB::table('account_locations')->insert([
                'account_id' => $profile->account_id,
                'location_type' => 'head_office',
                'label' => 'Head Office',
                'address_line_1' => $profile->address ?: 'Main Office',
                'country_id' => $profile->country_id,
                'state_id' => $profile->state_id,
                'city_id' => $profile->city_id,
                'is_primary' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Drop foreign keys and redundant location columns
        Schema::table('supplier_profiles', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                $table->dropForeign(['country_id']);
                $table->dropForeign(['state_id']);
                $table->dropForeign(['city_id']);
            }
            $table->dropColumn(['country_id', 'state_id', 'city_id', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('support_email');
            $table->foreignId('state_id')->nullable()->after('country_id');
            $table->foreignId('city_id')->nullable()->after('state_id');
            $table->text('address')->nullable()->after('city_id');
        });

        // Restore data from primary account location
        $primaryLocations = DB::table('account_locations')
            ->where('is_primary', true)
            ->get();

        foreach ($primaryLocations as $loc) {
            DB::table('supplier_profiles')
                ->where('account_id', $loc->account_id)
                ->update([
                    'country_id' => $loc->country_id,
                    'state_id' => $loc->state_id,
                    'city_id' => $loc->city_id,
                    'address' => $loc->address_line_1,
                ]);
        }
    }
};
