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

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCache;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsAlias(GalleryProviderInterface::class)]
final readonly class GalleryProvider implements GalleryProviderInterface
{
    public function __construct(
        private GalleryRootProviderInterface $rootProvider,
        private GalleryRepositoryInterface $repository,
        private TagAwareCacheInterface $cache,
        private GalleryFilesystemFingerprintProviderInterface $filesystemVersionProvider,
    ) {
    }

    /**
     * @return list<GalleryOverview>
     */
    public function findAllOverviews(): array
    {
        return $this->cache->get(
            $this->getCacheKey(
                $this->filesystemVersionProvider->getFilesystemFingerprint(),
            ),
            fn ($item) => $this->findUncachedEntry($item),
        );
    }

    public function findOverviewByRootPath(string $path): GalleryOverview|null
    {
        foreach ($this->findAllOverviews() as $overview) {
            if ($overview->root->filesystemDirectory === $path) {
                return $overview;
            }
        }

        return null;
    }

    public function findFolderByPath(string $path): GalleryFolder|null
    {
        foreach ($this->findAllOverviews() as $overview) {
            if (isset($overview->folderIndex[$path])) {
                return $overview->folderIndex[$path];
            }
        }

        return null;
    }

    public function findFolderByModuleIdAndPath(int $moduleId, string $path): GalleryFolder|null
    {
        $overview = $this->findOverviewByModuleId($moduleId);

        if (null === $overview) {
            return null;
        }

        return $overview->folderIndex[$path] ?? null;
    }

    private function findOverviewByModuleId(int $moduleId): GalleryOverview|null
    {
        foreach ($this->findAllOverviews() as $overview) {
            if ($overview->getModuleId() === $moduleId) {
                return $overview;
            }
        }

        return null;
    }

    /**
     * @return list<GalleryOverview>
     */
    private function findUncachedEntry(ItemInterface $item): array
    {
        $overviews = [];

        foreach ($this->rootProvider->getGalleryRoots() as $root) {
            $overviews[] = $this->repository->findOverview($root);
        }

        $item->tag(GalleryCache::TAG_OVERVIEWS);

        return $overviews;
    }

    private function getCacheKey(string $version): string
    {
        return \sprintf(
            '%s.%s',
            GalleryCache::KEY_OVERVIEWS,
            $version,
        );
    }
}
