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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsAlias(GalleryFilesystemFingerprintProviderInterface::class)]
final readonly class GalleryFilesystemFingerprintProvider implements GalleryFilesystemFingerprintProviderInterface
{
    private const array EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'avif',
    ];

    public function __construct(
        private GalleryRootProviderInterface $rootProvider,
        private TagAwareCacheInterface $cache,
    ) {
    }

    public function getFilesystemFingerprint(): string
    {
        return $this->cache->get(
            GalleryCache::KEY_FILESYSTEM_FINGERPRINT,
            function (ItemInterface $item): string {
                $item->expiresAfter(10);
                $item->tag(GalleryCache::TAG_FILESYSTEM);

                return $this->calculateFilesystemFingerprint();
            },
        );
    }

    private function calculateFilesystemFingerprint(): string
    {
        $context = hash_init('sha256');

        foreach ($this->rootProvider->getGalleryRoots() as $root) {
            $this->getFingerprint($context, $root->filesystemDirectory);
        }

        return hash_final($context);
    }

    private function getFingerprint(\HashContext $context, string $galleryRootDirectory): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $galleryRootDirectory,
                \FilesystemIterator::SKIP_DOTS,
            ),
        );

        $entries = iterator_to_array($iterator);

        usort(
            $entries,
            static fn (\SplFileInfo $a, \SplFileInfo $b) => strcmp($a->getPathname(), $b->getPathname()),
        );

        foreach ($entries as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if (!$this->isRelevantFile($file)) {
                continue;
            }
            hash_update(
                $context,
                \sprintf(
                    '%s|%s|%d',
                    $galleryRootDirectory,
                    Path::makeRelative($file->getPathname(), $galleryRootDirectory),
                    $file->getMTime(),
                ),
            );
        }
    }

    private function isRelevantFile(\SplFileInfo $file): bool
    {
        if (GalleryMetadata::METADATA_FILE_NAME === $file->getFilename()) {
            return true;
        }

        return \in_array(
            strtolower($file->getExtension()),
            self::EXTENSIONS,
            true,
        );
    }
}
