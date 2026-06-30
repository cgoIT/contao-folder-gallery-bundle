<?php

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

use Cgoit\ContaoFolderGalleryBundle\Drivers\DC_GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Contao\Backend;

$GLOBALS['TL_DCA']['tl_gallery_metadata'] = [
    'config' => [
        'dataContainer' => DC_GalleryMetadata::class,
    ],

    'palettes' => [
        'default' => '{general},title,description,cover,publishedFrom,publishedUntil,sortOrder,overviewMode',
    ],

    'fields' => [
        'title' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50'],
        ],

        'description' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE'],
        ],

        'cover' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr'],
        ],

        'publishedFrom' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ],

        'publishedUntil' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        ],

        'sortOrder' => [
            'inputType' => 'select',
            'options_callback' => ['tl_gallery_metadata', 'getSortOrders'],
            'eval' => ['tl_class' => 'w50'],
        ],

        'overviewMode' => [
            'inputType' => 'select',
            'options_callback' => ['tl_gallery_metadata', 'getOverviewModes'],
            'eval' => ['tl_class' => 'w50'],
        ],
    ],
];

class tl_gallery_metadata extends Backend
{
    public function getSortOrders(): array
    {
        return array_map(static fn (SortOrder $sortOrder) => $sortOrder->value, SortOrder::cases());
    }

    public function getOverviewModes(): array
    {
        return array_map(static fn (OverviewMode $overviewMode) => $overviewMode->value, OverviewMode::cases());
    }
}
