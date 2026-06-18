<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Cache;

use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

#[AutoconfigureTag('kernel.cache_warmer')]
final readonly class GalleryCacheWarmer implements CacheWarmerInterface
{
    public function __construct(
        private GalleryRootProviderInterface $rootProvider,
        private GalleryRepositoryInterface $repository,
    ) {
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp(string $cacheDir, string|null $buildDir = null): array
    {
        $this->warmGalleryCaches();

        return [];
    }

    public function warmGalleryCaches(): void
    {
        foreach ($this->rootProvider->getGalleryRoots() as $rootPath) {
            $this->repository->findOverview($rootPath);
        }
    }
}
