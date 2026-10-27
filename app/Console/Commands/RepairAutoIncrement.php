<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Restores AUTO_INCREMENT on integer `id` primary keys that lost it — e.g.
 * after a database dump was imported without that attribute. Symptom:
 * "SQLSTATE[HY000]: 1364 Field 'id' doesn't have a default value" on insert,
 * including Laravel's own insert into `migrations`, which is why this can't
 * be a normal migration and runs before `migrate` in /system-refresh.
 *
 * Idempotent: a no-op once every table is fixed. A table holding an id = 0
 * row is skipped — adding AUTO_INCREMENT would renumber that row and break
 * anything referencing it — and must be fixed by hand.
 */
class RepairAutoIncrement extends Command
{
    protected $signature = 'db:repair-auto-increment {--dry-run : Only list the affected tables}';

    protected $description = 'Restore AUTO_INCREMENT on integer id primary keys that are missing it';

    public function handle(): int
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->info('Not a MySQL connection — nothing to do.');

            return self::SUCCESS;
        }

        $columns = DB::select("
            SELECT TABLE_NAME AS tbl, COLUMN_TYPE AS type, COLUMN_COMMENT AS comment
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id' AND COLUMN_KEY = 'PRI'
              AND DATA_TYPE IN ('tinyint', 'smallint', 'mediumint', 'int', 'bigint')
              AND EXTRA NOT LIKE '%auto_increment%'
            ORDER BY TABLE_NAME
        ");

        if (empty($columns)) {
            $this->info('All integer id columns already have AUTO_INCREMENT.');

            return self::SUCCESS;
        }

        $this->info(count($columns).' table(s) missing AUTO_INCREMENT on id.');

        if ($this->option('dry-run')) {
            foreach ($columns as $column) {
                $this->line("  - {$column->tbl} ({$column->type})");
            }

            return self::SUCCESS;
        }

        $fixed = 0;
        $failed = [];
        $sqlMode = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;

        // FK checks off: these ids are referenced by foreign keys. Relaxed
        // sql_mode: some tables carry legacy '0000-00-00' date defaults that
        // strict mode refuses to re-validate during the table rebuild.
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement("SET SESSION sql_mode = ''");

        try {
            foreach ($columns as $column) {
                if (DB::table($column->tbl)->where('id', 0)->exists()) {
                    $failed[$column->tbl] = 'has a row with id = 0 — fix that row first';

                    continue;
                }

                $comment = $column->comment !== '' ? ' COMMENT '.DB::getPdo()->quote($column->comment) : '';

                try {
                    DB::statement("ALTER TABLE `{$column->tbl}` MODIFY `id` {$column->type} NOT NULL AUTO_INCREMENT{$comment}");
                    $fixed++;
                } catch (Throwable $e) {
                    $failed[$column->tbl] = $e->getMessage();
                }
            }
        } finally {
            DB::statement('SET SESSION sql_mode = '.DB::getPdo()->quote($sqlMode));
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        $this->info("Fixed {$fixed} table(s).");

        foreach ($failed as $table => $reason) {
            $this->error("  ! {$table}: {$reason}");
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
