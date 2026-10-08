<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Tests\Migration;

use Cgoit\ContaoFolderGalleryBundle\Migration\MisplacedGalleryMetadataMigration;
use Cgoit\ContaoFolderGalleryBundle\Tests\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(MisplacedGalleryMetadataMigration::class)]
final class MisplacedGalleryMetadataMigrationTest extends TestCase
{
    private string $projectDir;

    private string $webDir;

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->projectDir = sys_get_temp_dir().'/gallery-migration-'.uniqid('', true);
        $this->webDir = $this->projectDir.'/public';

        $this->filesystem->mkdir([
            $this->projectDir.'/files/gallery/2025',
            $this->projectDir.'/files/gallery/2026',
            $this->webDir.'/files',
        ]);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDir);

        parent::tearDown();
    }

    public function testMovesMisplacedFilesOfProtectedGallery(): void
    {
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/_metadata.yml', 'title: Root');
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/2025/_metadata.yml', 'title: 2025');

        $migration = $this->createMigration(['files/gallery']);

        $this->assertTrue($migration->shouldRun());

        $result = $migration->run();

        $this->assertTrue($result->isSuccessful());
        $this->assertStringContainsString('2 file(s) moved', $result->getMessage());
        $this->assertStringEqualsFile($this->projectDir.'/files/gallery/_metadata.yml', 'title: Root');
        $this->assertStringEqualsFile($this->projectDir.'/files/gallery/2025/_metadata.yml', 'title: 2025');
        $this->assertDirectoryDoesNotExist($this->webDir.'/files/gallery');
        $this->assertDirectoryExists($this->webDir.'/files');
        $this->assertFalse($migration->shouldRun());
    }

    public function testIgnoresPublicGallery(): void
    {
        $this->filesystem->dumpFile($this->projectDir.'/files/gallery/_metadata.yml', 'title: Root');
        $this->filesystem->symlink('../../files/gallery', $this->webDir.'/files/gallery');

        $this->assertFalse($this->createMigration(['files/gallery'])->shouldRun());
        $this->assertFileExists($this->projectDir.'/files/gallery/_metadata.yml');
    }

    public function testKeepsSymlinksToPublicSubfolders(): void
    {
        $this->filesystem->dumpFile($this->projectDir.'/files/gallery/2025/_metadata.yml', 'title: 2025');
        $this->filesystem->mkdir($this->webDir.'/files/gallery');
        $this->filesystem->symlink('../../../files/gallery/2025', $this->webDir.'/files/gallery/2025');
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/_metadata.yml', 'title: Root');

        $migration = $this->createMigration(['files/gallery']);
        $result = $migration->run();

        $this->assertStringContainsString('1 file(s) moved', $result->getMessage());
        $this->assertStringEqualsFile($this->projectDir.'/files/gallery/_metadata.yml', 'title: Root');
        $this->assertStringEqualsFile($this->projectDir.'/files/gallery/2025/_metadata.yml', 'title: 2025');
        $this->assertTrue(is_link($this->webDir.'/files/gallery/2025'));
        $this->assertFalse($migration->shouldRun());
    }

    public function testRemovesIdenticalDuplicate(): void
    {
        $this->filesystem->dumpFile($this->projectDir.'/files/gallery/_metadata.yml', 'title: Root');
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/_metadata.yml', 'title: Root');

        $result = $this->createMigration(['files/gallery'])->run();

        $this->assertStringContainsString('0 file(s) moved', $result->getMessage());
        $this->assertStringNotContainsString('merge by hand', $result->getMessage());
        $this->assertDirectoryDoesNotExist($this->webDir.'/files/gallery');
        $this->assertFileDoesNotExist($this->projectDir.'/files/gallery/'.MisplacedGalleryMetadataMigration::ORPHANED_FILE_NAME);
    }

    public function testKeepsExistingMetadataOnConflict(): void
    {
        $this->filesystem->dumpFile($this->projectDir.'/files/gallery/_metadata.yml', 'title: By hand');
        $this->filesystem->dumpFile($this->projectDir.'/files/gallery/'.MisplacedGalleryMetadataMigration::ORPHANED_FILE_NAME, 'title: Older');
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/_metadata.yml', 'title: Editor');

        $migration = $this->createMigration(['files/gallery']);
        $result = $migration->run();

        $this->assertStringContainsString('files/gallery/_metadata.orphaned-1.yml', $result->getMessage());
        $this->assertStringEqualsFile($this->projectDir.'/files/gallery/_metadata.yml', 'title: By hand');
        $this->assertStringEqualsFile($this->projectDir.'/files/gallery/_metadata.orphaned-1.yml', 'title: Editor');
        $this->assertDirectoryDoesNotExist($this->webDir.'/files/gallery');
        $this->assertFalse($migration->shouldRun());
    }

    public function testSkipsFilesOfDeletedGalleryFolders(): void
    {
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/2024/_metadata.yml', 'title: 2024');

        $this->assertFalse($this->createMigration(['files/gallery'])->shouldRun());
    }

    public function testIgnoresRootsOutsideTheUploadPath(): void
    {
        $this->filesystem->mkdir($this->projectDir.'/templates/gallery');
        $this->filesystem->dumpFile($this->webDir.'/templates/gallery/_metadata.yml', 'title: Root');
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/_metadata.yml', 'title: Root');

        $this->assertFalse($this->createMigration(['templates/gallery', 'files/../templates/gallery', '/files/gallery'])->shouldRun());
    }

    public function testDoesNotRunWithoutGalleryRootColumn(): void
    {
        $this->filesystem->dumpFile($this->webDir.'/files/gallery/_metadata.yml', 'title: Root');

        $this->assertFalse($this->createMigration(['files/gallery'], false)->shouldRun());
    }

    /**
     * @param list<string> $roots
     */
    private function createMigration(array $roots, bool $hasColumn = true): MisplacedGalleryMetadataMigration
    {
        $schemaManager = $this->createStub(AbstractSchemaManager::class);
        $schemaManager
            ->method('tablesExist')
            ->willReturn(true)
        ;

        $schemaManager
            ->method('listTableColumns')
            ->willReturn($hasColumn ? ['galleryroot' => $this->createStub(Column::class)] : [])
        ;

        $connection = $this->createStub(Connection::class);
        $connection
            ->method('createSchemaManager')
            ->willReturn($schemaManager)
        ;

        $connection
            ->method('fetchFirstColumn')
            ->willReturn($roots)
        ;

        return new MisplacedGalleryMetadataMigration($connection, $this->filesystem, $this->projectDir, $this->webDir, 'files');
    }
}
