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
            DB::statement("ALTER TABLE `supplier_service_areas` MODIFY COLUMN `area_level` ENUM('global', 'country', 'state', 'city', 'radius') NOT NULL");

            return;
        }

        Schema::table('supplier_service_areas', function (Blueprint $table) {
            $table->enum('area_level', ['global', 'country', 'state', 'city', 'radius'])->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `supplier_service_areas` MODIFY COLUMN `area_level` ENUM('country', 'state', 'city', 'radius') NOT NULL");

            return;
        }

        Schema::table('supplier_service_areas', function (Blueprint $table) {
            $table->enum('area_level', ['country', 'state', 'city', 'radius'])->change();
        });
    }
};
