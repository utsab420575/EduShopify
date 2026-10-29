<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * System Settings admin (/admin/system/settings): the settings table gets
     * a human label, a value type (how the admin form edits it) and a
     * description. Also adds the product video limits read by the product
     * wizard; existing values are never overwritten.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('label', 160)->nullable()->after('name');
            $table->string('type', 20)->default('text')->after('label'); // text | number | boolean | list
            $table->text('description')->nullable()->after('payload');
        });

        $now = now();
        $rows = [
            ['product_media', 'video_max_kb', 'number', 'Product video max size (KB)', 'Largest product video a supplier can upload, in KB (1024 KB = 1 MB). The server\'s PHP upload limit must be at least this big.', 2048],
            ['product_media', 'video_extensions', 'list', 'Product video file types', 'Video file extensions suppliers can upload for a product, comma separated (e.g. mp4, webm, mov).', ['mp4', 'webm']],
        ];
        foreach ($rows as [$group, $name, $type, $label, $description, $value]) {
            $exists = DB::table('settings')->where('group_name', $group)->where('name', $name)->exists();
            if (! $exists) {
                DB::table('settings')->insert([
                    'group_name' => $group, 'name' => $name, 'label' => $label, 'type' => $type,
                    'payload' => json_encode(['value' => $value]), 'description' => $description,
                    'locked' => false, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // Labels/types for the settings the code already reads.
        $known = [
            ['rfq', 'rfq_requires_admin_approval', 'boolean', 'RFQs require admin approval before publishing', 'When on, every new RFQ enters a pending_approval state until an admin approves it.'],
            ['capability', 'capability_application_max_attempts', 'number', 'Maximum capability application attempts', 'How many times a buyer or supplier may re-apply after a rejection.'],
            ['capability', 'buyer_application_review_days', 'number', 'Buyer application review days', null],
            ['capability', 'account_conversion_max_attempts', 'number', 'Account conversion max attempts', null],
            ['award', 'award_response_hours', 'number', 'Award response window (hours)', 'How long a supplier has to accept or reject an award before it expires.'],
            ['messaging', 'allow_unrelated_messaging', 'boolean', 'Allow unrelated messaging', 'Let users message accounts they have no RFQ/order relation with.'],
        ];
        foreach ($known as [$group, $name, $type, $label, $description]) {
            DB::table('settings')->where('group_name', $group)->where('name', $name)->whereNull('label')
                ->update(['label' => $label, 'type' => $type, 'description' => $description]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group_name', 'product_media')->whereIn('name', ['video_max_kb', 'video_extensions'])->delete();

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn(['label', 'type', 'description']);
        });
    }
};
