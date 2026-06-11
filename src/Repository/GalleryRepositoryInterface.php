<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryDay;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;

interface GalleryRepositoryInterface
{
    public function findOverview(): GalleryOverview;

    public function findDay(string $year, string $day): GalleryDay|null;
}
