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
    public function create(GalleryFolder $folder, PageModel $page, PictureConfiguration|array|int|string|null $coverImageSize, bool $recursive = true): GalleryFolderViewModel
    {
        $coverImage = $folder->getCoverImage();

        return new GalleryFolderViewModel(
            title: $folder->title,
            slug: $folder->slug,
            url: $this->urlGenerator->generate($page, $folder),
            children: $recursive
                ? array_map(
                    fn (GalleryFolder $child) => $this->create(
                        $child,
                        $page,
                        $coverImageSize,
                        true,
                    ),
                    $folder->folders,
                )
                : [],
            coverFigure: $coverImage
                ? $this->figureFactory->create($coverImage, $coverImageSize)
                : null,
            description: $folder->getDescription(),
            overviewMode: $folder->getOverviewMode(),
        );
    }
}
