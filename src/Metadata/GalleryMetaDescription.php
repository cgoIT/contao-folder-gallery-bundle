<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Metadata;

/**
 * Turns the HTML description of a gallery into a plain text suitable for the meta description.
 */
final class GalleryMetaDescription
{
    public const int MAX_LENGTH = 160;

    private const string ELLIPSIS = '…';

    private function __construct()
    {
    }

    public static function fromHtml(string|null $html, int $maxLength = self::MAX_LENGTH): string
    {
        if (null === $html || '' === trim($html)) {
            return '';
        }

        // Keep the words of adjacent block elements apart before stripping the tags
        $text = (string) preg_replace('/<\/?(?:p|br|div|li|ul|ol|h[1-6]|tr|td|th|blockquote)\b[^>]*>/i', ' ', $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $excerpt = mb_substr($text, 0, $maxLength - mb_strlen(self::ELLIPSIS));
        $lastSpace = mb_strrpos($excerpt, ' ');

        // Cut at a word boundary unless that would throw away most of the text
        if (false !== $lastSpace && $lastSpace > $maxLength / 2) {
            $excerpt = mb_substr($excerpt, 0, $lastSpace);
        }

        return rtrim($excerpt, " \t.,;:-–").self::ELLIPSIS;
    }
}
