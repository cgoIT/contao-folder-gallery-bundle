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

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryEntryPoint;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\SitemapEntry;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Contao\PageModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class GallerySitemapProvider implements GallerySitemapProviderInterface
{
    public function __construct(
        private GalleryEntryPointProviderInterface $entryPointProvider,
        private GalleryProviderInterface $galleryProvider,
        private GalleryUrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return list<SitemapEntry>
     */
    public function getEntries(): array
    {
        $entries = [];

        foreach ($this->entryPointProvider->getEntryPoints() as $entryPoint) {
            foreach ($this->createEntriesFromEntryPoint($entryPoint) as $entry) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @return list<SitemapEntry>
     */
    private function createEntriesFromEntryPoint(GalleryEntryPoint $entryPoint): array
    {
        $overview = $this->galleryProvider
            ->findOverviewByRootPath($entryPoint->galleryRoot->filesystemDirectory)
        ;

        if (null === $overview) {
            return [];
        }

        $entries = [];

        foreach ($overview->folderIndex as $galleryFolder) {
            if (!$galleryFolder->isPublished()) {
                continue;
            }

            $entries[] = $this->createSitemapEntry(
                $entryPoint->page,
                $galleryFolder,
            );
        }

        return $entries;
    }

    private function createSitemapEntry(PageModel $page, GalleryFolder $galleryFolder): SitemapEntry
    {
        return new SitemapEntry($this->urlGenerator->generate($page, $galleryFolder, UrlGeneratorInterface::ABSOLUTE_URL));
    }
}
