<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Loader;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class MetadataLoader
{
    public function read(string $directory): GalleryMetadata
    {
        $filename = rtrim($directory, '/').'/_metadata.yml';

        if (!is_file($filename)) {
            return new GalleryMetadata();
        }

        try {
            $data = Yaml::parseFile($filename);
        } catch (ParseException) {
            return new GalleryMetadata();
        }

        if (!\is_array($data)) {
            return new GalleryMetadata();
        }

        return new GalleryMetadata(
            title: $this->getString($data, 'title'),
            description: $this->getString($data, 'description'),
            cover: $this->getString($data, 'cover'),
            publishedFrom: $this->getDateTime($data, 'published_from'),
            publishedUntil: $this->getDateTime($data, 'published_until'),
            sortOrder: $this->getSortOrder($data),
            hiddenInOverview: $this->getBool($data, 'hidden_in_overview'),
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function getString(array $data, string $key): string|null
    {
        $value = $data[$key] ?? null;

        return \is_string($value) && '' !== trim($value)
            ? $value
            : null;
    }

    /**
     * @param array<mixed> $data
     */
    private function getBool(array $data, string $key, bool $default = false): bool
    {
        if (!\array_key_exists($key, $data)) {
            return $default;
        }

        $value = filter_var(
            $data[$key],
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        );

        return $value ?? $default;
    }

    /**
     * @param array<mixed> $data
     */
    private function getDateTime(array $data, string $key): \DateTimeImmutable|null
    {
        $value = $data[$key] ?? null;

        if (empty($value)) {
            return null;
        }

        try {
            return new \DateTimeImmutable('@'.$value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<mixed> $data
     */
    private function getSortOrder(array $data): SortOrder
    {
        $sortOrder = $data['sort_order'] ?? null;

        if (!\is_string($sortOrder) || '' === trim($sortOrder)) {
            return SortOrder::Asc;
        }

        return SortOrder::tryFrom(strtolower(trim($sortOrder))) ?? SortOrder::Asc;
    }

    /**
     * @param array<mixed> $data
     */
    private function getOverviewMode(array $data): OverviewMode
    {
        $overviewMode = $data['overview_mode'] ?? null;

        if (!\is_string($overviewMode) || '' === trim($overviewMode)) {
            return OverviewMode::Gallery;
        }

        return OverviewMode::tryFrom(strtolower(trim($overviewMode))) ?? OverviewMode::Gallery;
    }
}
