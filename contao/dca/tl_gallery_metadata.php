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
use Contao\DataContainer;
use Contao\FilesModel;

$GLOBALS['TL_DCA']['tl_gallery_metadata'] = [
    'config' => [
        'dataContainer' => DC_GalleryMetadata::class,
        'onbeforesubmit_callback' => [['tl_gallery_metadata', 'checkForValidCoverImage']],
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
            'attributes_callback' => [['tl_gallery_metadata', 'getPathForCoverImage']],
            'save_callback' => [['tl_gallery_metadata', 'onCoverSaved']],
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
            'enum' => SortOrder::class,
            'eval' => ['tl_class' => 'w50'],
        ],

        'overviewMode' => [
            'inputType' => 'select',
            'enum' => OverviewMode::class,
            'explanation' => 'folderGalleryOverviewMode',
            'eval' => ['helpwizard' => true, 'tl_class' => 'w50'],
        ],
    ],
];

class tl_gallery_metadata extends Backend
{
    public function getPathForCoverImage(array $attributes, DataContainer|null $dc = null): array
    {
        if (!($dc instanceof DC_GalleryMetadata)) {
            return $attributes;
        }

        $attributes['path'] = $dc->id;

        return $attributes;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function checkForValidCoverImage(array $values, DataContainer $dc): mixed
    {
        if (!($dc instanceof DC_GalleryMetadata)) {
            return $values;
        }

        $cover = $values['cover'] ?? null;

        if ($cover) {
            $cover = FilesModel::findByUuid($cover);

            if (!$cover) {
                throw new Exception('Das Coverbild existiert nicht!');
            }

            if (dirname($cover->path) !== $dc->id) {
                throw new Exception('Das Coverbild ist nicht Teil dieser Galerie!');
            }
        }

        return $values;
    }
}
