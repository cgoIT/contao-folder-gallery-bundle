<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\ViewModel;

final readonly class GalleryBreadcrumbViewModel
{
    public function __construct(
        public string $title,
        public string|null $url,
    ) {
    }

    public function isCurrent(): bool
    {
        return null === $this->url;
    }
}
