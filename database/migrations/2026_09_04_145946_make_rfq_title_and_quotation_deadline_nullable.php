<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The RFQ wizard now autosaves a real draft row after step 1 (items only —
 * title and quotation_deadline aren't collected until steps 2/3), so both
 * columns must accept NULL. RfqService::publish() enforces both are present
 * before a draft can actually go live, so no RFQ can ever publish without them.
 *
 * rfq_public_summary is a view over rfqs, so — same as
 * 2026_08_28_060001_migrate_rfq_visibility_type_to_fk.php already had to do
 * for this exact table — it must be dropped before altering rfqs and
 * recreated after, on every driver (SQLite's column-change support rebuilds
 * the table under the hood, which breaks a dependent view; MySQL doesn't
 * strictly require it, but the same steps are harmless there too).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS rfq_public_summary');

        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->timestamp('quotation_deadline')->nullable()->change();
        });

        $this->recreateView();
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS rfq_public_summary');

        DB::table('rfqs')->whereNull('title')->update(['title' => '']);
        DB::table('rfqs')->whereNull('quotation_deadline')->delete();

        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->timestamp('quotation_deadline')->nullable(false)->change();
        });

        $this->recreateView();
    }

    /**
     * Verbatim from 2026_08_28_060001_migrate_rfq_visibility_type_to_fk.php
     * — the current, authoritative definition of this view.
     */
    private function recreateView(): void
    {
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
};
