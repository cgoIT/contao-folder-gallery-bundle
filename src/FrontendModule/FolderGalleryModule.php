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
use Cgoit\ContaoFolderGalleryBundle\Twig\GalleryFolderRenderer;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FilesModel;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(
    category: 'application',
    template: 'frontend_module/folder_gallery',
    type: self::TYPE,
)]
final class FolderGalleryModule extends AbstractFrontendModuleController
{
    public const string TYPE = 'folder_gallery';

    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
        private readonly GalleryOverviewFactory $overviewFactory,
        private readonly GalleryFolderRenderer $folderRenderer,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $GLOBALS['TL_CSS'][] = 'bundles/cgoitfoldergallery/folder-gallery.css';

        $rootDir = FilesModel::findById($model->galleryRoot);

        if (null !== $rootDir) {
            $overview = $this->repository->findOverview($rootDir->path);

            $overviewViewModel = $this->overviewFactory->create($overview, $model->galleryCoverSize);

            $items = array_map(
                fn (GalleryFolderViewModel $folder) => $this->folderRenderer->render(
                    $folder,
                    $model->galleryFolderTpl ?: 'components/gallery_folder',
                ),
                $overviewViewModel->folders,
            );

            $template->set('items', $items);
        }

        return $template->getResponse();
    }
}
