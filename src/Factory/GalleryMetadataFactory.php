<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Factory;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Contao\StringUtil;

final readonly class GalleryMetadataFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, \DateTimeZone|null $dateTimeZone = null): GalleryMetadata
    {
        $publishedFrom = null;

        if (!empty($data['publishedFrom'])) {
            $publishedFrom = new \DateTimeImmutable(
                (string) $data['publishedFrom'],
                $dateTimeZone,
            );
        }

        $publishedUntil = null;

        if (!empty($data['publishedUntil'])) {
            $publishedUntil = new \DateTimeImmutable(
                (string) $data['publishedUntil'],
                $dateTimeZone,
            );
        }

        return new GalleryMetadata(
            title: $this->normalizeString($data['title'] ?? null),
            description: $this->normalizeHtml($data['description'] ?? null),
            cover: $this->normalizeString($data['cover'] ?? null),
            hideCoverInGallery: $this->normalizeBool($data['hideCoverInGallery'] ?? null),
            publishedFrom: $publishedFrom,
            publishedUntil: $publishedUntil,
            sortOrder: !empty($data['sortOrder'])
                ? SortOrder::from((string) $data['sortOrder'])
                : SortOrder::Asc,
            overviewMode: !empty($data['overviewMode'])
                ? OverviewMode::from((string) $data['overviewMode'])
                : OverviewMode::Gallery,
        );
    }

    private function normalizeString(mixed $value): string|null
    {
        if (!\is_string($value) || '' === trim($value)) {
            return null;
        }

        return $value;
    }

    private function normalizeHtml(mixed $value): string|null
    {
        $value = $this->normalizeString($value);

        return null !== $value
            ? StringUtil::decodeEntities($value)
            : null;
    }

    private function normalizeBool(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        $value = $this->normalizeString($value);

        if (null === $value) {
            return false;
        }

        return '1' === $value;
    }
}
