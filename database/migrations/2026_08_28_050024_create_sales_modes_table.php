<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_modes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the 3 default sales modes
        DB::table('sales_modes')->insert([
            ['name' => 'RFQ Only',        'code' => 'rfq_only',       'description' => 'Buyers can only submit RFQs; no direct purchase.',          'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direct Purchase', 'code' => 'direct_purchase','description' => 'Buyers can purchase directly from the catalog.',            'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Both',            'code' => 'both',            'description' => 'Both direct purchase and RFQ submission are available.',    'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_modes');
    }
};
