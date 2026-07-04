<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\EventListener\Menu;

use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener]
final readonly class BackendFolderGalleryListener
{
    public function __construct(
        private RouterInterface $router,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {
    }

    public function __invoke(MenuEvent $event): void
    {
        if ('mainMenu' !== $event->getTree()->getName()) {
            return;
        }

        $categoryNode = $event->getTree()->getChild('content');

        if (!$categoryNode) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        $node = $event->getFactory()
            ->createItem('folder-gallery')
            ->setLabel('MOD.folder_gallery.0')
            ->setExtra('translation_domain', 'contao_modules')
            ->setUri($this->router->generate('cgoit_folder_gallery'))
            ->setLinkAttribute('class', 'navigation folder-gallery')
            ->setLinkAttribute(
                'title',
                $this->translator->trans(
                    'MOD.folder_gallery.1',
                    [],
                    'contao_modules',
                ),
            )
            ->setCurrent(
                'cgoit_folder_gallery' === $request?->attributes->get('_route'),
            )
        ;

        $categoryNode->addChild($node);

        (new MenuManipulator())->moveToLastPosition($node);
    }
}
