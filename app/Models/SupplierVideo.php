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
     * Checks if the URL points directly to a video stream/file (.mp4, .webm, .ogg, etc.).
     */
    public function isDirectVideo(): bool
    {
        if (in_array($this->resolvedProvider(), ['youtube', 'vimeo'])) {
            return false;
        }

        if (! $this->video_url) {
            return false;
        }

        return (bool) preg_match('/\.(mp4|webm|ogg|ogv|mov|m4v)(\?.*)?$/i', $this->video_url)
            || str_starts_with($this->video_url, 'data:video/');
    }

    /**
     * Display badge details for supplier and public cards.
     */
    public function providerBadge(): array
    {
        if ($this->resolvedProvider() === 'youtube') {
            return [
                'label' => 'YouTube',
                'icon' => 'fa-brands fa-youtube',
                'bg_class' => 'bg-red-50 text-red-600 border-red-200',
            ];
        }

        if ($this->resolvedProvider() === 'vimeo') {
            return [
                'label' => 'Vimeo',
                'icon' => 'fa-brands fa-vimeo-v',
                'bg_class' => 'bg-sky-50 text-sky-600 border-sky-200',
            ];
        }

        if ($this->isDirectVideo()) {
            return [
                'label' => 'Direct Video',
                'icon' => 'fa-solid fa-file-video',
                'bg_class' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
            ];
        }

        return [
            'label' => 'Video',
            'icon' => 'fa-solid fa-play',
            'bg_class' => 'bg-indigo-50 text-indigo-600 border-indigo-200',
        ];
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
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:.*[?&]v=|embed/|v/|shorts/))([A-Za-z0-9_-]{11})~i', $url, $m)) {
            return $m[1];
        }

        // Fallback for non-standard 6-12 character IDs
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function extractVimeoId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/|)(\d+)~i', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
