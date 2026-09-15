<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryContentActionProvider;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryContentViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\ModuleModel;
use Contao\PageModel;

final readonly class GalleryContentViewModelFactory
{
    public function __construct(
        private GalleryFigureFactoryInterface $figureFactory,
        private GalleryFolderViewModelFactory $folderViewModelFactory,
        private GalleryBreadcrumbFactory $breadcrumbFactory,
        private GalleryContentActionProvider $actionProvider,
        private GallerySchemaOrgFactoryInterface $schemaOrgFactory,
    ) {
    }

    public function create(GalleryOverview $overview, GalleryFolder $folder, PageModel $page, ModuleModel $model): GalleryContentViewModel
    {
        $navigation = $this->breadcrumbFactory->create($overview, $folder, $page);
        $images = $this->getImages($folder);

        $actions = $this->actionProvider->getActions($overview, $folder, $page);

        $galleryViewer = GalleryViewer::None;
        if (null !== $model->galleryViewer) {
            $galleryViewer = GalleryViewer::tryFrom($model->galleryViewer) ?: GalleryViewer::Lightbox;
        }

        $folderViewModel = $this->folderViewModelFactory->create($folder, $page, $model);

        $figures = array_map(
            fn (GalleryImage $image): Figure => $this->figureFactory->create(
                $image,
                $model->galleryImageSize,
                $galleryViewer,
                'lb-'.$page->id.'-'.$folder->slug,
            ),
            $images,
        );

        return new GalleryContentViewModel(
            folder: $folderViewModel,
            images: $figures,
            actions: $actions,
            showEmptyMessage: $model->showEmptyGalleryMessage && $this->isEmpty($images, $folder->folders),
            emptyMessage: $model->showEmptyGalleryMessage ? $model->emptyGalleryMessage : null,
            breadcrumbs: $navigation['breadcrumbs'],
            backUrl: $navigation['backUrl'],
            schemaOrgData: fn (): array => $this->schemaOrgFactory->create($folderViewModel, $figures),
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
