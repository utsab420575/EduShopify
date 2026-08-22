<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_type_enables', function (Blueprint $table) {
            $table->boolean('is_required')->default(true)->after('capability_type_id');
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->boolean('is_required')->default(true)->after('slug');
        });

        Schema::table('document_type_enables', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
};
