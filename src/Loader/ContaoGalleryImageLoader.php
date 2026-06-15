<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Loader;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
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

    /**
     * @return list<GalleryImage>
     */
    public function loadImages(string $directory, string|null $coverImageName): array
    {
        $images = [];

        $arrFiles = FilesModel::findMultipleFilesByFolder($directory);

        if (!empty($arrFiles)) {
            foreach ($arrFiles as $file) {
                if ('.' === $file->name || '..' === $file->name || '_metadata.yml' === $file->name) {
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

        usort(
            $images,
            static fn (GalleryImage $a, GalleryImage $b): int => strcmp($a->filename, $b->filename),
        );

        return $images;
    }
}
