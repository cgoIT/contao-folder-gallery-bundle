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
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProviderInterface;
use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GalleryBackendController extends AbstractBackendController
{
    public const string TYPE = 'folder_gallery';

    public function __construct(private readonly GalleryFolderProviderInterface $folderProvider)
    {
    }

    #[Route(
        '/%contao.backend.route_prefix%/folder-gallery',
        name: 'cgoit_folder_gallery',
        defaults: ['_scope' => 'backend'],
    )]
    public function __invoke(Request $request): Response
    {
        System::loadLanguageFile('tl_gallery_metadata');

        $editor = null;

        if (null !== $request->query->get('id')) {
            $editor = (new DC_GalleryMetadata('tl_gallery_metadata'))->edit();
        }

        return $this->render('@Contao/backend/folder_gallery/index.html.twig', [
            'overviews' => $this->folderProvider->findAllOverviews(true),
            'editor' => $editor,
        ]);
    }
}
