<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\EventListener\DataContainer;

use Cgoit\ContaoFolderGalleryBundle\Cache\GalleryCacheInvalidator;
use Cgoit\ContaoFolderGalleryBundle\Controller\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Cgoit\ContaoFolderGalleryBundle\Provider\LightboxSizeProvider;
use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\ImageSizes;
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\DataContainer;
use Contao\Message;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ModuleCallbacks
{
    public function __construct(
        private FinderFactory $finderFactory,
        private Security $security,
        private ImageSizes $imageSizes,
        private GalleryCacheInvalidator $galleryCacheInvalidator,
        private ContaoFramework $framework,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.galleryCoverImageSize.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.galleryImageSize.options')]
    #[AsCallback(table: 'tl_module', target: 'fields.galleryLightboxSize.options')]
    public function getImageSizes(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            return [];
        }

        return $this->imageSizes->getOptionsForUser($user);
    }

    /**
     * @return array<mixed>
     *
     * @codeCoverageIgnore
     */
    #[AsCallback(table: 'tl_module', target: 'fields.galleryFolderTpl.options')]
    public function getGalleryFolderTemplates(): array
    {
        return $this->finderFactory
            ->create()
            ->identifier('component/gallery_folder')
            ->extension('html.twig')
            ->withVariants()
            ->excludePartials()
            ->asTemplateOptions()
        ;
    }

    /**
     * @return array<mixed>
     *
     * @codeCoverageIgnore
     */
    #[AsCallback(table: 'tl_module', target: 'fields.galleryContentTpl.options')]
    public function getGalleryContentTemplates(): array
    {
        return $this->finderFactory
            ->create()
            ->identifier('component/gallery_content')
            ->extension('html.twig')
            ->withVariants()
            ->excludePartials()
            ->asTemplateOptions()
        ;
    }

    #[AsCallback(table: 'tl_module', target: 'config.onsubmit')]
    public function onFolderGalleryModuleSaved(DataContainer $dc): void
    {
        $currentData = $dc->getCurrentRecord();

        if (
            null === $currentData
            || !\array_key_exists('type', $currentData)
            || FolderGalleryModule::TYPE !== $currentData['type']
        ) {
            return;
        }

        $this->galleryCacheInvalidator->invalidate();
        $this->addLightboxSizeHint($currentData);
    }

    /**
     * Without a lightbox size the large view links to the original image, which is
     * not reachable in protected folders. The page layouts the module is used in are
     * not determined, so the hint is shown whenever the module has no size.
     *
     * @param array<string, mixed> $currentData
     */
    private function addLightboxSizeHint(array $currentData): void
    {
        $viewer = GalleryViewer::tryFrom((string) ($currentData['galleryViewer'] ?? '')) ?? GalleryViewer::Lightbox;

        if (GalleryViewer::None === $viewer) {
            return;
        }

        if (null !== LightboxSizeProvider::normalize($currentData['galleryLightboxSize'] ?? null)) {
            return;
        }

        $this->framework->getAdapter(Message::class)->addInfo(
            $this->translator->trans('tl_module.galleryLightboxSizeMissing', [], 'contao_tl_module'),
        );
    }
}
