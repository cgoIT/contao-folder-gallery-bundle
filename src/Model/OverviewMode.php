<?php

namespace Cgoit\ContaoFolderGalleryBundle\Model;

enum OverviewMode: string
{
    case Gallery = 'gallery';
    case Group = 'group';
    case Hidden = 'hidden';
}
