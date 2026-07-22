<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Model;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum GalleryViewer: string implements TranslatableLabelInterface
{
    case None = 'none';
    case Lightbox = 'lightbox';
    case Photoswipe = 'photoswipe';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage(
            'tl_module.gallery_viewer.'.$this->getTranslationKey(),
            [],
            'contao_tl_module',
        );
    }

    private function getTranslationKey(): string
    {
        return match ($this) {
            self::None => 'none',
            self::Lightbox => 'lightbox',
            self::Photoswipe => 'photoswipe',
        };
    }
}
