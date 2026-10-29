<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * System Settings: how many photos and videos one product can have
     * (read by ProductMediaLimits). Existing values are never overwritten.
     */
    public function up(): void
    {
        $now = now();
        $rows = [
            ['max_images', 'Product photos per product', 'Most photos a supplier can add to one product.', 15],
            ['max_videos', 'Product videos per product', 'Most videos a supplier can add to one product (0 turns product videos off).', 5],
        ];

        foreach ($rows as [$name, $label, $description, $value]) {
            if (! DB::table('settings')->where('group_name', 'product_media')->where('name', $name)->exists()) {
                DB::table('settings')->insert([
                    'group_name' => 'product_media', 'name' => $name, 'label' => $label, 'type' => 'number',
                    'payload' => json_encode(['value' => $value]), 'description' => $description,
                    'locked' => false, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group_name', 'product_media')->whereIn('name', ['max_images', 'max_videos'])->delete();
    }
};
