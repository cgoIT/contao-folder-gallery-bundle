<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Model;

final readonly class GalleryYear
{
    /**
     * @param list<GalleryDay> $days
     */
    public function __construct(
        public string $slug,
        public string $title,
        public \DateTimeImmutable|null $publishedFrom,
        public array $days,
    ) {
    }
}
