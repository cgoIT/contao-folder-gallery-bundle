<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

use Contao\FilesModel;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(FilesModelProviderInterface::class)]
final class ContaoFilesModelProvider implements FilesModelProviderInterface
{
    public function findByPath(string $path): ImageFile|null
    {
        $model = FilesModel::findByPath($path);

        if (!$model instanceof FilesModel) {
            return null;
        }

        return new ImageFile(
            StringUtil::binToUuid($model->uuid),
            $model->path,
        );
    }
}
