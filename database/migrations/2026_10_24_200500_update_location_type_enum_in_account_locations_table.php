<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // 1. Expand enum to include 'head_office' while keeping 'primary' during data migration
            DB::statement("ALTER TABLE `account_locations` MODIFY COLUMN `location_type` ENUM('primary', 'head_office', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery') NOT NULL");

            // 2. Migrate existing records from 'primary' to 'head_office'
            DB::statement("UPDATE `account_locations` SET `location_type` = 'head_office' WHERE `location_type` = 'primary'");

            // 3. Finalize enum without legacy 'primary'
            DB::statement("ALTER TABLE `account_locations` MODIFY COLUMN `location_type` ENUM('head_office', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery') NOT NULL");

            return;
        }

        // SQLite / in-memory test database:
        DB::table('account_locations')->where('location_type', 'primary')->update(['location_type' => 'head_office']);

        Schema::table('account_locations', function (Blueprint $table) {
            $table->enum('location_type', [
                'head_office', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery'
            ])->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `account_locations` MODIFY COLUMN `location_type` ENUM('primary', 'head_office', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery') NOT NULL");
            DB::statement("UPDATE `account_locations` SET `location_type` = 'primary' WHERE `location_type` = 'head_office'");
            DB::statement("ALTER TABLE `account_locations` MODIFY COLUMN `location_type` ENUM('primary', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery') NOT NULL");

            return;
        }

        DB::table('account_locations')->where('location_type', 'head_office')->update(['location_type' => 'primary']);

        Schema::table('account_locations', function (Blueprint $table) {
            $table->enum('location_type', [
                'primary', 'registered_office', 'branch', 'warehouse', 'showroom', 'billing', 'delivery'
            ])->change();
        });
    }
};
