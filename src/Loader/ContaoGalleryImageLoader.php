<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Loader;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(GalleryImageLoaderInterface::class)]
final readonly class ContaoGalleryImageLoader implements GalleryImageLoaderInterface
{
    private const array IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'avif',
    ];

    public function __construct(private ContaoFramework $framework)
    {
    }

    /**
     * @return list<GalleryImage>
     */
    public function loadImages(string $directory, string|null $coverImageName): array
    {
        $images = [];

        $arrFiles = $this->framework
            ->getAdapter(FilesModel::class)
            ->findMultipleFilesByFolder($directory)
        ;

        if (!empty($arrFiles)) {
            foreach ($arrFiles as $file) {
                if ('.' === $file->name || '..' === $file->name || GalleryMetadata::METADATA_FILE_NAME === $file->name) {
                    continue;
                }

                if (!\in_array($file->extension, self::IMAGE_EXTENSIONS, true)) {
                    continue;
                }

                $images[] = new GalleryImage(
                    uuid: StringUtil::binToUuid($file->uuid),
                    path: $file->path,
                    filename: $file->name,
                    isCover: $file->name === $coverImageName,
                );
            }
        }

        return $images;
    }
}
