<?php

/*
 * This file is part of cgoit\contao-folder-gallery-bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) cgoIT
 * @author     cgoIT <https://cgo-it.de>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['XPL']['folderGalleryOverviewMode'] = [
    ['Galerie', 'Der Ordner wird als normale Galerie dargestellt.'],
    ['Gruppieren', 'Der Ordner dient als Galeriegruppe. Dazu wird der Titel des Ordners im Frontend als einfache Überschrift dargestellt. Die enthaltenen Unterordner werden darunter als einzelne Galerien angezeigt.'],
    ['Transparent', 'Der Ordner wird in der Galerie-Struktur übersprungen. Seine Unterordner werden direkt in die übergeordnete Ebene übernommen. Dies eignet sich beispielsweise für rein organisatorische Zwischenordner.'],
];

$GLOBALS['TL_LANG']['XPL']['folderGalleryHideCoverInGallery'] = [
    ['Deaktiviert (Standard)', 'Das Coverbild wird sowohl als Vorschaubild der Galerie als auch innerhalb der Galerie angezeigt.'],
    ['Aktiviert', 'Das ausgewählte Coverbild wird ausschließlich als Vorschaubild der Galerie verwendet und innerhalb der Galerie nicht angezeigt. Dadurch kann ein Ordner als Galerie mit eigenem Coverbild dienen und gleichzeitig weitere Untergalerien enthalten.'],
    ['Typischer Anwendungsfall', 'Ein Ordner enthält ausschließlich ein Coverbild sowie weitere Untergalerien. Das Coverbild erscheint in der Galerieübersicht, beim Öffnen der Galerie werden jedoch nur die Untergalerien angezeigt.'],
];
