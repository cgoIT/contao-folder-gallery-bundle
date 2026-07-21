<?php

declare(strict_types=1);

namespace Cgoit\ContaoFolderGalleryBundle\Metadata;

use Cgoit\ContaoFolderGalleryBundle\Model\GalleryMetadata;
use Cgoit\ContaoFolderGalleryBundle\Model\OverviewMode;
use Cgoit\ContaoFolderGalleryBundle\Model\SortOrder;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final readonly class GalleryMetadataReader
{
    private const array ALLOWED_KEYS = [
        'title',
        'description',
        'cover',
        'published_from',
        'published_until',
        'sort_order',
        'overview_mode',
    ];

    private \DateTimeZone|null $tz;

    private string $datimFormat;

    public function __construct(
        private ContaoFramework $framework,
        private LoggerInterface|null $logger = null,
    ) {
        $config = $this->framework->getAdapter(Config::class);
        $this->tz = $config->get('timeZone') ? new \DateTimeZone($config->get('timeZone')) : null;
        $this->datimFormat = $config->get('datimFormat') ?? GalleryMetadataManager::DATIM_FORMAT;
    }

    public function read(string $directory): GalleryMetadata
    {
        $filename = rtrim($directory, '/').GalleryMetadata::METADATA_FILE_NAME;

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
     * @param array<mixed> $data
     */
    private function getDateTime(array $data, string $key): \DateTimeImmutable|null
    {
        $value = $data[$key] ?? null;

        if (null === $value || '' === $value) {
            return null;
        }

        try {
            if (\is_int($value) || \is_float($value) || ctype_digit((string) $value)) {
                $date = new \DateTimeImmutable('@'.(int) $value);

                return $this->tz ? $date->setTimezone($this->tz) : $date;
            }

            return \DateTimeImmutable::createFromFormat($this->datimFormat, (string) $value, $this->tz);
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
