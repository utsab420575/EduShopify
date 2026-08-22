<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('attribute_group_id')->nullable()->after('id');
            $table->index('attribute_group_id');
            $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_group_id']);
            $table->dropIndex(['attribute_group_id']);
            $table->dropColumn('attribute_group_id');
        });
    }
};
