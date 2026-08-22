<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_documents', function (Blueprint $table) {
            // Make document_type_id nullable
            $table->unsignedBigInteger('document_type_id')->nullable()->change();
            // Add custom_name varchar column
            $table->string('custom_name')->nullable()->after('document_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_documents', function (Blueprint $table) {
            $table->dropColumn('custom_name');
            $table->unsignedBigInteger('document_type_id')->nullable(false)->change();
        });
    }
};
