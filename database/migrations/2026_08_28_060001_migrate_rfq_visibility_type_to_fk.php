<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Drop rfq_public_summary view before altering rfqs table ────────
        DB::statement('DROP VIEW IF EXISTS rfq_public_summary');

        // ── 2. Add visibility_type_id column ──────────────────────────────────
        if (Schema::hasTable('rfqs') && ! Schema::hasColumn('rfqs', 'visibility_type_id')) {
            Schema::table('rfqs', function (Blueprint $table) {
                $table->unsignedBigInteger('visibility_type_id')->nullable()->after('created_by_user_id');
            });
        }

        // ── 3. Migrate existing data from visibility_type enum to FK ──────────
        $openId    = DB::table('visibility_types')->where('code', 'open_matching')->value('id');
        $directId  = DB::table('visibility_types')->where('code', 'direct')->value('id');
        $invitedId = DB::table('visibility_types')->where('code', 'invited')->value('id');

        if (Schema::hasColumn('rfqs', 'visibility_type')) {
            if ($openId) {
                DB::table('rfqs')->where('visibility_type', 'global')->update(['visibility_type_id' => $openId]);
            }

            if ($invitedId) {
                DB::table('rfqs')->where('visibility_type', 'selected_suppliers')->update(['visibility_type_id' => $invitedId]);
            }
        }

        // Ensure any remaining NULL visibility_type_id is populated
        if ($openId) {
            DB::table('rfqs')->whereNull('visibility_type_id')->update(['visibility_type_id' => $openId]);
        }

        // ── 4. Set NOT NULL & Add Foreign Key Constraint ──────────────────────
        Schema::table('rfqs', function (Blueprint $table) {
            $table->unsignedBigInteger('visibility_type_id')->nullable(false)->change();

            $table->foreign('visibility_type_id', 'rfqs_visibility_type_id_fk')
                ->references('id')
                ->on('visibility_types')
                ->restrictOnDelete();

            if (Schema::hasColumn('rfqs', 'visibility_type')) {
                $table->dropColumn('visibility_type');
            }
        });

        // ── 5. Recreate rfq_public_summary database view ──────────────────────
        $driver = DB::getDriverName();
        $categorySummarySql = $driver === 'sqlite'
            ? "(SELECT GROUP_CONCAT(DISTINCT cat.name) FROM rfq_items ri JOIN categories cat ON cat.id = ri.category_id WHERE ri.rfq_id = r.id)"
            : "(SELECT GROUP_CONCAT(DISTINCT cat.name ORDER BY cat.name SEPARATOR ', ') FROM rfq_items ri JOIN categories cat ON cat.id = ri.category_id WHERE ri.rfq_id = r.id)";

        $itemTypesSql = $driver === 'sqlite'
            ? "(SELECT GROUP_CONCAT(DISTINCT ri.item_type) FROM rfq_items ri WHERE ri.rfq_id = r.id)"
            : "(SELECT GROUP_CONCAT(DISTINCT ri.item_type ORDER BY ri.item_type SEPARATOR ', ') FROM rfq_items ri WHERE ri.rfq_id = r.id)";

        DB::statement("
            CREATE VIEW rfq_public_summary AS
            SELECT
                r.id                     AS rfq_id,
                r.rfq_number             AS rfq_number,
                r.title                  AS title,
                r.status                 AS status,
                r.visibility_type_id     AS visibility_type_id,
                vt.code                  AS visibility_type,
                vt.engine_type           AS visibility_engine_type,
                r.currency_code          AS currency_code,
                r.quotation_deadline     AS quotation_deadline,
                r.qna_deadline           AS qna_deadline,
                r.expected_delivery_date AS expected_delivery_date,
                r.published_at           AS published_at,
                r.items_count            AS items_count,
                r.quotations_count       AS quotations_count,
                r.delivery_country_id    AS delivery_country_id,
                co.name                  AS delivery_country,
                r.delivery_state_id      AS delivery_state_id,
                st.name                  AS delivery_state,
                r.delivery_city_id       AS delivery_city_id,
                ci.name                  AS delivery_city,
                {$categorySummarySql}    AS category_summary,
                {$itemTypesSql}          AS item_types,
                (
                    SELECT COUNT(*) FROM rfq_items ri WHERE ri.rfq_id = r.id
                )                        AS item_count
            FROM rfqs r
            LEFT JOIN visibility_types vt ON vt.id = r.visibility_type_id
            LEFT JOIN countries        co ON co.id = r.delivery_country_id
            LEFT JOIN states           st ON st.id = r.delivery_state_id
            LEFT JOIN cities           ci ON ci.id = r.delivery_city_id
            WHERE r.deleted_at IS NULL
              AND r.published_at IS NOT NULL
              AND r.status = 'open'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS rfq_public_summary');

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropForeign('rfqs_visibility_type_id_fk');
            $table->dropColumn('visibility_type_id');
            $table->enum('visibility_type', ['global', 'selected_suppliers'])->default('global')->after('created_by_user_id');
        });
    }
};
