<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

use Cgoit\ContaoFolderGalleryBundle\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_module']['palettes'][FolderGalleryModule::TYPE]
    = '{title_legend},name,headline,type;'
    .'{config_legend},galleryRoot,galleryCoverImageSize,galleryImageSize,showEmptyGalleryMessage;'
    .'{template_legend:collapsed},customTpl,galleryFolderTpl,galleryContentTpl;'
    .'{viewer_legend:collapsed},galleryViewer;'
    .'{protected_legend:collapsed},protected;'
    .'{expert_legend:collapsed},guests,cssID;'
    .'{invisible_legend:collapsed},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'showEmptyGalleryMessage';

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['showEmptyGalleryMessage'] = 'emptyGalleryMessage';

$GLOBALS['TL_DCA']['tl_module']['fields']['galleryRoot'] = [
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => ['fieldType' => 'radio', 'filesOnly' => false, 'mandatory' => true],
    'sql' => 'binary(16) NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['galleryCoverImageSize'] =
[
    'inputType' => 'imageSize',
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => '', 'platformOptions' => ['collation' => 'ascii_bin']],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['galleryImageSize'] =
[
    'inputType' => 'imageSize',
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => '', 'platformOptions' => ['collation' => 'ascii_bin']],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['showEmptyGalleryMessage'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
    'sql' => ['type' => 'boolean', 'default' => false],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['emptyGalleryMessage'] = [
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr'],
    'explanation' => 'insertTags',
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['galleryFolderTpl'] = [
    'inputType' => 'select',
    'eval' => ['chosen' => true, 'tl_class' => 'clr w50'],
    'sql' => "varchar(64) COLLATE ascii_bin NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['galleryContentTpl'] = [
    'inputType' => 'select',
    'eval' => ['chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) COLLATE ascii_bin NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['galleryViewer'] = [
    'inputType' => 'select',
    'enum' => GalleryViewer::class,
    'eval' => ['chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) COLLATE ascii_bin NOT NULL default 'lightbox'",
];
