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
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;

final readonly class GalleryBreadcrumbFactory
{
    public function __construct(
        private GalleryUrlGeneratorInterface $urlGenerator,
        private ContaoFramework $framework,
    ) {
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

        $breadcrumbs[] = new GalleryBreadcrumbViewModel(
            title: $page->title,
            url: $this->urlGenerator->generate($page),
        );

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

    /**
     * Resolves the real Contao page trail above the gallery page itself (root
     * first), for callers that need to combine it with the folder-level
     * breadcrumbs above - e.g. to build a complete BreadcrumbList JSON-LD,
     * since the page tree has no notion of the virtual gallery folders.
     *
     * @return list<GalleryBreadcrumbViewModel>
     */
    public function createPageAncestors(PageModel $page): array
    {
        $trail = $page->trail;
        $ancestorIds = \is_array($trail) ? \array_slice($trail, 0, -1) : [];

        if ([] === $ancestorIds) {
            return [];
        }

        $pages = $this->framework->getAdapter(PageModel::class)->findMultipleByIds($ancestorIds);

        if (null === $pages) {
            return [];
        }

        $ancestors = [];

        foreach ($pages as $ancestorPage) {
            if ($ancestorPage->hide) {
                continue;
            }

            $ancestors[] = new GalleryBreadcrumbViewModel(
                title: $ancestorPage->title,
                url: $this->urlGenerator->generate($ancestorPage),
            );
        }

        return $ancestors;
    }
}
