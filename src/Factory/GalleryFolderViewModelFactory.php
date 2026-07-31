<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\Image\PictureConfiguration;
use Contao\PageModel;

final readonly class GalleryFolderViewModelFactory
{
    public function __construct(
        private GalleryFigureFactoryInterface $figureFactory,
        private GalleryUrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $coverImageSize
     */
    public function create(GalleryFolder $folder, PageModel $page, PictureConfiguration|array|int|string|null $coverImageSize): GalleryFolderViewModel
    {
        $coverImage = $folder->getCoverImage();
        $url = $this->urlGenerator->generate($page, $folder);

        $children = $this->createChildren($folder->folders, $page, $coverImageSize);
        $subGalleryCount = $this->countDirectSubGalleries($folder);

        $imageCount = $folder->metadata->hideCoverInGallery
            ? max($folder->imageCount() - 1, 0)
            : $folder->imageCount();

        return new GalleryFolderViewModel(
            title: $folder->title,
            slug: $folder->slug,
            url: $url,
            children: $children,
            imageCount: $imageCount,
            galleryCount: $subGalleryCount,
            coverFigure: $coverImage
                ? $this->figureFactory->createCoverImage($coverImage, $coverImageSize, $url)
                : null,
            description: $folder->getDescription(),
            anchor: $folder->isGroupInOverview()
                ? $this->urlGenerator->generateAnchor($folder)
                : null,
            level: $folder->getDepth(),
            overviewMode: $folder->getOverviewMode(),
        );
    }

    /**
     * @param list<GalleryFolder>                               $folders
     * @param PictureConfiguration|array<mixed>|int|string|null $coverImageSize
     *
     * @return list<GalleryFolderViewModel>
     */
    private function createChildren(array $folders, PageModel $page, PictureConfiguration|array|int|string|null $coverImageSize): array
    {
        $children = [];

        foreach ($folders as $folder) {
            if (!$folder->isPublished()) {
                continue;
            }

            if ($folder->isTransparentInOverview()) {
                $children = [
                    ...$children,
                    ...$this->createChildren(
                        $folder->folders,
                        $page,
                        $coverImageSize,
                    ),
                ];

                continue;
            }

            $children[] = $this->create($folder, $page, $coverImageSize);
        }

        return $children;
    }

    private function countDirectSubGalleries(GalleryFolder $folder): int
    {
        $count = 0;

        foreach ($folder->folders as $child) {
            if (!$child->isPublished()) {
                continue;
            }

            if ($child->isTransparentInOverview()) {
                $count += $this->countDirectSubGalleries($child);
                continue;
            }

            if ($child->isGroupInOverview()) {
                $count += $this->countDirectSubGalleries($child);
                continue;
            }

            ++$count;
        }

        return $count;
    }
}
