<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryImage;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryViewer;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\PageFinder;
use Contao\Image\ImageInterface;
use Contao\Image\PictureConfiguration;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\StringUtil;
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
        private PageFinder $pageFinder,
        private ContaoFramework $framework,
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
    public function create(GalleryImage $image, PictureConfiguration|array|int|string|null $size, GalleryViewer $galleryViewer = GalleryViewer::None, string|null $lightboxGroupIdentifier = null): Figure|null
    {
        $builder = $this->studio
            ->createFigureBuilder()
            ->fromUuid($image->uuid)
            ->setSize($size)
        ;

        if (GalleryViewer::Lightbox === $galleryViewer) {
            $builder = $builder
                ->setLightboxGroupIdentifier($lightboxGroupIdentifier)
                ->enableLightbox()
            ;
        } elseif (GalleryViewer::Photoswipe) {
            $builder = $builder
                ->setLinkAttributes([
                    'href' => $this->getPhotoswipeUrl($image),
                    'data-pspw-height' => '1024px',
                ])
            ;
        }

        return $builder->buildIfResourceExists();
    }

    private function getPhotoswipeUrl(GalleryImage $galleryImage): string|null
    {
        $getResourceOrUrl = function (ImageInterface|string $target): array {
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
        };

        [$filePathOrImage, $url] = $getResourceOrUrl($galleryImage->path);

        if (null === $filePathOrImage && null === $url) {
            return null;
        }

        return $this->studio
            ->createImage(
                $filePathOrImage,
                $this->getDefaultLightboxSizeConfiguration(),
            )
            ->getImageSrc()
        ;
    }

    /**
     * @return array<mixed>|null
     */
    private function getDefaultLightboxSizeConfiguration(): array|null
    {
        $page = $this->pageFinder->getCurrentPage();

        if (!$page instanceof PageModel || null === $page->layout) {
            return null;
        }

        $layoutModel = $this->framework->getAdapter(LayoutModel::class)->findById($page->layout);

        if (!$layoutModel || empty($layoutModel->lightboxSize)) {
            return null;
        }

        return StringUtil::deserialize($layoutModel->lightboxSize, true);
    }
}
