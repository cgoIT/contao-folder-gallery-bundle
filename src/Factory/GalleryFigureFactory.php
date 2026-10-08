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

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Cgoit\ContaoFolderGalleryBundle\Provider\LightboxSizeProvider;
use Contao\CoreBundle\File\Metadata;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\ImageResult;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Image\ImageInterface;
use Contao\Image\PictureConfiguration;
use Contao\Image\PictureInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Filesystem\Path;

#[AsAlias(GalleryFigureFactoryInterface::class)]
final readonly class GalleryFigureFactory implements GalleryFigureFactoryInterface
{
    /**
     * @param array<string> $validImageExtensions
     */
    public function __construct(
        private Studio $studio,
        private LightboxSizeProvider $lightboxSizeProvider,
        private array $validImageExtensions,
        private string $projectDir,
        private string $webDir,
    ) {
    }

    /**
     * @param PictureConfiguration|array<mixed>|int|string|null $size
     *
     * @codeCoverageIgnore
     */
    public function create(GalleryImage $image, PictureConfiguration|array|int|string|null $size, GalleryViewer $galleryViewer = GalleryViewer::None, string|null $lightboxGroupIdentifier = null, string|null $lightboxSize = null): Figure|null
    {
        $lightboxSizeConfiguration = $this->lightboxSizeProvider->getLightboxSize($lightboxSize);

        $builder = $this->studio
            ->createFigureBuilder()
            ->fromUuid($image->uuid)
            ->setSize($size)
        ;

        $builder = match ($galleryViewer) {
            GalleryViewer::Lightbox => $this->configureLightbox(
                $builder,
                $lightboxGroupIdentifier ?? '',
                $lightboxSizeConfiguration,
            ),
            GalleryViewer::Photoswipe => $this->configurePhotoswipe(
                $builder,
                $image,
                $lightboxSizeConfiguration,
            ),
            GalleryViewer::None => $builder,
        };

        return $builder->buildIfResourceExists();
    }

    public function createCoverImage(GalleryImage $image, PictureConfiguration|array|int|string|null $size, string $folderUrl, string|null $alt): Figure|null
    {
        $metadata = null;

        if (null !== $alt) {
            $metadata = new Metadata([Metadata::VALUE_ALT => $alt]);
        }

        return $this->studio
            ->createFigureBuilder()
            ->fromUuid($image->uuid)
            ->setSize($size)
            ->setMetadata($metadata)
            ->setLinkAttribute('href', $folderUrl)
            ->buildIfResourceExists()
        ;
    }

    /**
     * @param array<mixed>|null $lightboxSize
     */
    private function configureLightbox(FigureBuilder $builder, string|null $lightboxGroupIdentifier, array|null $lightboxSize): FigureBuilder
    {
        if (null !== $lightboxGroupIdentifier) {
            $builder->setLightboxGroupIdentifier($lightboxGroupIdentifier);
        }

        return $builder
            ->setLightboxSize($lightboxSize)
            ->enableLightbox()
        ;
    }

    /**
     * @param array<mixed>|null $lightboxSize
     */
    private function configurePhotoswipe(FigureBuilder $builder, GalleryImage $image, array|null $lightboxSize): FigureBuilder
    {
        $photoswipeImage = $this->getPhotoswipeImage($image, $lightboxSize);

        $picture = $photoswipeImage?->getPicture();

        // Use the dimensions of the delivered (resized) image, otherwise PhotoSwipe
        // zooms beyond its actual resolution
        $img = $photoswipeImage?->getImg() ?? [];

        $linkAttributes = [
            'href' => $photoswipeImage?->getImageSrc(),
            'target' => '_blank',
            'class' => 'pswp-link',
            'data-pswp-height' => (string) ($img['height'] ?? ''),
            'data-pswp-width' => (string) ($img['width'] ?? ''),
        ];

        $urls = $this->getAdditionalSourceUrls($picture);

        foreach ($urls as $type => $url) {
            $linkAttributes['data-pswp-'.$type.'-src'] = $url;
        }

        return $builder->setLinkAttributes($linkAttributes);
    }

    /**
     * @param array<mixed>|null $lightboxSize
     */
    private function getPhotoswipeImage(GalleryImage $galleryImage, array|null $lightboxSize): ImageResult|null
    {
        [$filePathOrImage, $url] = $this->resolveImage($galleryImage->path);

        if (null === $filePathOrImage && null === $url) {
            return null;
        }

        return $this->studio
            ->createImage(
                $filePathOrImage,
                $lightboxSize,
            )
        ;
    }

    /**
     * @return array<mixed>
     */
    private function resolveImage(ImageInterface|string $target): array
    {
        if ($target instanceof ImageInterface) {
            return [$target, null];
        }

        $validExtension = \in_array(Path::getExtension($target, true), $this->validImageExtensions, true);
        $externalUrl = 1 === preg_match('#^https?://#', $target);

        if (!$validExtension) {
            return [null, null];
        }

        if ($externalUrl) {
            return [null, $target];
        }

        // Check if target is an absolute filesystem path to an existing resource
        if (Path::isAbsolute($target) && is_file($target)) {
            return [Path::canonicalize($target), null];
        }

        $filePath = urldecode($target);

        // Check if target references a resource relative to the project dir
        $projectPath = Path::join($this->projectDir, $filePath);

        if (is_file($projectPath)) {
            return [$projectPath, null];
        }

        // Check if target references a resource relative to the public dir
        $publicPath = Path::join($this->webDir, $filePath);

        if (is_file($publicPath)) {
            return [null, $target];
        }

        return [null, null];
    }

    /**
     * @return array<string, string>
     */
    private function getAdditionalSourceUrls(PictureInterface|null $picture): array
    {
        $urls = [];

        if (null === $picture) {
            return $urls;
        }

        foreach ($picture->getSources() as $source) {
            if (!\in_array($source['type'] ?? null, ['image/webp', 'image/avif'], true)) {
                continue;
            }

            $url = $source['src']->getUrl($this->projectDir);

            if (null === $url) {
                continue;
            }

            $type = match ($source['type']) {
                'image/avif' => 'avif',
                'image/webp' => 'webp',
            };

            $urls[$type] = '/'.$url;
        }

        return $urls;
    }
}
