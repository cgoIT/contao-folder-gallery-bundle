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

enum OverviewMode: string implements TranslatableLabelInterface
{
    case Gallery = 'gallery';
    case Group = 'group';
    case Transparent = 'transparent';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage(
            'tl_gallery_metadata.overview_mode.'.$this->getTranslationKey(),
            [],
            'contao_tl_gallery_metadata',
        );
    }

    private function getTranslationKey(): string
    {
        return match ($this) {
            self::Gallery => 'gallery',
            self::Group => 'group',
            self::Transparent => 'transparent',
        };
    }
}
