<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheKeyGenerator;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsAlias(CachedGalleryFolderProviderInterface::class)]
#[AsDecorator(GalleryFolderProvider::class)]
final readonly class CachedGalleryFolderProvider implements GalleryFolderProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private GalleryFolderProviderInterface $inner,
        private CacheItemPoolInterface $cache,
        private GalleryCacheKeyGenerator $cacheKeyGenerator,
    ) {
    }

    /**
     * @return list<GalleryOverview>
     */
    public function findAllOverviews(bool $blnShowUnpublished = false): array
    {
        $cacheKey = $this->cacheKeyGenerator->allOverviews($blnShowUnpublished);

        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $folders = $this->inner->findAllOverviews($blnShowUnpublished);

        $item->set($folders);
        $this->cache->save($item);

        return $folders;
    }

    public function findOverviewByRootPath(string $path, bool $blnShowUnpublished = false): GalleryOverview|null
    {
        foreach ($this->findAllOverviews($blnShowUnpublished) as $overview) {
            if ($overview->filesystemDirectory === $path) {
                return $overview;
            }
        }

        return null;
    }

    public function findFolderByPath(string $path, bool $blnShowUnpublished = false): GalleryFolder|null
    {
        foreach ($this->findAllOverviews($blnShowUnpublished) as $overview) {
            if (isset($overview->folderIndex[$path])) {
                return $overview->folderIndex[$path];
            }
        }

        return null;
    }
}
