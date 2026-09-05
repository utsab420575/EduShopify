<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: supplier_videos
 */
class SupplierVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_account_id',
        'provider',
        'video_id',
        'video_url',
        'title',
        'caption',
        'sort_order',
        'is_active',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'supplier_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * The stored `provider` column defaults to 'youtube' at the DB level
     * regardless of what was actually pasted (addVideo() never set it), so
     * it can't be trusted on its own — sniff the URL first and only fall
     * back to the column when the URL itself is inconclusive.
     */
    public function resolvedProvider(): string
    {
        if ($this->video_url && str_contains($this->video_url, 'vimeo.com')) {
            return 'vimeo';
        }

        if ($this->video_url && (str_contains($this->video_url, 'youtube.com') || str_contains($this->video_url, 'youtu.be'))) {
            return 'youtube';
        }

        return $this->provider ?: 'other';
    }

    /**
     * Same story as resolvedProvider(): `video_id` is often empty on
     * existing rows because it was never parsed out of the pasted URL at
     * save time, so it's derived here on the fly as a fallback.
     */
    public function resolvedVideoId(): ?string
    {
        if ($this->video_id) {
            return $this->video_id;
        }

        return match ($this->resolvedProvider()) {
            'youtube' => self::extractYoutubeId($this->video_url),
            'vimeo' => self::extractVimeoId($this->video_url),
            default => null,
        };
    }

    public function embedUrl(): ?string
    {
        $id = $this->resolvedVideoId();

        return match ($this->resolvedProvider()) {
            'youtube' => $id ? "https://www.youtube.com/embed/{$id}" : null,
            'vimeo' => $id ? "https://player.vimeo.com/video/{$id}" : null,
            default => $this->video_url,
        };
    }

    /**
     * YouTube exposes a public, no-auth thumbnail image per video id.
     * Vimeo has no equivalent without an API call, so those (and "other")
     * fall back to no thumbnail — the UI shows a plain play-icon tile.
     */
    public function thumbnailUrl(): ?string
    {
        if ($this->resolvedProvider() === 'youtube' && ($id = $this->resolvedVideoId())) {
            return "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
        }

        return null;
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if ($url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function extractVimeoId(?string $url): ?string
    {
        if ($url && preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
