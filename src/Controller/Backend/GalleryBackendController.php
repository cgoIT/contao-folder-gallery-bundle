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
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

final class GalleryBackendController extends AbstractBackendController
{
    public const string ROUTE_NAME = 'cgoit_folder_gallery';

    public const string TYPE = 'folder_gallery';

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly UrlGeneratorInterface $router,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly GalleryFolderProviderInterface $folderProvider,
        private readonly GalleryMetadataAjaxHandler $ajaxHandler,
        private readonly DC_GalleryMetadata $dataContainer,
    ) {
    }

    #[Route(
        '/%contao.backend.route_prefix%/folder-gallery',
        name: self::ROUTE_NAME,
        defaults: ['_scope' => 'backend'],
    )]
    public function __invoke(Request $request): Response
    {
        $this->framework
            ->getAdapter(System::class)
            ->loadLanguageFile(GalleryMetadata::DCA_TABLE_NAME)
        ;

        // Check the request token
        if (
            $request->isMethodSafe()
            && !\in_array($request->query->get('act'), [null, 'edit'], true)
            && (null === $request->query->get('rt')
                || !$this->csrfTokenManager->isTokenValid(new CsrfToken($this->getParameter('contao.csrf_token_name'), $request->query->get('rt')))
            )
        ) {
            $objSession = $request->getSession();
            $objSession->set('INVALID_TOKEN_URL', Environment::get('requestUri'));

            return new RedirectResponse($this->router->generate('contao_backend_confirm'));
        }

        $this->dataContainer->initialize(Input::get('id', true));

        // Ajax request
        $action = Input::post('action');
        if ($action && Environment::get('isAjaxRequest')) {
            $this->ajaxHandler->executePostActions($action, $this->dataContainer);
        }

        $editor = null;

        if (null !== $this->dataContainer->id) {
            $editor = $this->dataContainer->edit();
        }

        return $this->render('@Contao/backend/folder_gallery/index.html.twig', [
            'overviews' => $this->folderProvider->findAllOverviews(true),
            'editor' => $editor,
            'id' => $this->dataContainer->id,
        ]);
    }
}
