<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This is the Spatie Laravel Permission migration extended with V4.3 extra
     * columns on the permissions and roles tables as specified in the schema doc.
     *
     * IMPORTANT: teams = true and team_foreign_key = 'account_id' must be set
     * in config/permission.php BEFORE this migration runs.
     *
     * Note: roles.account_id FK to accounts.id cannot be declared here because
     * the accounts table is created in a later migration. A separate migration
     * (create_roles_account_fk) adds that constraint after accounts exists.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        // ----------------------------------------------------------------
        // 4.1  permissions
        // V4.3: extended with display_name, group_name, capability_scope,
        //       description, is_sensitive, is_owner_only, is_assignable,
        //       is_active, sort_order.
        // ----------------------------------------------------------------
        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Spatie required
            $table->string('guard_name'); // Spatie required

            // V4.3 extra columns (spec section 4.1)
            $table->string('display_name', 180)->nullable();
            $table->string('group_name', 120)->nullable();
            $table->enum('capability_scope', ['platform', 'common', 'buyer', 'supplier', 'both'])
                  ->default('common');
            $table->text('description')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_owner_only')->default(false);
            $table->boolean('is_assignable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['name', 'guard_name']);

            // V4.3 indexes
            $table->index('group_name');
            $table->index('capability_scope');
            $table->index(['is_active', 'sort_order']);
            $table->index('is_owner_only');
        });

        // ----------------------------------------------------------------
        // 4.2  roles
        // V4.3: account_id is the Spatie team key (= null for global roles).
        //       Extended with display_name, capability_scope, description,
        //       is_system, is_owner_role, is_active, created_by_user_id.
        //
        //       IMPORTANT: the FK account_id -> accounts.id is intentionally
        //       omitted here. It is added in a later migration after the
        //       accounts table is created (circular dependency).
        // ----------------------------------------------------------------
        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->id();

            // Spatie Teams key — nullable because global roles have account_id = NULL
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name');        // Spatie required
            $table->string('guard_name'); // Spatie required

            // V4.3 extra columns (spec section 4.2)
            $table->string('display_name', 180)->nullable();
            $table->enum('capability_scope', ['platform', 'common', 'buyer', 'supplier', 'both'])
                  ->default('common');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_owner_role')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->timestamps();

            // Spatie Teams unique constraint
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }

            // V4.3 indexes
            $table->index('capability_scope');
            $table->index('is_system');
            $table->index('is_active');

            // created_by_user_id FK to users (users table precedes this migration)
            $table->foreign('created_by_user_id')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        // ----------------------------------------------------------------
        // 4.4  model_has_permissions
        // Spatie Teams: includes account_id as part of composite PK.
        // ----------------------------------------------------------------
        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        // ----------------------------------------------------------------
        // 4.3  model_has_roles
        // Spatie Teams: includes account_id as part of composite PK.
        // ----------------------------------------------------------------
        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        // ----------------------------------------------------------------
        // 4.5  role_has_permissions
        // ----------------------------------------------------------------
        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Flush Spatie permission cache — wrapped in try/catch because the cache
        // table (database driver) may not exist yet on the first migration run.
        try {
            app('cache')
                ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
                ->forget(config('permission.cache.key'));
        } catch (\Exception $e) {
            // Cache table not yet created — safe to ignore during initial migration.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};

