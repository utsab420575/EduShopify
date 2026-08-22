<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Table: settings — admin-editable platform configuration.
 * unique(group_name, name); payload always holds {"value": ...}.
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'name',
        'locked',
        'payload',
    ];

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
