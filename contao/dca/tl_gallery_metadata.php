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
        '__selector__' => ['overviewMode'],
        'gallery' => '{general_legend},title,overviewMode,description;{cover_legend},cover,hideCoverInGallery;{sorting_legend:collapsed},sortOrder;{publish_legend:collapsed},publishedFrom,publishedUntil',
        'group' => '{general_legend},title,overviewMode;{sorting_legend},sortOrder;{publish_legend:collapsed},publishedFrom,publishedUntil',
        'transparent' => '{general_legend},title,overviewMode',
    ],

    'fields' => [
        'title' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50'],
        ],

        'overviewMode' => [
            'inputType' => 'select',
            'enum' => OverviewMode::class,
            'explanation' => 'folderGalleryOverviewMode',
            'eval' => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
        ],

        'description' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE'],
        ],

        'cover' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'clr w100'],
            'attributes_callback' => [['tl_gallery_metadata', 'getPathForCoverImage']],
            'save_callback' => [['tl_gallery_metadata', 'onCoverSaved']],
        ],

        'hideCoverInGallery' => [
            'inputType' => 'checkbox',
            'explanation' => 'folderGalleryHideCoverInGallery',
            'eval' => ['helpwizard' => true, 'tl_class' => 'clr w50 m12'],
        ],

        'sortOrder' => [
            'inputType' => 'select',
            'enum' => SortOrder::class,
            'eval' => ['tl_class' => 'w50'],
        ],

        'publishedFrom' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'clr w50 wizard'],
        ],

        'publishedUntil' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
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

            if (dirname($cover->path) !== $dc->id) {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['tl_gallery_metadata']['error']['imageOutsideGalleryFolder'], dirname($cover->path), $dc->id));
            }
        }

        return $values;
    }
}
