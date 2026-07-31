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

/**
 * @codeCoverageIgnore
 */
final readonly class GalleryMetadataManager
{
    public const string DATIM_FORMAT = \DateTimeInterface::ATOM;

    public function __construct(
        private GalleryMetadataReader $reader,
        private GalleryMetadataWriter $writer,
    ) {
    }

    public function read(string $directory): GalleryMetadata
    {
        return $this->reader->read($directory);
    }

    public function write(string $directory, GalleryMetadata $metadata): void
    {
        $this->writer->write($directory, $metadata);
    }
}
