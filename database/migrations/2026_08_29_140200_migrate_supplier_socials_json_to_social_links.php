<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Moves every existing supplier_profiles.socials JSON entry into its own
     * social_links row before the column is dropped, so no applicant's
     * already-entered social links are lost. Old freeform "extra socials"
     * keys (typed by hand, slugified with underscores) are matched against
     * the platform list with punctuation stripped; anything that still
     * doesn't match a known platform is left out rather than guessed at.
     */
    public function up(): void
    {
        $platformIds = DB::table('social_platforms')->pluck('id', 'slug')
            ->mapWithKeys(fn ($id, $slug) => [preg_replace('/[^a-z0-9]/', '', strtolower($slug)) => $id]);

        $rows = DB::table('supplier_profiles')->whereNotNull('socials')->get(['account_id', 'socials']);
        $now = now();

        foreach ($rows as $row) {
            $socials = json_decode($row->socials, true);

            if (! is_array($socials)) {
                continue;
            }

            foreach ($socials as $key => $url) {
                if (! filled($url)) {
                    continue;
                }

                $normalizedKey = preg_replace('/[^a-z0-9]/', '', strtolower($key));
                $platformId = $platformIds[$normalizedKey] ?? null;

                if (! $platformId) {
                    continue;
                }

                DB::table('social_links')->insertOrIgnore([
                    // 'account', not the Account::class string — the app
                    // registers a morph map (AppServiceProvider) that stores
                    // Account rows under this alias.
                    'socialable_type' => 'account',
                    'socialable_id' => $row->account_id,
                    'social_platform_id' => $platformId,
                    'url' => $url,
                    'is_public' => true,
                    'is_verified' => false,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->dropColumn('socials');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_profiles', function (Blueprint $table) {
            $table->json('socials')->nullable();
        });
        // Data intentionally not restored on rollback — social_links rows
        // created by up() are left in place as the now-canonical copy.
    }
};
