<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stores each category's depth in the tree (root = 0) as a real column
 * instead of recomputing it by walking parent_id every time. Set once at
 * creation (CategoryController::store(), CategoryImportService) and kept
 * in sync on manual re-parenting (CategoryController::update()) — this
 * migration backfills it for whatever hierarchy already exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('level')->default(0)->after('parent_id');
        });

        // Backfill breadth-first: every root is level 0, then walk down one
        // generation at a time so a child's level is always set from an
        // already-correct parent level.
        $level = 0;
        $ids = DB::table('categories')->whereNull('parent_id')->pluck('id')->all();

        while (! empty($ids)) {
            DB::table('categories')->whereIn('id', $ids)->update(['level' => $level]);

            $ids = DB::table('categories')->whereIn('parent_id', $ids)->pluck('id')->all();
            $level++;
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
