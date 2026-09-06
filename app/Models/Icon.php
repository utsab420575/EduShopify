<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

class Icon extends Model
{
    protected $fillable = [
        'library_id',
        'name',
        'icon_type',
        'icon_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(IconLibrary::class, 'library_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'icon_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Render the icon as HTML based on its icon_type.
     */
    public function render(string $classes = '', string $style = ''): HtmlString
    {
        $val = trim($this->icon_value);

        if ($this->icon_type === 'fontawesome') {
            return new HtmlString("<i class=\"{$val} {$classes}\" style=\"{$style}\"></i>");
        }

        if ($this->icon_type === 'svg') {
            if (str_starts_with($val, '<svg')) {
                // If it's a full SVG string, inject class
                $styledSvg = preg_replace('/<svg\b/', '<svg class="' . e($classes) . '" style="' . e($style) . '"', $val, 1);
                return new HtmlString($styledSvg);
            }

            // Otherwise treat as SVG path data
            return new HtmlString(
                "<svg class=\"inline-block fill-current {$classes}\" style=\"{$style}\" viewBox=\"0 0 24 24\"><path d=\"{$val}\"/></svg>"
            );
        }

        if ($this->icon_type === 'image_url') {
            return new HtmlString(
                "<img src=\"" . e($val) . "\" class=\"inline-block object-contain {$classes}\" style=\"{$style}\" alt=\"" . e($this->name) . "\">"
            );
        }

        return new HtmlString("<i class=\"{$val} {$classes}\" style=\"{$style}\"></i>");
    }
}
