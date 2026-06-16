<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Loader\GalleryImageLoaderInterface;
use Cgoit\ContaoFolderGalleryBundle\Loader\MetadataLoader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Contao\CoreBundle\Slug\Slug;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Filesystem\Path;

#[AsAlias(GalleryRepositoryInterface::class)]
final readonly class FilesystemGalleryRepository implements GalleryRepositoryInterface
{
    public function __construct(
        private MetadataLoader $metadataLoader,
        private GalleryImageLoaderInterface $galleryImageLoader,
        private Slug $slug,
    ) {
    }

    public function findOverview(string $rootPath): GalleryOverview
    {
        $folders = [];

        foreach ($this->getDirectories($rootPath) as $subFolder) {
            $folder = $this->createFolder($subFolder);

            if (null !== $folder) {
                $folders[] = $folder;
            }
        }

        $metadata = $this->metadataLoader->read($rootPath);
        $folders = $this->sortFoldersByTitle($folders, $metadata);

        return new GalleryOverview($folders);
    }

    public function findDay(string $rootPath, string $yearSlug, string $daySlug): GalleryFolder|null
    {
        return null;
    }

    private function createFolder(string $directory, bool $recursive = true): GalleryFolder|null
    {
        $metadata = $this->metadataLoader->read($directory);

        if (!$metadata->isPublished()) {
            return null;
        }

        $folders = [];

        if ($recursive) {
            foreach ($this->getDirectories($directory) as $subFolder) {
                $folder = $this->createFolder($subFolder);

                if (null !== $folder) {
                    $folders[] = $folder;
                }
            }
        }

        $folders = $this->sortFoldersByTitle($folders, $metadata);

        return new GalleryFolder(
            slug: $this->slug->generate($metadata->title ?? basename($directory)),
            title: $metadata->title ?? basename($directory),
            description: $metadata->description,
            publishedFrom: $metadata->publishedFrom,
            publishedUntil: $metadata->publishedUntil,
            folders: $folders,
            images: $this->galleryImageLoader->loadImages(
                $directory,
                $metadata->cover,
            ),
        );
    }

    /**
     * @return list<string>
     */
    private function getDirectories(string $directory): array
    {
        $directories = glob(
            Path::join($directory, '*'),
            GLOB_ONLYDIR,
        );

        if (false === $directories) {
            return [];
        }

        sort($directories);

        return $directories;
    }

    /**
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolder>
     */
    private function sortFoldersByTitle(array $folders, GalleryMetadata $metadata): array
    {
        usort(
            $folders,
            static fn (GalleryFolder $a, GalleryFolder $b): int => match ($metadata->sortOrder) {
                SortOrder::Asc => strcmp($a->title, $b->title),
                SortOrder::Desc => strcmp($b->title, $a->title),
            },
        );

        return $folders;
    }
}
