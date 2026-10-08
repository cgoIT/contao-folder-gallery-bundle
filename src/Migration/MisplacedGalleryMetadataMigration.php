<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Migration;

use Cgoit\ContaoFolderGalleryBundle\Controller\FrontendModule\FolderGalleryModule;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Up to v1.10.1 the metadata editor resolved gallery directories relative to the
 * working directory of the web request (the public dir). For protected gallery
 * folders it therefore wrote the _metadata.yml to "public/files/…" instead of
 * "files/…". This migration moves these files to their gallery folders and removes
 * the directories that were only created for them.
 */
final class MisplacedGalleryMetadataMigration extends AbstractMigration
{
    public const string ORPHANED_FILE_NAME = '_metadata.orphaned.yml';

    public function __construct(
        private readonly Connection $connection,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
        private readonly string $webDir,
        #[Autowire('%contao.upload_path%')]
        private readonly string $uploadPath,
    ) {
    }

    public function getName(): string
    {
        return 'Move misplaced folder gallery metadata files out of the public directory';
    }

    public function shouldRun(): bool
    {
        return [] !== $this->findMisplacedFiles();
    }

    public function run(): MigrationResult
    {
        $moved = [];
        $conflicts = [];

        foreach ($this->findMisplacedFiles() as $source => $target) {
            if (!is_file($target)) {
                $this->filesystem->rename($source, $target);
                $moved[] = $this->toProjectPath($target);
            } elseif (file_get_contents($source) === file_get_contents($target)) {
                $this->filesystem->remove($source);
            } else {
                // Never overwrite a metadata file maintained in the gallery folder; keep the
                // misplaced one next to it so it can be merged by hand
                $orphaned = $this->getOrphanedFileName(\dirname($target));
                $this->filesystem->rename($source, $orphaned);
                $conflicts[] = $this->toProjectPath($orphaned);
            }

            $this->removeEmptyDirectories(\dirname($source));
        }

        $message = \sprintf('%s: %d file(s) moved', $this->getName(), \count($moved));

        if ([] !== $conflicts) {
            $message .= \sprintf(
                '. The following gallery folders already had a %s, please merge by hand: %s',
                GalleryMetadata::METADATA_FILE_NAME,
                implode(', ', $conflicts),
            );
        }

        return $this->createResult(true, $message);
    }

    /**
     * @return array<string, string> Misplaced file => target file
     */
    private function findMisplacedFiles(): array
    {
        $files = [];

        foreach ($this->getGalleryRoots() as $root) {
            $publicRoot = Path::join($this->webDir, $root);
            $projectRoot = Path::join($this->projectDir, $root);

            if (!is_dir($publicRoot) || !is_dir($projectRoot)) {
                continue;
            }

            // A public gallery folder (or one of its parents) is a symlink to the
            // project dir, the files found there are the real ones
            if (realpath($publicRoot) === realpath($projectRoot)) {
                continue;
            }

            // Symlinks to public sub folders are not followed
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($publicRoot, \FilesystemIterator::SKIP_DOTS),
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (GalleryMetadata::METADATA_FILE_NAME !== $file->getFilename() || $file->isLink() || !$file->isFile()) {
                    continue;
                }

                $source = Path::canonicalize($file->getPathname());
                $target = Path::join($projectRoot, Path::makeRelative($source, $publicRoot));

                // The gallery folder no longer exists, there is nothing to attach the file to
                if (!is_dir(\dirname($target))) {
                    continue;
                }

                $files[$source] = $target;
            }
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    private function getGalleryRoots(): array
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_module', 'tl_files'])) {
            return [];
        }

        if (!isset($schemaManager->listTableColumns('tl_module')['galleryroot'])) {
            return [];
        }

        $paths = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT f.path FROM tl_module m INNER JOIN tl_files f ON f.uuid = m.galleryRoot WHERE m.type = ?',
            [FolderGalleryModule::TYPE],
        );

        return array_values(array_filter(
            array_map(strval(...), $paths),
            fn (string $path): bool => !Path::isAbsolute($path) && Path::isBasePath($this->uploadPath, $path),
        ));
    }

    private function getOrphanedFileName(string $directory): string
    {
        $filename = Path::join($directory, self::ORPHANED_FILE_NAME);

        for ($i = 1; $this->filesystem->exists($filename); ++$i) {
            $filename = Path::join($directory, \sprintf('_metadata.orphaned-%d.yml', $i));
        }

        return $filename;
    }

    /**
     * Removes the directories that were created for the misplaced file, but never the
     * public upload dir itself or anything Contao put there (e.g. symlinks).
     */
    private function removeEmptyDirectories(string $directory): void
    {
        $publicUploadDir = Path::join($this->webDir, $this->uploadPath);

        while (
            Path::isBasePath($publicUploadDir, $directory)
            && $directory !== $publicUploadDir
            && !is_link($directory)
            && is_dir($directory)
            && [] === array_diff(scandir($directory) ?: ['?'], ['.', '..'])
        ) {
            $this->filesystem->remove($directory);
            $directory = \dirname($directory);
        }
    }

    private function toProjectPath(string $path): string
    {
        return Path::makeRelative($path, $this->projectDir);
    }
}
