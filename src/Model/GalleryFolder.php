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

final readonly class GalleryFolder
{
    /**
     * @param list<GalleryFolder> $folders
     * @param list<GalleryImage>  $images
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string|null $description,
        public \DateTimeImmutable|null $publishedFrom,
        public \DateTimeImmutable|null $publishedUntil,
        public array $folders = [],
        public array $images = [],
    ) {
    }

    public function getCoverImage(): GalleryImage|null
    {
        foreach ($this->images as $image) {
            if ($image->isCover) {
                return $image;
            }
        }

        return $this->images[0] ?? null;
    }

    public function hasSubFolders(): bool
    {
        return !empty($this->folders);
    }

    public function hasImages(): bool
    {
        return !empty($this->images);
    }
}
