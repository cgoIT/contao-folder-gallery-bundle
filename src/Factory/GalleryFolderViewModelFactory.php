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
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class GalleryFolderViewModelFactory
{
    public function __construct(
        private GalleryFigureFactoryInterface $figureFactory,
        private GalleryUrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function create(GalleryFolder $folder, PageModel $page, ModuleModel $model): GalleryFolderViewModel
    {
        $coverImage = $folder->getCoverImage();
        $coverImageAlt = $coverImage
            ? $this->translator->trans('folder_gallery.cover_image_alt', [$folder->title], 'contao_folder_gallery')
            : null;

        $url = $this->urlGenerator->generate($page, $folder);

        $children = $this->createChildren($folder->folders, $page, $model);
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
                ? $this->figureFactory->createCoverImage($coverImage, $model->galleryCoverImageSize, $url, $coverImageAlt)
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
     * @param list<GalleryFolder> $folders
     *
     * @return list<GalleryFolderViewModel>
     */
    private function createChildren(array $folders, PageModel $page, ModuleModel $model): array
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
                        $model,
                    ),
                ];

                continue;
            }

            $children[] = $this->create($folder, $page, $model);
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
