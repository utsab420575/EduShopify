<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the 3 default pricing types
        DB::table('pricing_types')->insert([
            ['name' => 'Fixed Catalog Price',  'code' => 'fixed',       'description' => 'Direct purchase with a transparent base price.', 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quote Only / Custom',  'code' => 'quote_only',  'description' => 'Buyers request a custom quotation. No public price shown.', 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fixed Price + RFQ',    'code' => 'rfq_enabled', 'description' => 'Both direct purchase and custom RFQ supported.', 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_types');
    }
};
