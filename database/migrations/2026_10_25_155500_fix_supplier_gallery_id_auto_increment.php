<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The supplier_gallery.id column was created without AUTO_INCREMENT on
     * production, causing SQLSTATE[HY000]: General error: 1364 Field 'id'
     * doesn't have a default value on insert.
     *
     * Blueprint::id() should have created BIGINT UNSIGNED AUTO_INCREMENT, but
     * the column reached production without that attribute. This migration
     * corrects it with a raw ALTER TABLE.
     */
    public function up(): void
    {
        // MySQL-only syntax (and a MySQL-only problem) — skipped on other
        // drivers, e.g. the SQLite database the test suite runs on.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `supplier_gallery` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `supplier_gallery` MODIFY `id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
