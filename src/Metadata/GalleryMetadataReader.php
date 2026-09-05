<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Metadata;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final readonly class GalleryMetadataReader
{
    private const array ALLOWED_KEYS = [
        'title',
        'description',
        'cover',
        'hide_cover_in_gallery',
        'published_from',
        'published_until',
        'sort_order',
        'overview_mode',
    ];

    private const array DATIM_FORMATS = [
        GalleryMetadataManager::DATIM_FORMAT,
        'Y-m-d H:i',
        'd.m.Y H:i',
    ];

    private \DateTimeZone $installationTimezone;

    public function __construct(private LoggerInterface|null $logger = null)
    {
        $this->installationTimezone = $this->getInstallationTimezone();
    }

    public function read(string $directory): GalleryMetadata
    {
        $filename = rtrim($directory, '/').'/'.GalleryMetadata::METADATA_FILE_NAME;

        if (!is_file($filename)) {
            return new GalleryMetadata();
        }

        try {
            $data = Yaml::parseFile($filename);
        } catch (ParseException $exception) {
            $this->logger?->warning(
                \sprintf(
                    'Invalid gallery metadata file "%s": %s',
                    $filename,
                    $exception->getMessage(),
                ),
                [
                    'file' => $filename,
                    'exception' => $exception,
                ],
            );

            return new GalleryMetadata();
        }

        if (!\is_array($data)) {
            $this->logger?->warning(
                \sprintf(
                    'Gallery metadata file "%s" does not contain a YAML object.',
                    $filename,
                ),
                [
                    'file' => $filename,
                    'type' => get_debug_type($data),
                ],
            );

            return new GalleryMetadata();
        }

        $this->logUnknownKeys($filename, $data);

        return new GalleryMetadata(
            title: $this->getString($data, 'title'),
            description: $this->getString($data, 'description'),
            cover: $this->getString($data, 'cover'),
            hideCoverInGallery: $this->getBoolean($data, 'hide_cover_in_gallery'),
            publishedFrom: $this->getDateTime($data, 'published_from'),
            publishedUntil: $this->getDateTime($data, 'published_until'),
            sortOrder: $this->getSortOrder($data),
            overviewMode: $this->getOverviewMode($data),
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function logUnknownKeys(string $filename, array $data): void
    {
        $unknownKeys = array_diff(
            array_keys($data),
            self::ALLOWED_KEYS,
        );

        if ([] === $unknownKeys) {
            return;
        }

        $this->logger?->warning(
            \sprintf(
                'Unknown gallery metadata keys in "%s": %s',
                $filename,
                implode(', ', $unknownKeys),
            ),
            [
                'file' => $filename,
                'unknown_keys' => array_values($unknownKeys),
            ],
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
     * @param array<string, string> $data
     */
    private function getBoolean(array $data, string $key): bool
    {
        $value = $data[$key] ?? null;

        if (null === $value) {
            return false;
        }

        if (\is_bool($value)) {
            return $value;
        }

        $value = !\is_string($value) ? (string) $value : $value;

        return match (strtolower($value)) {
            'true' => true,
            'ja' => true,
            'yes' => true,
            'on' => true,
            '1' => true,
            default => false,
        };
    }

    /**
     * @param array<mixed> $data
     */
    private function getDateTime(array $data, string $key): \DateTimeImmutable|null
    {
        $value = $data[$key] ?? null;

        if (null === $value || '' === $value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return new \DateTimeImmutable('@'.(int) $value, $this->installationTimezone);
            }

            foreach (self::DATIM_FORMATS as $format) {
                $date = \DateTimeImmutable::createFromFormat($format, (string) $value, $this->installationTimezone);

                if (false !== $date) {
                    return $date;
                }
            }

            return null;
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

    private function getInstallationTimezone(): \DateTimeZone
    {
        $tz = date_default_timezone_get();

        try {
            return new \DateTimeZone($tz);
        } catch (\DateInvalidTimeZoneException $exception) {
            $this->logger?->warning(
                \sprintf(
                    'Invalid timezone configuration. Timezone: %s, error: %s',
                    $tz,
                    $exception->getMessage(),
                ),
                [
                    'tz' => $tz,
                    'exception' => $exception,
                ],
            );

            return new \DateTimeZone('UTC');
        }
    }
}
