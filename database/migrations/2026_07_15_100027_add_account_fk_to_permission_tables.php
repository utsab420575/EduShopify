<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the deferred foreign key constraints from the Spatie permission tables
     * to the accounts table.
     *
     * These FKs could not be declared in the create_permission_tables migration
     * because accounts did not exist yet at that point.
     *
     * Covers (V4.3 spec sections 4.2, 4.3, 4.4):
     *   roles.account_id              → accounts.id  ON DELETE CASCADE
     *   model_has_roles.account_id    → accounts.id  ON DELETE CASCADE
     *   model_has_permissions.account_id → accounts.id ON DELETE CASCADE
     */
    public function up(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey     = $columnNames['team_foreign_key']; // 'account_id'

        // roles.account_id -> accounts.id
        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
            $table->foreign($teamKey, 'roles_account_id_foreign')
                  ->references('id')
                  ->on('accounts')
                  ->cascadeOnDelete();

            // V4.3 spec: index(account_id) on roles
            $table->index($teamKey, 'roles_account_id_index');
        });

        // model_has_roles.account_id -> accounts.id
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey) {
            $table->foreign($teamKey, 'model_has_roles_account_id_foreign')
                  ->references('id')
                  ->on('accounts')
                  ->cascadeOnDelete();
        });

        // model_has_permissions.account_id -> accounts.id
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey) {
            $table->foreign($teamKey, 'model_has_permissions_account_id_foreign')
                  ->references('id')
                  ->on('accounts')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamKey     = $columnNames['team_foreign_key'];

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey) {
            $table->dropForeign('model_has_permissions_account_id_foreign');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey) {
            $table->dropForeign('model_has_roles_account_id_foreign');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamKey) {
            $table->dropIndex('roles_account_id_index');
            $table->dropForeign('roles_account_id_foreign');
        });
    }
};
