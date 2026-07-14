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
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;
use Cgoit\ContaoFolderGalleryBundle\Routing\GalleryUrlGeneratorInterface;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryBreadcrumbViewModel;
use Contao\PageModel;

final readonly class GalleryBreadcrumbFactory
{
    public function __construct(private GalleryUrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @return array{
     *     breadcrumbs: list<GalleryBreadcrumbViewModel>,
     *     backUrl: string
     * }
     */
    public function create(GalleryOverview $overview, GalleryFolder $folder, PageModel $page): array
    {
        $breadcrumbs = [];

        $parentGallery = null;
        $currentGallery = null;

        $trail = [];

        foreach ($folder->trail as $slug) {
            $trail[] = $slug;

            $current = $overview->findFolderByTrail($trail);

            if (null === $current || !$current->isVisibleInBreadcrumb()) {
                continue;
            }

            $url = null;

            if ($current !== $folder) {
                $url = $current->isGalleryInOverview()
                    ? $this->urlGenerator->generate($page, $current)
                    : $this->urlGenerator->generate($page, $currentGallery)
                    .'#'
                    .$this->urlGenerator->generateAnchor($current);
            }

            if ($current->isGalleryInOverview()) {
                $parentGallery = $currentGallery;
                $currentGallery = $current;
            }

            $breadcrumbs[] = new GalleryBreadcrumbViewModel(
                title: $current->title,
                url: $url,
            );
        }

        return [
            'breadcrumbs' => $breadcrumbs,
            'backUrl' => $this->urlGenerator->generate($page, $parentGallery),
        ];
    }
}
