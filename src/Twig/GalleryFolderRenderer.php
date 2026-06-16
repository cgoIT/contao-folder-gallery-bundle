<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Twig;

use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Twig\Environment;

final readonly class GalleryFolderRenderer
{
    public function __construct(private Environment $twig)
    {
    }

    public function render(GalleryFolderViewModel $folder, string $template, int $level = 1): string
    {
        return $this->twig->render("@Contao/$template.html.twig", [
            'folder' => $folder,
            'wrapperAttributes' => ['class' => 'level-'.$level],
            'children' => array_map(
                fn (GalleryFolderViewModel $child) => $this->render($child, $template, ++$level),
                $folder->children,
            ),
        ]);
    }
}
