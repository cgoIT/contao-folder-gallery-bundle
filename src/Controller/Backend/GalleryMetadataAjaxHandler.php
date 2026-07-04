<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Controller\Backend;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\FileTree;
use Contao\Input;
use Contao\PageTree;
use Contao\Picker;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class GalleryMetadataAjaxHandler
{
    public function executePostActions(string $action, DataContainer $dataContainer): void
    {
        if ('reloadFiletree' !== $action) {
            return;
        }

        $intId = Input::get('id', true);
        $strField = $dataContainer->inputName = Input::post('name');

        if (!isset($GLOBALS['TL_DCA'][$dataContainer->table]['fields'][$strField])) {
            throw new BadRequestHttpException('Invalid field name: '.$strField);
        }

        $dcaField = $GLOBALS['TL_DCA'][$dataContainer->table]['fields'][$strField];
        $varValue = Input::post('value', true);

        if (
            '' !== $varValue
            && !$this->ensureFileIsInsideGallery($intId, $varValue)
        ) {
            throw new BadRequestHttpException('Image from invalid folder selected: '.$varValue);
        }

        $file = $varValue ? FilesModel::findByPath($varValue) : null;

        $varCoverUuid = $file?->uuid;

        /** @var class-string<FileTree|PageTree|Picker> $strClass */
        $strClass = $GLOBALS['BE_FFL']['fileTree'] ?? null;
        $objWidget = new $strClass($strClass::getAttributesFromDca($dcaField, $dataContainer->inputName,
            $varCoverUuid, $strField, $dataContainer->table, $dataContainer));

        throw new ResponseException(new Response($objWidget->generate()));
    }

    private function ensureFileIsInsideGallery(string $galleryPath, string $selectedFile): bool
    {
        return \dirname($selectedFile) === $galleryPath
            && str_starts_with($selectedFile, rtrim($galleryPath, '/').'/');
    }
}
