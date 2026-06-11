# Contao Folder Gallery Bundle

[![](https://img.shields.io/packagist/v/cgoit/contao-folder-gallery-bundle.svg)](https://packagist.org/packages/cgoit/contao-folder-gallery-bundle)
![Dynamic JSON Badge](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FcgoIT%2Fcontao-folder-gallery-bundle%2Fmain%2Fcomposer.json\&query=%24.require%5B%22contao%2Fcore-bundle%22%5D\&label=Contao%20Version)
[![](https://img.shields.io/packagist/dt/cgoit/contao-folder-gallery-bundle.svg)](https://packagist.org/packages/cgoit/contao-folder-gallery-bundle)
[![CI](https://github.com/cgoIT/contao-folder-gallery-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/cgoIT/contao-folder-gallery-bundle/actions/workflows/ci.yml)

A flexible folder-based gallery bundle for Contao CMS that builds image galleries directly from the file system using YAML metadata and Contao's image pipeline.

## Features

* Build galleries directly from folders in the Contao file manager
* Organize galleries using a simple folder structure
* Support for gallery metadata via YAML files
* Responsive images using Contao's native image pipeline
* Gallery overview and gallery reader views
* Fully customizable frontend templates
* Support for custom image sizes
* Designed for large photo collections
* No additional database tables required

## Folder Structure

A typical gallery structure could look like this:

```text
files/gallery/
├── 2025/
│   ├── _metadata.yml
│   ├── friday/
│   │   ├── _metadata.yml
│   │   ├── image01.jpg
│   │   └── image02.jpg
│   ├── saturday/
│   └── sunday/
├── 2024/
└── 2023/
```

## Metadata

Metadata can be stored in `_metadata.yml` files.

Example:

```yaml
title: Friday, September 5th 2025
cover: image01.jpg
published_from: 2025-09-05 23:00:00
description: Pictures from the opening day of the festival.
```

## Frontend Modules

Currently the bundle provides the following frontend modules:

| Name           | Description                                                                  |
| -------------- | ---------------------------------------------------------------------------- |
| Folder Gallery | Displays gallery overviews and gallery reader pages based on the current URL |

## Install

```bash
composer require cgoit/contao-folder-gallery-bundle
```

## Requirements

* Contao 5.3 or newer
* PHP 8.3 or newer

## License

LGPL-3.0-or-later
