<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\EventListener;

use Cgoit\ContaoFolderGalleryBundle\Provider\GallerySitemapProviderInterface;
use Contao\CoreBundle\Event\SitemapEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class GallerySitemapListener
{
    public function __construct(private GallerySitemapProviderInterface $gallerySitemapProvider)
    {
    }

    public function __invoke(SitemapEvent $e): void
    {
        foreach ($this->gallerySitemapProvider->getEntries() as $entry) {
            $e->addUrlToDefaultUrlSet($entry->url);
        }
    }
}
