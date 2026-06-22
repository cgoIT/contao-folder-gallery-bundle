<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Model;

final readonly class GalleryMetadata
{
    public function __construct(
        public string|null $title = null,
        public string|null $description = null,
        public string|null $cover = null,
        public \DateTimeImmutable|null $publishedFrom = null,
        public \DateTimeImmutable|null $publishedUntil = null,
        public SortOrder $sortOrder = SortOrder::Asc,
        public OverviewMode $overviewMode = OverviewMode::Gallery,
    ) {
    }

    public function isPublished(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        if (
            $this->publishedFrom instanceof \DateTimeImmutable
            && $now < $this->publishedFrom
        ) {
            return false;
        }

        if (
            $this->publishedUntil instanceof \DateTimeImmutable
            && $now > $this->publishedUntil
        ) {
            return false;
        }

        return true;
    }
}
