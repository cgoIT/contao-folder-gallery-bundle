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

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(FilesModelProviderInterface::class)]
final readonly class GalleryFilesModelProvider implements FilesModelProviderInterface
{
    public function __construct(private ContaoFramework $framework)
    {
    }

    public function findByPath(string $path): ImageFile|null
    {
        $model = $this->framework
            ->getAdapter(FilesModel::class)
            ->findByPath($path)
        ;

        if (!$model instanceof FilesModel) {
            return null;
        }

        return new ImageFile(
            StringUtil::binToUuid($model->uuid),
            $model->path,
        );
    }
}
