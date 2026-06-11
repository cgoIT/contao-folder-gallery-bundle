<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Model;

final readonly class GalleryDay
{
    /**
     * @param list<GalleryImage> $images
     */
    public function __construct(
        public string $year,
        public string $slug,
        public string $title,
        public string|null $description,
        public string|null $coverImage,
        public \DateTimeImmutable|null $publishedFrom,
        public array $images,
    ) {
    }
}
