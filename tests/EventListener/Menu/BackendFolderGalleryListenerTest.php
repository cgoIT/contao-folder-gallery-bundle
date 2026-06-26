<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Tests\EventListener\Menu;

use Cgoit\ContaoFolderGalleryBundle\EventListener\Menu\BackendFolderGalleryListener;
use Contao\CoreBundle\Event\MenuEvent;
use Contao\TestCase\ContaoTestCase;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(BackendFolderGalleryListener::class)]
final class BackendFolderGalleryListenerTest extends ContaoTestCase
{
    public function testDoesNothingForOtherMenu(): void
    {
        $factory = new MenuFactory();
        $menu = new MenuItem('foo', $factory);

        $listener = $this->createListener();

        $listener(new MenuEvent($factory, $menu));

        $this->assertNotInstanceOf(ItemInterface::class, $menu->getChild('folder-gallery'));
    }

    public function testDoesNothingIfContentNodeDoesNotExist(): void
    {
        $factory = new MenuFactory();
        $menu = new MenuItem('mainMenu', $factory);

        $listener = $this->createListener();

        $listener(new MenuEvent($factory, $menu));

        $this->assertNotInstanceOf(ItemInterface::class, $menu->getChild('folder-gallery'));
    }

    public function testAddsMenuEntry(): void
    {
        $factory = new MenuFactory();

        $menu = new MenuItem('mainMenu', $factory);
        $content = $menu->addChild('content');

        $listener = $this->createListener();

        $listener(new MenuEvent($factory, $menu));

        $node = $content->getChild('folder-gallery');

        $this->assertInstanceOf(ItemInterface::class, $node);

        $this->assertSame('MOD.folder_gallery.0', $node->getLabel());
        $this->assertSame('/contao/folder-gallery', $node->getUri());
        $this->assertSame('navigation folder-gallery', $node->getLinkAttribute('class'));
        $this->assertSame('Folder galleries', $node->getLinkAttribute('title'));
        $this->assertSame('contao_modules', $node->getExtra('translation_domain'));
    }

    public function testMarksCurrentMenuEntry(): void
    {
        $factory = new MenuFactory();

        $menu = new MenuItem('mainMenu', $factory);
        $content = $menu->addChild('content');

        $listener = $this->createListener('cgoit_folder_gallery');

        $listener(new MenuEvent($factory, $menu));

        $this->assertTrue($content->getChild('folder-gallery')->isCurrent());
    }

    public function testDoesNotMarkCurrentMenuEntry(): void
    {
        $factory = new MenuFactory();

        $menu = new MenuItem('mainMenu', $factory);
        $content = $menu->addChild('content');

        $listener = $this->createListener('contao_dashboard');

        $listener(new MenuEvent($factory, $menu));

        $this->assertFalse($content->getChild('folder-gallery')->isCurrent());
    }

    private function createListener(string|null $route = null): BackendFolderGalleryListener
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->atMost(1))
            ->method('generate')
            ->with('cgoit_folder_gallery')
            ->willReturn('/contao/folder-gallery')
        ;

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturn('Folder galleries')
        ;

        $request = new Request();

        if (null !== $route) {
            $request->attributes->set('_route', $route);
        }

        $requestStack = new RequestStack([$request]);

        return new BackendFolderGalleryListener(
            $router,
            $requestStack,
            $translator,
        );
    }
}
