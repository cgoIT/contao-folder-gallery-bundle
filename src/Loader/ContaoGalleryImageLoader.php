<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Loader;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Provider\FilesModelProviderInterface;
use Cgoit\ContaoFolderGalleryBundle\Provider\ImageFile;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Filesystem\Path;

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

    public function __construct(private FilesModelProviderInterface $provider)
    {
    }

    /**
     * @return list<GalleryImage>
     */
    public function loadImages(string $directory, string|null $coverImageName): array
    {
        $images = [];

        foreach (scandir($directory) ?: [] as $file) {
            if ('.' === $file || '..' === $file || '_metadata.yml' === $file) {
                continue;
            }

            $path = Path::join($directory, $file);

            if (is_dir($path)) {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (!\in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $model = $this->provider->findByPath($path);

            if (!$model instanceof ImageFile) {
                continue;
            }

            $images[] = new GalleryImage(
                uuid: StringUtil::binToUuid($model->uuid),
                path: $model->path,
                filename: $file,
                isCover: $file === $coverImageName,
            );
        }

        usort(
            $images,
            static fn (GalleryImage $a, GalleryImage $b): int => strcmp($a->filename, $b->filename),
        );

        return $images;
    }
}
