<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

final class RichText
{
    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h2><h3><a>');
        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        foreach ($document->getElementsByTagName('*') as $element) {
            if (! $element instanceof DOMElement || $element->tagName === 'body') {
                continue;
            }
            foreach (iterator_to_array($element->attributes) as $attribute) {
                if ($element->tagName !== 'a' || $attribute->name !== 'href') {
                    $element->removeAttribute($attribute->name);
                }
            }
            if ($element->tagName === 'a') {
                $href = trim($element->getAttribute('href'));
                if (! preg_match('#^(https?://|mailto:)#i', $href)) {
                    $element->removeAttribute('href');
                }
                $element->setAttribute('target', '_blank');
                $element->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body) {
            return e(strip_tags($html));
        }

        $clean = '';
        foreach ($body->childNodes as $node) {
            $clean .= $document->saveHTML($node);
        }

        return trim($clean);
    }

    public static function plain(?string $html): string
    {
        $text = preg_replace('#<\s*br\s*/?>#i', "\n", (string) $html);
        $text = preg_replace('#</\s*(p|li|h2|h3|blockquote)\s*>#i', "\n", $text);

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim($text);
    }
}
