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
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final readonly class GalleryMetadataWriter
{
    private string $datimFormat;

    public function __construct(
        private ContaoFramework $framework,
        private Filesystem $filesystem,
    ) {
        $config = $this->framework->getAdapter(Config::class);
        $this->datimFormat = $config->get('datimFormat') ?? GalleryMetadataManager::DATIM_FORMAT;
    }

    public function write(string $directory, GalleryMetadata $metadata): void
    {
        $filename = rtrim($directory, '/').'/'.GalleryMetadata::METADATA_FILE_NAME;

        $data = [
            'title' => $metadata->title,
            'description' => $metadata->description,
            'cover' => $metadata->cover,
            'published_from' => $metadata->publishedFrom?->format($this->datimFormat),
            'published_until' => $metadata->publishedUntil?->format($this->datimFormat),
            'sort_order' => $metadata->sortOrder->value,
            'overview_mode' => $metadata->overviewMode->value,
        ];

        $data = array_filter(
            $data,
            static fn (mixed $value): bool => null !== $value,
        );

        $yaml = Yaml::dump($data);
        $this->filesystem->dumpFile($filename, $yaml);
    }
}
