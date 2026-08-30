<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->unsignedBigInteger('input_type_id')->nullable()->after('input_type');
            $table->boolean('allow_custom_value')->default(false)->after('is_required');

            $table->index('input_type_id');
            $table->foreign('input_type_id')->references('id')->on('input_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['input_type_id']);
            $table->dropIndex(['input_type_id']);
            $table->dropColumn(['input_type_id', 'allow_custom_value']);
        });
    }
};
