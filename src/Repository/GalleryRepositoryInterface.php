<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;

interface GalleryRepositoryInterface
{
    public function findOverview(string $rootPath): GalleryOverview;

    public function findDay(string $rootPath, string $yearSlug, string $daySlug): GalleryFolder|null;
}
