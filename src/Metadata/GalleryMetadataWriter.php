<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Metadata;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final readonly class GalleryMetadataWriter
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function write(string $directory, GalleryMetadata $metadata): void
    {
        $filename = rtrim($directory, '/').'/_metadata.yml';

        $data = [
            'title' => $metadata->title,
            'description' => $metadata->description,
            'cover' => $metadata->cover,
            'published_from' => $metadata->publishedFrom?->format('Y-m-d H:i:s'),
            'published_until' => $metadata->publishedUntil?->format('Y-m-d H:i:s'),
            'sort_order' => $metadata->sortOrder->value,
            'overview_mode' => $metadata->overviewMode->value,
        ];

        $data = array_filter(
            $data,
            static fn (mixed $value): bool => null !== $value,
        );

        $this->filesystem->dumpFile($filename, Yaml::dump($data, 2, 4));
    }
}
