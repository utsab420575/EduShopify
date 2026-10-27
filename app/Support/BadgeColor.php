<?php

namespace App\Support;

/**
 * Turns one admin-picked badge colour (#rrggbb) into a readable pill style:
 * a light tint for the background, a softer tint for the border, and the
 * colour itself as text — darkened step by step until it reaches WCAG AA
 * contrast (4.5:1) on that background, so even a very light pick (e.g.
 * yellow) stays legible.
 *
 * The admin achievement form's live preview mirrors this in JS
 * (achievements/partials/_badge-fields.blade.php) — keep the two in sync.
 */
class BadgeColor
{
    /** Tailwind 600 shades, offered as one-click swatches beside the picker. */
    public const TAILWIND = [
        'Red' => '#dc2626', 'Orange' => '#ea580c', 'Amber' => '#d97706', 'Yellow' => '#ca8a04',
        'Lime' => '#65a30d', 'Green' => '#16a34a', 'Emerald' => '#059669', 'Teal' => '#0d9488',
        'Cyan' => '#0891b2', 'Sky' => '#0284c7', 'Blue' => '#2563eb', 'Indigo' => '#4f46e5',
        'Violet' => '#7c3aed', 'Purple' => '#9333ea', 'Fuchsia' => '#c026d3', 'Pink' => '#db2777',
        'Rose' => '#e11d48', 'Slate' => '#475569', 'Gray' => '#4b5563', 'Stone' => '#57534e',
    ];

    public const HEX_PATTERN = '/^#[0-9a-fA-F]{6}$/';

    /** @return array{bg: string, border: string, text: string} */
    public static function palette(string $hex): array
    {
        $rgb = self::toRgb($hex);
        $bg = self::mixWithWhite($rgb, 0.9);
        $text = $rgb;

        for ($i = 0; $i < 20 && self::contrast($text, $bg) < 4.5; $i++) {
            $text = array_map(fn ($c) => $c * 0.88, $text);
        }

        return [
            'bg' => self::toHex($bg),
            'border' => self::toHex(self::mixWithWhite($rgb, 0.65)),
            'text' => self::toHex($text),
        ];
    }

    public static function style(string $hex): string
    {
        $p = self::palette($hex);

        return "background:{$p['bg']};color:{$p['text']};border:1px solid {$p['border']};";
    }

    private static function toRgb(string $hex): array
    {
        return array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    }

    private static function toHex(array $rgb): string
    {
        return '#'.implode('', array_map(fn ($c) => str_pad(dechex((int) round($c)), 2, '0', STR_PAD_LEFT), $rgb));
    }

    private static function mixWithWhite(array $rgb, float $white): array
    {
        return array_map(fn ($c) => $c * (1 - $white) + 255 * $white, $rgb);
    }

    private static function luminance(array $rgb): float
    {
        [$r, $g, $b] = array_map(function ($c) {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private static function contrast(array $a, array $b): float
    {
        $la = self::luminance($a);
        $lb = self::luminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }
}
