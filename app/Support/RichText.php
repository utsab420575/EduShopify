<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allowlist sanitizer for rich-text editor (Summernote) HTML that is shown
 * publicly — e.g. a listing's "Summary Description of Product".
 *
 * Keeps only basic formatting (paragraphs, headings, bold/italic/underline,
 * lists, links, tables, colour/alignment styles). Everything else is removed:
 * images, media, iframes, scripts, forms, event handlers (on*), javascript:
 * links and any CSS beyond a few safe properties. Disallowed wrapper tags are
 * unwrapped (their text kept); dangerous ones are dropped with their content.
 */
class RichText
{
    /** tag => attributes allowed on it */
    private const ALLOWED = [
        'p' => ['style'], 'br' => [], 'div' => ['style'], 'span' => ['style'],
        'b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 's' => [], 'strike' => [], 'del' => [],
        'sub' => [], 'sup' => [], 'small' => [], 'mark' => [],
        'h1' => ['style'], 'h2' => ['style'], 'h3' => ['style'], 'h4' => ['style'], 'h5' => ['style'], 'h6' => ['style'],
        'ul' => [], 'ol' => [], 'li' => ['style'],
        'blockquote' => [], 'pre' => [], 'code' => [], 'hr' => [],
        'a' => ['href', 'target', 'title'],
        'table' => ['style'], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => [],
        'th' => ['style', 'colspan', 'rowspan'], 'td' => ['style', 'colspan', 'rowspan'],
    ];

    /** Removed together with everything inside them. */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'img', 'picture', 'svg', 'video', 'audio',
        'source', 'canvas', 'form', 'input', 'button', 'select', 'textarea', 'link', 'meta', 'noscript',
    ];

    private const SAFE_CSS = ['color', 'background-color', 'text-align', 'font-weight', 'font-style', 'text-decoration'];

    /** Sanitized HTML ('' when there is no visible text left). */
    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="rt-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('rt-root');
        if (! $root) {
            return '';
        }

        self::cleanChildren($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        $out = trim($out);

        return self::toText($out) === '' ? '' : $out;
    }

    /** Plain text: tags removed, entities decoded, whitespace collapsed. */
    public static function toText(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $text = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);
        $text = preg_replace('/<(br|\/p|\/div|\/li|\/h[1-6]|\/tr)\b[^>]*>/i', ' ', (string) $text);
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** True when the value is editor HTML rather than legacy plain text. */
    public static function isHtml(?string $value): bool
    {
        return $value !== null && preg_match('/<\s*[a-z][a-z0-9]*[\s>\/]/i', $value) === 1;
    }

    private static function cleanChildren(DOMNode $node): void
    {
        // Copy first — the list changes as nodes are removed/unwrapped.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);

                continue;
            }
            if (! $child instanceof DOMElement) {
                continue; // text
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);

                continue;
            }

            self::cleanChildren($child);

            if (! array_key_exists($tag, self::ALLOWED)) {
                // Unknown wrapper: keep its (already cleaned) content.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, self::ALLOWED[$tag]);
        }
    }

    private static function cleanAttributes(DOMElement $el, array $allowed): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);

            if (! in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->name);

                continue;
            }

            if ($name === 'href') {
                $href = trim($attr->value);
                if (! preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
                    $el->removeAttribute('href');
                }
            } elseif ($name === 'style') {
                $style = self::cleanStyle($attr->value);
                $style === '' ? $el->removeAttribute('style') : $el->setAttribute('style', $style);
            } elseif ($name === 'target') {
                $el->setAttribute('target', '_blank');
            }
        }

        if ($el->tagName === 'a' && $el->hasAttribute('target')) {
            $el->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function cleanStyle(string $style): string
    {
        $kept = [];
        foreach (explode(';', $style) as $declaration) {
            [$prop, $value] = array_pad(array_map('trim', explode(':', $declaration, 2)), 2, '');
            $prop = strtolower($prop);
            if (in_array($prop, self::SAFE_CSS, true) && $value !== '' && ! preg_match('/url\s*\(|expression\s*\(|javascript:|[<>]/i', $value)) {
                $kept[] = "{$prop}: {$value}";
            }
        }

        return implode('; ', $kept);
    }
}
