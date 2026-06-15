<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\FrontendModule;

use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryOverviewFactory;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(
    category: 'miscellaneous',
    template: 'frontend_module/gallery_overview',
    type: self::TYPE,
)]
final class GalleryOverviewModule extends AbstractFrontendModuleController
{
    private const string TYPE = 'gallery_overview';

    private const string GALLERY_ROOT = 'files/gallery';

    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
        private readonly GalleryOverviewFactory $overviewFactory,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $overview = $this->repository->findOverview(
            self::GALLERY_ROOT,
        );

        $template->set(
            'overview',
            $this->overviewFactory->create(
                $overview,
                null,
            ),
        );

        return $template->getResponse();
    }
}
