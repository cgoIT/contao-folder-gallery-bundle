<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCache;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsAlias(CachedGalleryFolderProviderInterface::class)]
#[AsDecorator(GalleryFolderProvider::class)]
final readonly class CachedGalleryFolderProvider implements CachedGalleryFolderProviderInterface
{
    public function __construct(
        #[AutowireDecorated]
        private GalleryFolderProviderInterface $inner,
        private TagAwareCacheInterface $cache,
        private GalleryFilesystemFingerprintProviderInterface $filesystemVersionProvider,
    ) {
    }

    /**
     * @return list<GalleryOverview>
     */
    public function findAllOverviews(bool $blnShowUnpublished = false): array
    {
        return $this->cache->get(
            $this->getCacheKey(
                $blnShowUnpublished,
                $this->filesystemVersionProvider->getFilesystemFingerprint(),
            ),
            fn ($item) => $this->findUncachedEntry($item, $blnShowUnpublished),
        );
    }

    public function findOverviewByRootPath(string $path, bool $blnShowUnpublished = false): GalleryOverview|null
    {
        foreach ($this->findAllOverviews($blnShowUnpublished) as $overview) {
            if ($overview->root->filesystemDirectory === $path) {
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

    /**
     * @return list<GalleryOverview>
     */
    private function findUncachedEntry(ItemInterface $item, bool $blnShowUnpublished): array
    {
        $overviews = $this->inner->findAllOverviews($blnShowUnpublished);

        $item->tag(GalleryCache::TAG_OVERVIEWS);

        return $overviews;
    }

    private function getCacheKey(bool $showUnpublished, string $version): string
    {
        return \sprintf(
            '%s.%s',
            $showUnpublished
                ? GalleryCache::KEY_ALL_OVERVIEWS
                : GalleryCache::KEY_PUBLISHED_OVERVIEWS,
            $version,
        );
    }
}
