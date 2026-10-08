<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;

/**
 * Without a lightbox size, Contao links the large view to the original file. That
 * file is not reachable for protected folders and still contains all EXIF data.
 */
final readonly class LightboxSizeProvider
{
    public function __construct(
        private PageFinder $pageFinder,
        private ContaoFramework $framework,
    ) {
    }

    /**
     * Returns the lightbox size of the module or, as a fallback, the one of the
     * current page layout.
     *
     * @return array<mixed>|null
     */
    public function getLightboxSize(string|null $moduleLightboxSize): array|null
    {
        return self::normalize($moduleLightboxSize) ?? $this->getLayoutLightboxSize();
    }

    /**
     * Returns the size configuration of an "imageSize" field or null if it is empty.
     *
     * @return array<mixed>|null
     */
    public static function normalize(mixed $size): array|null
    {
        $size = StringUtil::deserialize($size, true);

        // A predefined image size is referenced by its ID or by "_name"
        $mode = (string) ($size[2] ?? '');
        $isPredefinedSize = is_numeric($mode) || str_starts_with($mode, '_');

        if (empty($size[0]) && empty($size[1]) && !$isPredefinedSize) {
            return null;
        }

        return $size;
    }

    /**
     * @return array<mixed>|null
     */
    private function getLayoutLightboxSize(): array|null
    {
        $page = $this->pageFinder->getCurrentPage();

        if (!$page instanceof PageModel || null === $page->layout) {
            return null;
        }

        $layoutModel = $this->framework->getAdapter(LayoutModel::class)->findById($page->layout);

        if (!$layoutModel instanceof LayoutModel) {
            return null;
        }

        return self::normalize($layoutModel->lightboxSize);
    }
}
