<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. supplier_profiles: add legal_entity_type
        Schema::table('supplier_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('supplier_profiles', 'legal_entity_type')) {
                $table->string('legal_entity_type', 100)->nullable()->after('legal_name');
            }
        });

        // 2. supplier_documents: add current_document_type_id generated column & unique constraint
        // Raw expression indexes are a MySQL/MariaDB-only construct — SQLite's grammar
        // can't translate them (used only by production/dev; the sqlite test DB skips this).
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('supplier_documents', function (Blueprint $table) {
                $table->rawIndex(
                    '(CASE WHEN is_current = 1 THEN document_type_id ELSE NULL END)',
                    'supp_docs_current_type_index'
                );
            });

            // 3. subscriptions: add active_supplier_account_id generated column & unique constraint
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->rawIndex(
                    '(CASE WHEN status IN (\'active\',\'trialing\') THEN supplier_account_id ELSE NULL END)',
                    'subs_active_account_index'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('subs_active_account_index');
        });

        Schema::table('supplier_documents', function (Blueprint $table) {
            $table->dropIndex('supp_docs_current_type_index');
        });

        Schema::table('supplier_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_profiles', 'legal_entity_type')) {
                $table->dropColumn('legal_entity_type');
            }
        });
    }
};
