<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentAction;
use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentActionInterface;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class GalleryContentActionProvider
{
    /**
     * @param iterable<GalleryContentActionInterface> $actions
     */
    public function __construct(
        #[AutowireIterator(GalleryContentActionInterface::class)]
        private iterable $actions,
    ) {
    }

    /**
     * @return list<GalleryContentAction>
     */
    public function getActions(GalleryFolder $folder): array
    {
        $result = [];

        foreach ($this->actions as $actionProvider) {
            $action = $actionProvider->createAction($folder);

            if (null !== $action) {
                $result[] = $action;
            }
        }

        return $result;
    }
}
