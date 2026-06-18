<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheKeyGenerator;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(FilesystemGalleryRepository::class)]
#[AsAlias(GalleryRepositoryInterface::class)]
final readonly class CachedGalleryRepository implements GalleryRepositoryInterface
{
    public function __construct(
        #[AutowireDecorated]
        private GalleryRepositoryInterface $inner,
        private CacheItemPoolInterface $cache,
        private GalleryCacheKeyGenerator $cacheKeyGenerator,
    ) {
    }

    public function findOverview(string $rootPath): GalleryOverview
    {
        $cacheKey = $this->cacheKeyGenerator->overview($rootPath);
        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $overview = $this->inner->findOverview($rootPath);

        $item->set($overview);
        $this->cache->save($item);

        return $overview;
    }

    public function findFolderByPath(string $rootPath, string $path): GalleryFolder|null
    {
        return null;
    }
}
