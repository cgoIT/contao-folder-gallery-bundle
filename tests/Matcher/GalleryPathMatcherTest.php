<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Matcher;

use Cgoit\ContaoFolderGalleryBundle\Matcher\GalleryPathMatcher;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryRoot;
use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryRootProviderInterface;
use Contao\CoreBundle\Filesystem\Dbafs\ChangeSet\ChangeSet;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GalleryPathMatcher::class)]
#[AllowMockObjectsWithoutExpectations]
final class GalleryPathMatcherTest extends TestCase
{
    #[DataProvider('affectedGalleryProvider')]
    public function testDetectsAffectedGallery(ChangeSet $changeSet): void
    {
        $provider = $this->createStub(GalleryRootProviderInterface::class);
        $provider
            ->method('getGalleryRoots')
            ->willReturn([
                new GalleryRoot('module', 1, 'files/gallery'),
            ])
        ;

        $matcher = new GalleryPathMatcher($provider);

        $this->assertTrue($matcher->affectsGallery($changeSet));
    }

    #[DataProvider('unaffectedGalleryProvider')]
    public function testIgnoresUnrelatedChanges(ChangeSet $changeSet): void
    {
        $provider = $this->createStub(GalleryRootProviderInterface::class);
        $provider
            ->method('getGalleryRoots')
            ->willReturn([
                new GalleryRoot('module', 1, 'files/gallery'),
            ])
        ;

        $matcher = new GalleryPathMatcher($provider);

        $this->assertFalse($matcher->affectsGallery($changeSet));
    }

    /**
     * @return iterable<array{ChangeSet}>
     */
    public static function affectedGalleryProvider(): iterable
    {
        yield 'created file inside gallery' => [
            new ChangeSet(
                [
                    [
                        'hash' => 'abc',
                        'path' => 'files/gallery/2025/image.jpg',
                        'type' => ChangeSet::TYPE_FILE,
                    ],
                ],
                [],
                [],
            ),
        ];

        yield 'created directory inside gallery' => [
            new ChangeSet(
                [
                    [
                        'hash' => 'abc',
                        'path' => 'files/gallery/2025',
                        'type' => ChangeSet::TYPE_DIRECTORY,
                    ],
                ],
                [],
                [],
            ),
        ];

        yield 'updated file inside gallery' => [
            new ChangeSet(
                [],
                [
                    'files/gallery/2025/image.jpg' => [
                        'hash' => 'newhash',
                    ],
                ],
                [],
            ),
        ];

        yield 'deleted file inside gallery' => [
            new ChangeSet(
                [],
                [],
                [
                    'files/gallery/2025/image.jpg' => ChangeSet::TYPE_FILE,
                ],
            ),
        ];

        yield 'gallery root itself changed' => [
            new ChangeSet(
                [
                    [
                        'hash' => 'abc',
                        'path' => 'files/gallery',
                        'type' => ChangeSet::TYPE_DIRECTORY,
                    ],
                ],
                [],
                [],
            ),
        ];
    }

    /**
     * @return iterable<array{ChangeSet}>
     */
    public static function unaffectedGalleryProvider(): iterable
    {
        yield 'created file outside gallery' => [
            new ChangeSet(
                [
                    [
                        'hash' => 'abc',
                        'path' => 'files/downloads/manual.pdf',
                        'type' => ChangeSet::TYPE_FILE,
                    ],
                ],
                [],
                [],
            ),
        ];

        yield 'updated file outside gallery' => [
            new ChangeSet(
                [],
                [
                    'files/downloads/manual.pdf' => [
                        'hash' => 'newhash',
                        'path' => 'files/downloads_new/manual.pdf',
                    ],
                ],
                [],
            ),
        ];

        yield 'deleted file outside gallery' => [
            new ChangeSet(
                [],
                [],
                [
                    'files/downloads/manual.pdf' => ChangeSet::TYPE_FILE,
                ],
            ),
        ];

        yield 'empty change set' => [
            ChangeSet::createEmpty(),
        ];
    }
}
