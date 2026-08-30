<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visibility_types')) {
            Schema::create('visibility_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->enum('engine_type', ['invited', 'open'])->default('open');
                $table->unsignedInteger('max_suppliers')->nullable();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default visibility types
            DB::table('visibility_types')->insert([
                [
                    'name'           => 'Direct RFQ',
                    'code'           => 'direct',
                    'engine_type'    => 'invited',
                    'max_suppliers'  => 1,
                    'description'    => 'Send directly to 1 specific supplier for single-source negotiation.',
                    'sort_order'     => 1,
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'name'           => 'Invited RFQ',
                    'code'           => 'invited',
                    'engine_type'    => 'invited',
                    'max_suppliers'  => null,
                    'description'    => 'Send to a curated shortlist of selected suppliers.',
                    'sort_order'     => 2,
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'name'           => 'Open RFQ',
                    'code'           => 'open_matching',
                    'engine_type'    => 'open',
                    'max_suppliers'  => null,
                    'description'    => 'Make available on the marketplace to eligible matching suppliers.',
                    'sort_order'     => 3,
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
                [
                    'name'           => 'All Suppliers',
                    'code'           => 'broadcast_all',
                    'engine_type'    => 'open',
                    'max_suppliers'  => null,
                    'description'    => 'Broadcast across the marketplace to all registered verified suppliers.',
                    'sort_order'     => 4,
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visibility_types');
    }
};
