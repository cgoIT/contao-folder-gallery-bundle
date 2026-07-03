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

use Cgoit\ContaoFolderGalleryBundle\Drivers\DC_GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProviderInterface;
use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Environment;
use Contao\FilesModel;
use Contao\FileTree;
use Contao\Input;
use Contao\PageTree;
use Contao\Picker;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GalleryBackendController extends AbstractBackendController
{
    public const string ROUTE_NAME = 'cgoit_folder_gallery';

    public const string TYPE = 'folder_gallery';

    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly GalleryFolderProviderInterface $folderProvider,
    ) {
    }

    #[Route(
        '/%contao.backend.route_prefix%/folder-gallery',
        name: self::ROUTE_NAME,
        defaults: ['_scope' => 'backend'],
    )]
    public function __invoke(Request $request): Response
    {
        System::loadLanguageFile(GalleryMetadata::DCA_TABLE_NAME);

        $dc = new DC_GalleryMetadata($this->router);

        // Ajax request
        $action = Input::post('action');
        if ($action && Environment::get('isAjaxRequest')) {
            $this->doAjax($action, $dc);
        }

        $editor = null;

        if (null !== $request->query->get('id')) {
            $editor = $dc->edit();
        }

        return $this->render('@Contao/backend/folder_gallery/index.html.twig', [
            'overviews' => $this->folderProvider->findAllOverviews(true),
            'editor' => $editor,
        ]);
    }

    private function doAjax(string $action, DC_GalleryMetadata $dc): void
    {
        if ('reloadFiletree' !== $action) {
            return;
        }

        $intId = Input::get('id', true);
        $strField = $dc->inputName = Input::post('name');

        if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField])) {
            throw new BadRequestHttpException('Invalid field name: '.$strField);
        }

        $dcaField = $GLOBALS['TL_DCA'][$dc->table]['fields'][$strField];
        $varValue = Input::post('value', true);

        if ($varValue) {
            if (\dirname($varValue) !== $intId && !str_starts_with($varValue, rtrim($intId, '/').'/')) {
                throw new BadRequestHttpException('Image from invalid folder selected: '.$varValue);
            }

            $file = FilesModel::findByPath($varValue);

            if (null === $file) {
                throw new BadRequestHttpException(\sprintf('File "%s" not found.', $varValue));
            }

            $varCoverUuid = $file->uuid;

            /** @var class-string<FileTree|PageTree|Picker> $strClass */
            $strClass = $GLOBALS['BE_FFL']['fileTree'] ?? null;
            $objWidget = new $strClass($strClass::getAttributesFromDca($dcaField, $dc->inputName, $varCoverUuid, $strField, $dc->table, $dc));

            throw new ResponseException(new Response($objWidget->generate()));
        }
    }
}
