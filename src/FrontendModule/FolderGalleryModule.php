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

use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryContentFactory;
use Cgoit\ContaoFolderGalleryBundle\Factory\GalleryOverviewFactory;
use Cgoit\ContaoFolderGalleryBundle\Repository\GalleryRepositoryInterface;
use Cgoit\ContaoFolderGalleryBundle\Twig\GalleryFolderRenderer;
use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FilesModel;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
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
        private readonly GalleryContentFactory $contentFactory,
        private readonly GalleryFolderRenderer $folderRenderer,
        private readonly PageFinder $pageFinder,
        private readonly ContaoFramework $framework,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $GLOBALS['TL_CSS'][] = 'bundles/cgoitfoldergallery/folder-gallery.css';

        $rootDir = FilesModel::findById($model->galleryRoot);
        if (null === $rootDir) {
            throw new PageNotFoundException();
        }

        $page = $this->pageFinder->getCurrentPage();
        if (null === $page) {
            throw new PageNotFoundException();
        }

        $path = trim((string) $request->attributes->get('parameters', ''), '/');

        // Folder gallery routes use the raw "parameters" attribute.
        // The legacy InputEnhancer interprets path fragments as
        // key/value pairs and would otherwise trigger an
        // UnusedArgumentsException.
        $this->framework
            ->getAdapter(Input::class)
            ->setUnusedRouteParameters([])
        ;

        if ('' === $path) {
            return $this->renderOverview($template, $model, $page, $rootDir);
        }

        return $this->renderContent($template, $model, $page, $rootDir, $path);
    }

    private function renderOverview(FragmentTemplate $template, ModuleModel $model, PageModel $page, FilesModel $rootDir): Response
    {
        $overview = $this->repository->findOverview($rootDir->path);
        $overviewViewModel = $this->overviewFactory->create($overview, $page, $model->galleryCoverSize);

        $items = array_map(
            fn (GalleryFolderViewModel $folder) => $this->folderRenderer->render(
                $folder,
                $model->galleryFolderTpl ?: 'component/gallery_folder',
            ),
            $overviewViewModel->folders,
        );

        $template->set('items', $items);

        return $template->getResponse();
    }

    private function renderContent(FragmentTemplate $template, ModuleModel $model, PageModel $page, FilesModel $rootDir, string $path): Response
    {
        $folder = $this->repository->findOverview($rootDir->path)
            ->findFolderByPath($path)
        ;

        if (null === $folder) {
            throw new PageNotFoundException();
        }

        $template->set(
            'content',
            $this->contentFactory->create($folder, $page, $model->galleryImageSize, $model->galleryCoverImageSize),
        );

        return $template->getResponse();
    }
}
