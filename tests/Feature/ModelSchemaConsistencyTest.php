<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;
use Throwable;

/**
 * Guards the Eloquent layer against schema drift.
 *
 * The migrations are the source of truth. Every model must describe the table
 * it actually maps to: real relationships, real fillable columns, casts that
 * match column types, SoftDeletes only where deleted_at exists, and automatic
 * timestamps only where both timestamp columns exist.
 *
 * Each test walks every model and reports all offenders at once, so a single
 * run tells you everything that drifted rather than only the first failure.
 */
class ModelSchemaConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string, list<string>> table => columns */
    private array $columns = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Schema::getTables() as $table) {
            $name = $table['name'];
            $this->columns[$name] = Schema::getColumnListing($name);
        }

        // Some models (e.g. RfqPublicSummary) intentionally map to a read-only
        // DB VIEW rather than a table (see the rfq_public_summary migration) —
        // Schema::getTables() does not include views, so register those too.
        foreach (Schema::getViews() as $view) {
            $name = $view['name'];
            $this->columns[$name] = Schema::getColumnListing($name);
        }
    }

    /**
     * @return array<string, class-string<Model>>
     */
    private function models(): array
    {
        $models = [];

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (class_exists($class) && is_subclass_of($class, Model::class)) {
                $models[class_basename($class)] = $class;
            }
        }

        ksort($models);

        return $models;
    }

    private function hasTable(Model $model): bool
    {
        return isset($this->columns[$model->getTable()]);
    }

    private function hasColumn(Model $model, string $column): bool
    {
        return in_array($column, $this->columns[$model->getTable()] ?? [], true);
    }

    /**
     * @param  list<string>  $problems
     */
    private function assertNoProblems(array $problems, string $headline): void
    {
        $this->assertSame(
            [],
            $problems,
            $headline . "\n  - " . implode("\n  - ", $problems) . "\n"
        );
    }

    #[Test]
    public function every_model_maps_to_an_existing_table(): void
    {
        $problems = [];

        foreach ($this->models() as $name => $class) {
            $table = (new $class)->getTable();

            if (! isset($this->columns[$table])) {
                $problems[] = "$name maps to table `$table`, which does not exist";
            }
        }

        $this->assertNoProblems($problems, 'Models pointing at missing tables:');
    }

    #[Test]
    public function every_relationship_executes_against_the_database(): void
    {
        $problems = [];
        $executed = 0;

        foreach ($this->models() as $name => $class) {
            foreach ($this->relationMethodsOf($class) as $relation) {
                try {
                    $instance = (new $class)->{$relation}();

                    if ($instance instanceof MorphTo) {
                        // has() is not supported on morphTo; eager loading proves the query.
                        $class::query()->with($relation)->limit(1)->get();
                        $executed++;

                        continue;
                    }

                    $class::query()->has($relation)->count();
                    $class::query()->with($relation)->limit(1)->get();
                    $executed++;
                } catch (Throwable $e) {
                    $problems[] = sprintf('%s::%s() — %s', $name, $relation, $e->getMessage());
                }
            }
        }

        $this->assertNoProblems($problems, 'Relationships that do not match the schema:');
        $this->assertGreaterThan(0, $executed);
    }

    #[Test]
    public function fillable_contains_only_real_columns(): void
    {
        $problems = [];

        foreach ($this->models() as $name => $class) {
            $model = new $class;

            if (! $this->hasTable($model)) {
                continue;
            }

            foreach ($model->getFillable() as $field) {
                if (! $this->hasColumn($model, $field)) {
                    $problems[] = "$name lists `$field` in \$fillable but `{$model->getTable()}` has no such column";
                }
            }
        }

        $this->assertNoProblems($problems, 'Phantom $fillable fields:');
    }

    #[Test]
    public function casts_reference_only_real_columns(): void
    {
        $problems = [];

        foreach ($this->models() as $name => $class) {
            $model = new $class;

            if (! $this->hasTable($model)) {
                continue;
            }

            foreach (array_keys($model->getCasts()) as $attribute) {
                if ($attribute === $model->getKeyName()) {
                    continue;
                }

                if (! $this->hasColumn($model, $attribute)) {
                    $problems[] = "$name casts `$attribute`, which is not a column on `{$model->getTable()}`";
                }
            }
        }

        $this->assertNoProblems($problems, 'Casts on non-existent columns:');
    }

    #[Test]
    public function json_decimal_and_date_columns_are_cast(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Column type names are compared against MySQL/MariaDB types.');
        }

        // tinyint columns that hold a count or an enumerated value, not a flag.
        $notBooleans = [
            'reviews.rating',
            'business_hours.day_of_week',
            'otp_codes.attempts',
            'currencies.decimal_places',
        ];

        $ignored = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];

        $types = [];
        $database = DB::connection()->getDatabaseName();

        foreach (DB::select(
            'SELECT TABLE_NAME AS t, COLUMN_NAME AS c, DATA_TYPE AS d
             FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?',
            [$database]
        ) as $row) {
            $types[$row->t][$row->c] = $row->d;
        }

        $problems = [];

        foreach ($this->models() as $name => $class) {
            $model = new $class;
            $table = $model->getTable();

            if (! isset($types[$table])) {
                continue;
            }

            $casts = $model->getCasts();

            foreach ($types[$table] as $column => $type) {
                if (in_array($column, $ignored, true) || isset($casts[$column])) {
                    continue;
                }

                if (in_array("$table.$column", $notBooleans, true)) {
                    continue;
                }

                if (in_array($type, ['json', 'decimal', 'timestamp', 'date'], true)) {
                    $problems[] = "$name leaves `$column` ($type) uncast; it reads back as a raw string";
                }
            }
        }

        $this->assertNoProblems($problems, 'Structured columns without a cast:');
    }

    #[Test]
    public function soft_deletes_matches_the_presence_of_deleted_at(): void
    {
        $problems = [];

        foreach ($this->models() as $name => $class) {
            $model = new $class;

            if (! $this->hasTable($model)) {
                continue;
            }

            $hasColumn = $this->hasColumn($model, 'deleted_at');
            $usesTrait = in_array(SoftDeletes::class, class_uses_recursive($class), true);

            if ($hasColumn && ! $usesTrait) {
                $problems[] = "`{$model->getTable()}` has deleted_at but $name does not use SoftDeletes, so delete() would be permanent";
            }

            if (! $hasColumn && $usesTrait) {
                $problems[] = "$name uses SoftDeletes but `{$model->getTable()}` has no deleted_at column";
            }
        }

        $this->assertNoProblems($problems, 'SoftDeletes mismatches:');
    }

    #[Test]
    public function timestamps_are_enabled_only_when_both_columns_exist(): void
    {
        $problems = [];

        foreach ($this->models() as $name => $class) {
            $model = new $class;

            if (! $this->hasTable($model) || ! $model->usesTimestamps()) {
                continue;
            }

            foreach ([$model->getCreatedAtColumn(), $model->getUpdatedAtColumn()] as $column) {
                if ($column !== null && ! $this->hasColumn($model, $column)) {
                    $problems[] = "$name has \$timestamps enabled but `{$model->getTable()}` has no `$column`; inserts would fail";
                }
            }
        }

        $this->assertNoProblems($problems, 'Timestamp configuration mismatches:');
    }

    /**
     * Public, zero-argument methods whose return type is an Eloquent relation.
     *
     * @return list<string>
     */
    private function relationMethodsOf(string $class): array
    {
        $names = [];

        foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $class || $method->getNumberOfParameters() > 0) {
                continue;
            }

            $returnType = (string) $method->getReturnType();

            if (preg_match('/(HasMany|HasOne|BelongsTo|BelongsToMany|HasOneThrough|HasManyThrough|MorphTo|MorphMany|MorphOne)$/', $returnType)) {
                $names[] = $method->getName();
            }
        }

        return $names;
    }
}
