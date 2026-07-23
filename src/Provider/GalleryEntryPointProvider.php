<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryEntryPoint;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;

final readonly class GalleryEntryPointProvider implements GalleryEntryPointProviderInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly GalleryRootProviderInterface $galleryRootProvider,
    ) {
    }

    /**
     * @return list<GalleryEntryPoint>
     */
    public function getEntryPoints(): array
    {
        $entryPoints = [];

        foreach ($this->galleryRootProvider->getGalleryRoots() as $galleryRoot) {
            $entryPoints = [
                ...$entryPoints,
                ...$this->findContentElementEntryPoints($galleryRoot),
                ...$this->findLayoutEntryPoints($galleryRoot),
            ];
        }

        return $this->removeDuplicates($entryPoints);
    }

    /**
     * @return list<GalleryEntryPoint>
     */
    private function findContentElementEntryPoints(GalleryRoot $galleryRoot): array
    {
        $entryPoints = [];

        foreach ($this->findContentElements($galleryRoot->moduleId) as $contentElement) {
            $article = $this->findArticle((int) $contentElement->pid);

            if (!$article instanceof ArticleModel) {
                continue;
            }

            $page = $this->findPage((int) $article->pid);

            if (!$page instanceof PageModel) {
                continue;
            }

            $entryPoints[] = $this->createEntryPoint($galleryRoot, $page);
        }

        return $entryPoints;
    }

    /**
     * @return list<GalleryEntryPoint>
     */
    private function findLayoutEntryPoints(GalleryRoot $galleryRoot): array
    {
        $entryPoints = [];

        $module = $this->findModule($galleryRoot->moduleId);

        if (!$module instanceof ModuleModel) {
            return [];
        }

        foreach ($this->findLayouts((int) $module->pid) as $layout) {
            $modules = StringUtil::deserialize($layout->modules, true);

            foreach ($modules as $layoutModule) {
                if ($galleryRoot->moduleId !== (int) ($layoutModule['mod'] ?? 0)) {
                    continue;
                }

                foreach ($this->findPagesByLayout((int) $layout->id) as $page) {
                    $entryPoints[] = $this->createEntryPoint($galleryRoot, $page);
                }

                break;
            }
        }

        return $entryPoints;
    }

    /**
     * @param array<GalleryEntryPoint> $entryPoints
     *
     * @return list<GalleryEntryPoint>
     */
    private function removeDuplicates(array $entryPoints): array
    {
        $result = [];

        foreach ($entryPoints as $entryPoint) {
            $key = \sprintf(
                '%d-%d',
                $entryPoint->galleryRoot->moduleId,
                $entryPoint->page->id,
            );

            $result[$key] = $entryPoint;
        }

        return array_values($result);
    }

    /**
     * @return list<ContentModel>
     */
    private function findContentElements(int $moduleId): array
    {
        $result = $this->framework
            ->getAdapter(ContentModel::class)
            ->findBy(
                [
                    'type = ?',
                    'module = ?',
                ],
                [
                    'module',
                    $moduleId,
                ],
            )
        ;

        if (null === $result) {
            return [];
        }

        return $result->getModels();
    }

    private function findArticle(int $articleId): ArticleModel|null
    {
        return $this->framework
            ->getAdapter(ArticleModel::class)
            ->findById($articleId)
        ;
    }

    private function findPage(int $pageId): PageModel|null
    {
        return $this->framework
            ->getAdapter(PageModel::class)
            ->findById($pageId)
        ;
    }

    private function findModule(int $moduleId): ModuleModel|null
    {
        return $this->framework
            ->getAdapter(ModuleModel::class)
            ->findById($moduleId)
        ;
    }

    /**
     * @return list<LayoutModel>
     */
    private function findLayouts(int $themeId): array
    {
        $result = $this->framework
            ->getAdapter(LayoutModel::class)
            ->findByPid($themeId)
        ;

        if (null === $result) {
            return [];
        }

        return $result->getModels();
    }

    /**
     * @return list<PageModel>
     */
    private function findPagesByLayout(int $layoutId): array
    {
        $result = $this->framework
            ->getAdapter(PageModel::class)
            ->findBy('layout', $layoutId)
        ;

        if (null === $result) {
            return [];
        }

        return $result->getModels();
    }

    private function createEntryPoint(GalleryRoot $galleryRoot, PageModel $page): GalleryEntryPoint
    {
        return new GalleryEntryPoint(
            galleryRoot: $galleryRoot,
            page: $page,
        );
    }
}
