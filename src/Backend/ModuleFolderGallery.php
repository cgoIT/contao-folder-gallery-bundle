<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Backend;

use Cgoit\ContaoFolderGalleryBundle\Provider\GalleryFolderProviderInterface;
use Contao\BackendModule;
use Contao\BackendTemplate;
use Contao\DataContainer;
use Contao\System;

/**
 * @property BackendTemplate $Template
 */
final class ModuleFolderGallery extends BackendModule
{
    public const string TYPE = 'folder_gallery';

    protected $strTemplate = 'be_folder_gallery';

    private readonly GalleryFolderProviderInterface $folderProvider;

    public function __construct(DataContainer|null $dc = null)
    {
        parent::__construct($dc);

        $this->folderProvider = System::getContainer()->get(GalleryFolderProviderInterface::class);
    }

    protected function compile(): void
    {
        System::loadLanguageFile(self::TYPE);

        $this->Template->folders = $this->folderProvider->findAllFolders();
    }
}
