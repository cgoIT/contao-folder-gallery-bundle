<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryContentActionProvider;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryContentViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\Image\PictureConfiguration;
use Contao\PageModel;

final readonly class GalleryContentViewModelFactory
{
    public function __construct(
        private GalleryFigureFactoryInterface $figureFactory,
        private GalleryFolderViewModelFactory $folderViewModelFactory,
        private GalleryBreadcrumbFactory $breadcrumbFactory,
        private GalleryContentActionProvider $actionProvider,
    ) {
    }

    /**
     * @param array<mixed>|PictureConfiguration|int|string|null $imageSize
     * @param array<mixed>|PictureConfiguration|int|string|null $coverImageSize
     */
    public function create(GalleryOverview $overview, GalleryFolder $folder, PageModel $page, PictureConfiguration|array|int|string|null $imageSize, PictureConfiguration|array|int|string|null $coverImageSize, bool $showEmptyGalleryMessage, string|null $emptyGalleryMessage, GalleryViewer $galleryViewer = GalleryViewer::None): GalleryContentViewModel
    {
        $navigation = $this->breadcrumbFactory->create($overview, $folder, $page);
        $images = $this->getImages($folder);

        $actions = $this->actionProvider->getActions($overview, $folder, $page);

        return new GalleryContentViewModel(
            folder: $this->folderViewModelFactory->create($folder, $page, $coverImageSize),
            images: array_map(
                fn (GalleryImage $image): Figure => $this->figureFactory->create(
                    $image,
                    $imageSize,
                    $galleryViewer,
                    'lb-'.$page->id.'-'.$folder->slug,
                ),
                $images,
            ),
            actions: $actions,
            showEmptyMessage: $showEmptyGalleryMessage && $this->isEmpty($images, $folder->folders),
            emptyMessage: $showEmptyGalleryMessage ? $emptyGalleryMessage : null,
            breadcrumbs: $navigation['breadcrumbs'],
            backUrl: $navigation['backUrl'],
        );
    }

    /**
     * @return list<GalleryImage>
     */
    private function getImages(GalleryFolder $folder): array
    {
        if (!$folder->metadata->hideCoverInGallery) {
            return $folder->images;
        }

        return array_filter($folder->images, static fn (GalleryImage $image) => !$image->isCover);
    }

    /**
     * @param list<GalleryImage>  $images
     * @param list<GalleryFolder> $children
     */
    private function isEmpty(array $images, array $children): bool
    {
        $visibleImageCount = \count($images);

        $visibleChildFolders = \count(
            array_filter(
                $children,
                static fn (GalleryFolder $folder) => $folder->isPublished(),
            ),
        );

        return 0 === $visibleImageCount && 0 === $visibleChildFolders;
    }
}
