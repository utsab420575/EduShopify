<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Spec section 18.6 — rfq_public_summary.
     *
     * A view, not a table, so the public RFQ search has a projection that cannot
     * leak private data no matter how the query is written. It exposes only:
     * identifier, title, a general item/category summary, delivery geography,
     * the quotation deadline and the publication date.
     *
     * Deliberately NOT exposed: the buyer's identity or contact details, the
     * description, budget range, exact delivery address and coordinates, and
     * anything from rfq_items beyond aggregated category names.
     *
     * Only live, published, open RFQs appear.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS rfq_public_summary');

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
                r.id                    AS rfq_id,
                r.rfq_number            AS rfq_number,
                r.title                 AS title,
                r.status                AS status,
                r.visibility_type       AS visibility_type,
                r.currency_code         AS currency_code,
                r.quotation_deadline    AS quotation_deadline,
                r.qna_deadline          AS qna_deadline,
                r.expected_delivery_date AS expected_delivery_date,
                r.published_at          AS published_at,
                r.items_count           AS items_count,
                r.quotations_count      AS quotations_count,
                r.delivery_country_id   AS delivery_country_id,
                co.name                 AS delivery_country,
                r.delivery_state_id     AS delivery_state_id,
                st.name                 AS delivery_state,
                r.delivery_city_id      AS delivery_city_id,
                ci.name                 AS delivery_city,
                {$categorySummarySql}   AS category_summary,
                {$itemTypesSql}         AS item_types,
                (
                    SELECT COUNT(*) FROM rfq_items ri WHERE ri.rfq_id = r.id
                )                       AS item_count
            FROM rfqs r
            LEFT JOIN countries co ON co.id = r.delivery_country_id
            LEFT JOIN states    st ON st.id = r.delivery_state_id
            LEFT JOIN cities    ci ON ci.id = r.delivery_city_id
            WHERE r.deleted_at IS NULL
              AND r.published_at IS NOT NULL
              AND r.status = 'open'
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS rfq_public_summary');
    }
};
