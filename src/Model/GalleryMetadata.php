<?php

declare(strict_types=1);

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

namespace Cgoit\ContaoFolderGalleryBundle\Model;

use Contao\Input;

final readonly class GalleryMetadata
{
    public function __construct(
        public string|null $title = null,
        public string|null $description = null,
        public string|null $cover = null,
        public \DateTimeImmutable|null $publishedFrom = null,
        public \DateTimeImmutable|null $publishedUntil = null,
        public SortOrder $sortOrder = SortOrder::Asc,
        public OverviewMode $overviewMode = OverviewMode::Gallery,
    ) {
    }

    public function isPublished(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        if (
            $this->publishedFrom instanceof \DateTimeImmutable
            && $now < $this->publishedFrom
        ) {
            return false;
        }

        if (
            $this->publishedUntil instanceof \DateTimeImmutable
            && $now > $this->publishedUntil
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCurrentRecord(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'cover' => $this->cover,
            'publishedFrom' => $this->publishedFrom?->getTimestamp(),
            'publishedUntil' => $this->publishedUntil?->getTimestamp(),
            'sortOrder' => $this->sortOrder->value,
            'overviewMode' => $this->overviewMode->value,
        ];
    }

    public function mergeWithInput(\DateTimeZone $dateTimeZone = null)
    {
        $publishedFrom = Input::post('publishedFrom') ? new \DateTimeImmutable(Input::post('publishedFrom'), $dateTimeZone) : null;
        $publishedUntil = Input::post('publishedUntil') ? new \DateTimeImmutable(Input::post('publishedUntil'), $dateTimeZone) : null;
        $sortOrder = Input::post('sortOrder') ? SortOrder::from(Input::post('sortOrder')) : SortOrder::Asc;
        $overviewMode = Input::post('overviewMode') ? OverviewMode::from(Input::post('overviewMode')) : OverviewMode::Gallery;

        return new GalleryMetadata(
            title: Input::post('title'),
            description: Input::post('description'),
            cover: Input::post('cover'),
            publishedFrom: $publishedFrom,
            publishedUntil: $publishedUntil,
            sortOrder: $sortOrder,
            overviewMode: $overviewMode,
        );
    }
}
