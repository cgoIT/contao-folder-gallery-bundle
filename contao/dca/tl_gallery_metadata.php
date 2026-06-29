<?php

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

use Cgoit\ContaoFolderGalleryBundle\Drivers\DC_GalleryMetadata;

$GLOBALS['TL_DCA']['tl_gallery_metadata'] = [
    'config' => [
        'dataContainer' => DC_GalleryMetadata::class,
    ],

    'palettes' => [
        'default' => '{general},title,description',
    ],

    'fields' => [
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_metadata']['title'],
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50'],
        ],

        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_metadata']['description'],
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr', 'rte' => true],
        ],
    ],
];
