<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\ViewModel;

use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentAction;
use Contao\CoreBundle\Image\Studio\Figure;

final readonly class GalleryContentViewModel
{
    /**
     * @param list<Figure>                           $images
     * @param list<GalleryContentAction>             $actions
     * @param list<GalleryBreadcrumbViewModel>       $breadcrumbs
     * @param (\Closure():array<string, mixed>)|null $schemaOrgData           Built lazily, like
     *                                                                        Figure::$metadata, so
     *                                                                        it is only resolved
     *                                                                        if the template
     *                                                                        actually renders it
     * @param (\Closure():array<string, mixed>)|null $breadcrumbSchemaOrgData Same laziness as
     *                                                                        $schemaOrgData - also
     *                                                                        avoids resolving the
     *                                                                        page ancestor trail
     *                                                                        (an extra query)
     *                                                                        unless requested
     */
    public function __construct(
        public GalleryFolderViewModel $folder,
        public array $images,
        public array $actions,
        public bool $showEmptyMessage,
        public string|null $emptyMessage,
        public array $breadcrumbs,
        public string|null $backUrl,
        private \Closure|null $schemaOrgData = null,
        private \Closure|null $breadcrumbSchemaOrgData = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getSchemaOrgData(): array
    {
        return null === $this->schemaOrgData ? [] : ($this->schemaOrgData)();
    }

    /**
     * @return array<string, mixed>
     */
    public function getBreadcrumbSchemaOrgData(): array
    {
        return null === $this->breadcrumbSchemaOrgData ? [] : ($this->breadcrumbSchemaOrgData)();
    }
}
