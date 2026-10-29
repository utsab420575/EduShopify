<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Table: settings — admin-editable platform configuration (System Settings,
 * /admin/system/settings). unique(group_name, name); payload always holds
 * {"value": ...}; label/type/description drive the admin form.
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'name',
        'label',
        'type',
        'locked',
        'payload',
        'description',
    ];

    /** How the admin form edits a value. */
    public const TYPES = [
        'text' => 'Text',
        'number' => 'Number',
        'boolean' => 'Yes / No',
        'list' => 'List (comma separated)',
    ];

    /** Groups with their own admin page — not listed in System Settings. */
    public const MANAGED_GROUPS = ['theme', 'homepage_hero'];

    protected static function booted(): void
    {
        // Any change (admin CRUD included) drops the cached reads.
        $flush = function (self $setting): void {
            Cache::forget("setting:{$setting->group_name}:{$setting->name}");
            Cache::forget("setting-group:{$setting->group_name}");
            if ($setting->isDirty(['group_name', 'name'])) {
                Cache::forget("setting:{$setting->getOriginal('group_name')}:{$setting->getOriginal('name')}");
                Cache::forget("setting-group:{$setting->getOriginal('group_name')}");
            }
        };
        static::saved($flush);
        static::deleted($flush);
    }

    public function value(): mixed
    {
        return $this->payload['value'] ?? null;
    }

    /** A form input string → the stored value for a type. */
    public static function castValue(string $type, mixed $raw): mixed
    {
        return match ($type) {
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'number' => str_contains((string) $raw, '.') ? (float) $raw : (int) $raw,
            'list' => array_values(array_filter(array_map('trim', explode(',', (string) $raw)), fn ($v) => $v !== '')),
            default => (string) $raw,
        };
    }

    /** The value as a form input string. */
    public function formValue(): string
    {
        $value = $this->value();

        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_array($value) => implode(', ', array_map(fn ($v) => is_scalar($v) ? (string) $v : json_encode($v), $value)),
            $value === null => '',
            default => (string) $value,
        };
    }

    /** The value for the admin list. */
    public function displayValue(): string
    {
        $value = $this->value();

        return match (true) {
            is_bool($value) => $value ? 'Yes' : 'No',
            $value === null || $value === '' || $value === [] => '—',
            default => $this->formValue(),
        };
    }

    public function displayLabel(): string
    {
        return $this->label ?: \Illuminate\Support\Str::headline($this->name);
    }

    protected function casts(): array
    {
        return [
            'locked'  => 'boolean',
            'payload' => 'array',
        ];
    }

    /**
     * Read a setting's value, cached briefly since these are read on hot paths
     * (capability application limits, RFQ approval toggles, etc.).
     */
    public static function get(string $group, string $name, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$group}:{$name}", 300, function () use ($group, $name, $default) {
            $row = static::where('group_name', $group)->where('name', $name)->first();

            return $row ? ($row->payload['value'] ?? $default) : $default;
        });
    }

    /**
     * A locked setting must not be overwritten through ordinary Admin UI
     * (admin_dashboard_workflow.md Part 16.1 / rule 28) — callers that hold an
     * explicit sensitive permission to change one must pass $force = true.
     */
    public static function set(string $group, string $name, mixed $value, bool $force = false): self
    {
        $existing = static::where('group_name', $group)->where('name', $name)->first();

        if ($existing && $existing->locked && ! $force) {
            throw new \RuntimeException("Setting [{$group}.{$name}] is locked and cannot be changed through ordinary settings update.");
        }

        $setting = static::updateOrCreate(
            ['group_name' => $group, 'name' => $name],
            ['payload' => ['value' => $value]]
        );

        Cache::forget("setting:{$group}:{$name}");
        Cache::forget("setting-group:{$group}");

        return $setting;
    }

    /**
     * All settings in a group as a flat name => value array, cached.
     */
    public static function group(string $group): array
    {
        return Cache::remember("setting-group:{$group}", 300, function () use ($group) {
            return static::where('group_name', $group)->get()
                ->mapWithKeys(fn (self $row) => [$row->name => $row->payload['value'] ?? null])
                ->all();
        });
    }
}
