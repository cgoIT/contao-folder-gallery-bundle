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
    public function __construct(
        public string $slug,
        public string $title,
        /**
         * @var list<string>
         */
        public array $trail,
        public string|null $description,
        public \DateTimeImmutable|null $publishedFrom,
        public \DateTimeImmutable|null $publishedUntil,
        /**
         * @var list<GalleryFolder>
         */
        public array $folders = [],
        /**
         * @var list<GalleryImage>
         */
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

    public function getPath(): string
    {
        return implode('/', $this->trail);
    }

    public function getDepth(): int
    {
        return \count($this->trail);
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
