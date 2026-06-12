<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Provider;

interface FilesModelProviderInterface
{
    public function findByPath(string $path): ImageFile|null;
}
