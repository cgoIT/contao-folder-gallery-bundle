<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Routing;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsAlias(GalleryUrlGeneratorInterface::class)]
final readonly class GalleryUrlGenerator implements GalleryUrlGeneratorInterface
{
    public function __construct(private ContentUrlGenerator $contentUrlGenerator)
    {
    }

    public function generate(PageModel $page, GalleryFolder $folder, int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->contentUrlGenerator->generate(
            $page,
            [
                'parameters' => '/'.$folder->getPath(),
            ],
            $referenceType,
        );
    }
}
