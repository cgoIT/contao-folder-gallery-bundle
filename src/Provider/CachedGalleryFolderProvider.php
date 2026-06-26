<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCache;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
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
        private TagAwareAdapter $cache,
    ) {
    }

    /**
     * @return list<GalleryOverview>
     */
    public function findAllOverviews(bool $blnShowUnpublished = false): array
    {
        $item = $this->cache->getItem($this->getCacheKey($blnShowUnpublished));

        if ($item->isHit()) {
            return $item->get();
        }

        $overviews = $this->inner->findAllOverviews($blnShowUnpublished);

        $item->set($overviews);
        $item->tag(GalleryCache::TAG_OVERVIEWS);
        $this->cache->save($item);

        return $overviews;
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

    private function getCacheKey(bool $showUnpublished): string
    {
        return $showUnpublished
            ? GalleryCache::KEY_ALL_OVERVIEWS
            : GalleryCache::KEY_PUBLISHED_OVERVIEWS;
    }
}
