<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\DependencyInjection;

use Cgoit\ContaoFolderGalleryBundle\DependencyInjection\CgoitFolderGalleryExtension;
use Cgoit\ContaoFolderGalleryBundle\Metadata\MetadataReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CgoitFolderGalleryExtensionTest extends TestCase
{
    /**
     * Test that the extension loads config files and registers services.
     */
    public function testExtensionLoadsServices(): void
    {
        // 1. Arrange: Create the Extension and an empty ContainerBuilder
        $extension = new CgoitFolderGalleryExtension();
        $container = new ContainerBuilder();

        // 2. Act: Trigger the load method just like Symfony does at boot
        $extension->load([], $container);

        // 3. Assert: Verify your services exist in the processed container
        $serviceId = MetadataReader::class;

        $this->assertTrue(
            $container->hasDefinition($serviceId),
            \sprintf('Service "%s" was not registered by the extension.', $serviceId),
        );

        // 4. Assert: Deep-dive into definition configuration
        $definition = $container->getDefinition($serviceId);

        // Ensure the class name is mapped properly
        $this->assertSame(MetadataReader::class, $definition->getClass());

        // Ensure it is configured for autowiring if applicable
        $this->assertTrue($definition->isAutowired());
    }
}
