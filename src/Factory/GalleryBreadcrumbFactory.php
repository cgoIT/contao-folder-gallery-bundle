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
        $breadcrumbFolders = [];

        $trail = [];

        foreach ($folder->trail as $slug) {
            $trail[] = $slug;

            $current = $overview->findFolderByPath(implode('/', $trail));

            if (null === $current || !$current->isGalleryInOverview()) {
                continue;
            }

            $breadcrumbFolders[] = $current;
        }

        $last = array_key_last($breadcrumbFolders);

        $breadcrumbs = array_map(
            fn (GalleryFolder $folder, int $index) => new GalleryBreadcrumbViewModel(
                title: $folder->title,
                url: $index === $last
                    ? null
                    : $this->urlGenerator->generate($page, $folder),
            ),
            $breadcrumbFolders,
            array_keys($breadcrumbFolders),
        );

        if ([] === $breadcrumbFolders) {
            return [
                'breadcrumbs' => [],
                'backUrl' => $this->urlGenerator->generate($page),
            ];
        }

        $backUrl = $last > 0
            ? $this->urlGenerator->generate($page, $breadcrumbFolders[$last - 1])
            : $this->urlGenerator->generate($page);

        return [
            'breadcrumbs' => $breadcrumbs,
            'backUrl' => $backUrl,
        ];
    }
}
