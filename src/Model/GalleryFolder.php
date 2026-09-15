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
        public string $filesystemDirectory,
        /**
         * @var list<string>
         */
        public array $trail,
        public GalleryMetadata $metadata,
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

    public function imageCount(): int
    {
        return \count($this->images);
    }

    /**
     * Returns the images shown inside the gallery itself, i.e. without the cover image
     * if it is configured to be used only as preview image in the overview.
     *
     * @return list<GalleryImage>
     */
    public function getGalleryImages(): array
    {
        if (!$this->metadata->hideCoverInGallery) {
            return $this->images;
        }

        return array_values(
            array_filter($this->images, static fn (GalleryImage $image): bool => !$image->isCover),
        );
    }

    public function getDescription(): string|null
    {
        return $this->metadata->description;
    }

    public function getOverviewMode(): OverviewMode
    {
        return $this->metadata->overviewMode;
    }

    public function isPublished(): bool
    {
        return $this->metadata->isPublished();
    }

    public function isVisibleInBreadcrumb(): bool
    {
        return !$this->isTransparentInOverview();
    }

    public function isTransparentInOverview(): bool
    {
        return OverviewMode::Transparent === $this->metadata->overviewMode;
    }

    public function isGroupInOverview(): bool
    {
        return OverviewMode::Group === $this->metadata->overviewMode;
    }

    public function isGalleryInOverview(): bool
    {
        return OverviewMode::Gallery === $this->metadata->overviewMode;
    }
}
