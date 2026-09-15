<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\ViewModel\GalleryFolderViewModel;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\String\HtmlDecoder;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(GallerySchemaOrgFactoryInterface::class)]
final readonly class GallerySchemaOrgFactory implements GallerySchemaOrgFactoryInterface
{
    public function __construct(private HtmlDecoder $htmlDecoder)
    {
    }

    /**
     * Builds the "ImageGallery" JSON-LD data for a gallery folder. Images are
     * referenced by "@id" only (matching the identifier each figure's own
     * "ImageObject" node already uses), so the per-image data added via
     * add_schema_org() in the figure template is not duplicated here.
     *
     * @param list<Figure> $images
     *
     * @return array<string, mixed>
     */
    public function create(GalleryFolderViewModel $folder, array $images): array
    {
        $jsonLd = [
            '@type' => 'ImageGallery',
            'name' => $this->htmlDecoder->inputEncodedToPlainText($folder->title),
        ];

        if (null !== $folder->description && '' !== $folder->description) {
            $jsonLd['description'] = $this->htmlDecoder->htmlToPlainText($folder->description);
        }

        $associatedMedia = array_values(array_filter(array_map(
            $this->createImageReference(...),
            $images,
        )));

        if ([] !== $associatedMedia) {
            $jsonLd['associatedMedia'] = $associatedMedia;
        }

        return $jsonLd;
    }

    /**
     * @return array<string, string>|null
     */
    private function createImageReference(Figure $figure): array|null
    {
        $identifier = $figure->getSchemaOrgData()['identifier'] ?? null;

        if (!\is_string($identifier) || '' === $identifier) {
            return null;
        }

        return ['@id' => $identifier];
    }
}
