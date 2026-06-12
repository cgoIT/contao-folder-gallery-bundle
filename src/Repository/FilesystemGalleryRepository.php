<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Repository;

use Cgoit\ContaoFolderGalleryBundle\Loader\GalleryImageLoaderInterface;
use Cgoit\ContaoFolderGalleryBundle\Metadata\MetadataReader;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryDay;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryYear;
use Contao\CoreBundle\Slug\Slug;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Filesystem\Path;

#[AsAlias(GalleryRepositoryInterface::class)]
final readonly class FilesystemGalleryRepository implements GalleryRepositoryInterface
{
    public function __construct(
        private MetadataReader $metadataReader,
        private GalleryImageLoaderInterface $galleryImageLoader,
        private Slug $slug,
    ) {
    }

    public function findOverview(string $rootPath): GalleryOverview
    {
        $years = [];

        foreach ($this->getDirectories($rootPath) as $yearDirectory) {
            $year = $this->createYear($yearDirectory);

            if (null !== $year) {
                $years[] = $year;
            }
        }

        usort(
            $years,
            static fn (GalleryYear $a, GalleryYear $b): int => strcmp($b->title, $a->title),
        );

        return new GalleryOverview($years);
    }

    public function findDay(string $rootPath, string $yearSlug, string $daySlug): GalleryDay|null
    {
        $directory = Path::join(
            $rootPath,
            $yearSlug,
            $daySlug,
        );

        if (!is_dir($directory)) {
            return null;
        }

        return $this->createDay(
            $yearSlug,
            $directory,
        );
    }

    private function createYear(string $directory): GalleryYear|null
    {
        $metadata = $this->metadataReader->read($directory);

        if (!$metadata->isPublished()) {
            return null;
        }

        $days = [];

        foreach ($this->getDirectories($directory) as $dayDirectory) {
            $day = $this->createDay(
                basename($directory),
                $dayDirectory,
            );

            if (null !== $day) {
                $days[] = $day;
            }
        }

        $yearSlug = $this->slug->generate($metadata->title ?? basename($directory));

        return new GalleryYear(
            slug: $yearSlug,
            title: $metadata->title ?? basename($directory),
            publishedFrom: $metadata->publishedFrom,
            publishedUntil: $metadata->publishedUntil,
            days: $days,
        );
    }

    private function createDay(string $year, string $directory): GalleryDay|null
    {
        $metadata = $this->metadataReader->read($directory);

        if (!$metadata->isPublished()) {
            return null;
        }

        $daySlug = $this->slug->generate($metadata->title ?? basename($directory));

        return new GalleryDay(
            year: $year,
            slug: $daySlug,
            title: $metadata->title ?? basename($directory),
            description: $metadata->description,
            publishedFrom: $metadata->publishedFrom,
            publishedUntil: $metadata->publishedUntil,
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
}
