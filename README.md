# Contao Folder Gallery Bundle

[![](https://img.shields.io/packagist/v/cgoit/contao-folder-gallery-bundle.svg)](https://packagist.org/packages/cgoit/contao-folder-gallery-bundle)
![Dynamic JSON Badge](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FcgoIT%2Fcontao-folder-gallery-bundle%2Fmain%2Fcomposer.json\&query=%24.require%5B%22contao%2Fcore-bundle%22%5D\&label=Contao%20Version)
[![](https://img.shields.io/packagist/dt/cgoit/contao-folder-gallery-bundle.svg)](https://packagist.org/packages/cgoit/contao-folder-gallery-bundle)
[![CI](https://github.com/cgoIT/contao-folder-gallery-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/cgoIT/contao-folder-gallery-bundle/actions/workflows/ci.yml)

## Inhaltsverzeichnis

- [Kurzüberblick](#kurzüberblick)
- [Warum gibt es diese Erweiterung?](#warum-gibt-es-diese-erweiterung)
- [Installation](#installation)
- [Schnellstart (5 Minuten bis zur ersten Galerie)](#schnellstart-5-minuten-bis-zur-ersten-galerie)
- [Designprinzipien](#designprinzipien)
- [Galerie-Struktur](#galerie-struktur)
- [Metadaten (`_metadata.yml`)](#metadaten-_metadatayml)
- [Backend-Konfiguration](#backend-konfiguration)
- [Frontend](#frontend)
- [Sitemap](#sitemap)
- [FAQ](#faq)
- [Mitwirken](#mitwirken)
- [Lizenz](#lizenz)

## Kurzüberblick

Das **Contao Folder Gallery Bundle** verfolgt einen anderen Ansatz als klassische Galerie-Erweiterungen.

Anstatt Galerien im Backend anzulegen und Bilder einzelnen Datensätzen zuzuordnen, nutzt diese Erweiterung die bereits
vorhandene Ordnerstruktur im Dateisystem. Jeder Ordner entspricht genau einer Galerie. Zusätzliche Informationen wie
Titel, Beschreibung oder Veröffentlichungszeiträume werden direkt in einer [`_metadata.yml`](#metadaten-_metadatayml) innerhalb des jeweiligen
Ordners gespeichert.

Dadurch reduziert sich der Pflegeaufwand auf ein Minimum und bestehende Dateistrukturen können ohne zusätzliche
Konfiguration im Backend als vollständige Bildergalerien verwendet werden.

---

## Warum gibt es diese Erweiterung?

Die Idee zu dieser Erweiterung entstand bei der Betreuung der Website eines mehrtägigen Stadtteilfestes.

Während der Veranstaltung entstehen jedes Jahr mehrere tausend Fotos, die von verschiedenen Personen aufgenommen
werden. Die Bilder werden anschließend direkt in die Dateiverwaltung von Contao übernommen – beispielsweise über SFTP
oder andere Synchronisationswerkzeuge – und dort bereits sinnvoll in einer Ordnerstruktur organisiert.

Eine typische Struktur könnte beispielsweise so aussehen:

```text
files/gallery/
├── 2026/
│   ├── Freitag/
│   ├── Samstag/
│   └── Sonntag/
├── 2025/
└── 2024/
```

Mit klassischen Galerie-Erweiterungen beginnt an dieser Stelle jedoch häufig die eigentliche Arbeit: Für jede Galerie
müssen Seiten oder Datensätze angelegt, Bilder ausgewählt, Übersichtsseiten gepflegt und Veröffentlichungszeiträume
konfiguriert werden.

Wiederholt sich dieser Ablauf jedes Jahr, entsteht ein erheblicher Pflegeaufwand – obwohl die eigentliche Struktur
bereits vollständig im Dateisystem vorhanden ist.

Das Contao Folder Gallery Bundle verfolgt deshalb einen anderen Ansatz.

> **Die Ordnerstruktur ist die Galerie.**

Jeder Ordner repräsentiert genau eine Galerie. Das Bundle erzeugt daraus automatisch Übersichten und Galerieansichten.
Zusätzliche Informationen wie Titel, Beschreibung, Coverbild oder Veröffentlichungszeiträume werden direkt in
einer [`_metadata.yml`](#metadaten-_metadatayml) innerhalb des jeweiligen Ordners gespeichert.

Damit entfällt der größte Teil der wiederkehrenden Backend-Konfiguration.

> **Das Dateisystem ist die Quelle der Wahrheit.**
>
> Contao übernimmt die Darstellung der Galerien und ergänzt die vorhandene Ordnerstruktur lediglich um optionale Metadaten.

### Keine zusätzlichen Datenbanktabellen

Das Bundle verzichtet bewusst auf eigene Datenbanktabellen.

Eine Galerie besteht ausschließlich aus

- der vorhandenen Ordnerstruktur innerhalb von `files/`,
- den Bildern selbst,
- sowie optionalen [`_metadata.yml`](#metadaten-_metadatayml)-Dateien.

Die Dateiverwaltung von Contao wird dabei vollständig weiter genutzt. Bilder können wie gewohnt über den
Contao-Dateimanager hochgeladen und verwaltet werden. Ebenso lassen sich bestehende Workflows mit SFTP, rsync
oder anderen Synchronisationswerkzeugen problemlos weiterverwenden.

Es ist daher nicht notwendig, dieselben Informationen sowohl im Dateisystem als auch in einer Datenbank zu pflegen.
Die vorhandene Ordnerstruktur bleibt die Quelle der Wahrheit, während Contao sämtliche Vorteile seiner Bildverarbeitung,
Bildgrößen, Responsive Images und Frontend-Templates weiterhin bereitstellt.

> **Die Erweiterung ersetzt die Dateiverwaltung von Contao nicht – sie baut konsequent auf ihr auf.**
>
> Sämtliche Bilder verbleiben im regulären `files/`-Verzeichnis und können jederzeit sowohl über die Dateiverwaltung
> von Contao als auch mit externen Werkzeugen verwaltet werden.

## Installation

Das Bundle kann wie jede andere Contao-Erweiterung entweder über den **Contao Manager** oder über **Composer** installiert werden.

### Installation mit Composer

```bash
composer require cgoit/contao-folder-gallery-bundle
```

### Installation mit dem Contao Manager

Alternativ kann das Bundle bequem über den Contao Manager installiert werden.

Auch bei einer Installation über den Contao Manager muss anschließend die Datenbank aktualisiert werden.

### Datenbank aktualisieren

Nach der Installation müssen die Datenbankänderungen übernommen werden.

Dies kann entweder

- über den **Contao Manager**,
- auf der Kommandozeile

erfolgen:

```bash
bin/console contao:migrate
```

Dadurch werden die zusätzlichen Felder für das Frontend-Modul angelegt.

> ⚠️ **Wichtig**
>
> Das Bundle legt keine eigenen Datenbanktabellen für Galerien an. Die Datenbankmigration erweitert ausschließlich
> das Frontend-Modul (`tl_module`).

---

## Schnellstart (5 Minuten bis zur ersten Galerie)

In wenigen Minuten zur ersten Galerie:

1. Einen Ordner innerhalb von `files/` anlegen, beispielsweise:

   ```text
   files/gallery/
   └── 2026/
       ├── Freitag/
       ├── Samstag/
       └── Sonntag/
   ```

2. Die gewünschten Bilder in die Ordner hochladen.

3. Ein [Frontend-Modul](#frontend-modul) **Ordner-Galerie** erstellen.

4. Als **Galerie-Wurzel** den gewünschten Ordner (z. B. `files/gallery`) auswählen.

5. Das Frontend-Modul auf einer Seite einbinden.

Fertig. Das Bundle erzeugt daraus automatisch eine [Galerieübersicht](#galerie-übersicht) sowie die einzelnen [Galerieansichten](#galerieansicht).

> 💡 **Tipp**
>
> Eine [`_metadata.yml`](#metadaten-_metadatayml) ist optional. Ohne Metadaten verwendet das Bundle automatisch sinnvolle Standardwerte.
> Metadaten können jederzeit später ergänzt werden.

## Designprinzipien

Das Contao Folder Gallery Bundle wurde nach einigen einfachen Grundprinzipien entwickelt.

### Das Dateisystem ist die Quelle der Wahrheit

Die Galerie existiert bereits durch ihre Ordnerstruktur. Contao ergänzt diese lediglich um optionale [Metadaten](#metadaten-_metadatayml) und stellt
sie im Frontend dar.

### Keine zusätzlichen Datenbanktabellen

Das Bundle speichert weder Galerien noch Metadaten oder Zuordnungen in eigenen Datenbanktabellen. Alle Informationen bleiben
direkt im Dateisystem.

### Bestehende Workflows weiterverwenden

Ob Bilder über die Dateiverwaltung von Contao, per SFTP, rsync oder ein anderes Synchronisationswerkzeug bereitgestellt werden,
spielt keine Rolle. Das Bundle arbeitet mit der vorhandenen Ordnerstruktur und passt sich bestehenden Arbeitsabläufen an.

### Keine Abhängigkeit von der Erweiterung

Die Erweiterung verändert weder die Ordnerstruktur noch die Bilddateien.

Wird das Bundle deinstalliert, bleiben sämtliche Bilder und Metadaten unverändert erhalten. Anschließend können die Galerien
beispielsweise mit der Standard-Galerie von Contao oder einer anderen Galerie-Erweiterung weiterverwendet werden.

Ebenso ist ein schrittweiser Einstieg jederzeit möglich. Einzelne Bereiche einer bestehenden Galerie können nach und nach
auf das Contao Folder Gallery Bundle umgestellt werden, ohne die vorhandene Dateistruktur ändern zu müssen.

## Galerie-Struktur

Das Contao Folder Gallery Bundle erzeugt Galerien direkt aus der Ordnerstruktur innerhalb des `files/`-Verzeichnisses.

Jeder Ordner repräsentiert genau eine Galerie. Er kann entweder als **Galerie** oder abhängig vom
[`overview_mode`](#overview_mode)
als Galeriegruppe dargestellt werden.

Eine typische Struktur könnte beispielsweise so aussehen:

```text
files/
└── galerie/
    ├── 2026/
    │   ├── _metadata.yml
    │   ├── Freitag/
    │   │   ├── _metadata.yml
    │   │   ├── IMG_0001.jpg
    │   │   ├── IMG_0002.jpg
    │   │   └── ...
    │   ├── Samstag/
    │   └── Sonntag/
    ├── 2025/
    └── 2024/
```

In diesem Beispiel stellt der Ordner **2026** eine Galeriegruppe dar. Im Frontend wird zunächst die Überschrift
„2026“ ausgegeben und darunter die Galerien „Freitag“, „Samstag“ und „Sonntag“ angezeigt.

Ob ein Ordner als Galerie oder als Galeriegruppe dargestellt wird, wird in seiner [`_metadata.yml`](#metadaten-_metadatayml) festgelegt.

> 💡 **Hinweis**
>
> Die tatsächlichen Bilddateien bleiben vollständig im Contao-Dateisystem (`files/`). Es werden keine Bilder
> kopiert oder in einer Datenbank gespeichert.

## Metadaten (`_metadata.yml`)

Jeder Galerie-Ordner kann optional eine Datei mit dem Namen `_metadata.yml` enthalten.

Über diese Datei werden alle Informationen gepflegt, die sich nicht direkt aus der Ordnerstruktur ergeben, beispielsweise:

- Titel
- Beschreibung
- Coverbild
- Veröffentlichungszeitraum
- Sortierung
- Darstellungsmodus

Existiert keine `_metadata.yml`, werden sinnvolle Standardwerte verwendet. Beispielsweise wird der Ordnername als
Titel verwendet.

Eine typische Datei könnte beispielsweise so aussehen:

```yaml
title: Stadtfest 2026 - Freitag
description: '<p>Die schönsten Bilder vom Freitagabend.</p>'

cover: IMG_1234.jpg
hide_cover_in_gallery: false

published_from: '2026-09-04T18:00:00+02:00'
published_until: '2027-09-30T23:59:59+02:00'

sort_order: asc
overview_mode: gallery
```

### Unterstützte Felder

| Feld | Beschreibung                                                                                                     |
|------|------------------------------------------------------------------------------------------------------------------|
| `title` | Titel der Galerie oder Galeriegruppe                                                                             |
| `description` | Beschreibung (HTML erlaubt)                                                                                      |
| `cover` | Dateiname des Coverbildes innerhalb des aktuellen Ordners                                                        |
| `hide_cover_in_gallery` | Verwendet das Coverbild ausschließlich als Vorschaubild. Innerhalb der Galerie wird dieses Bild nicht angezeigt. |
| `published_from` | Galerie ist erst ab diesem Zeitpunkt sichtbar                                                                    |
| `published_until` | Galerie ist nur bis zu diesem Zeitpunkt sichtbar                                                                 |
| `sort_order` | Sortierreihenfolge der Unterordner bzw. Bilder in einem Ordner (`asc` oder `desc`)                               |
| `overview_mode` | Legt fest, wie der Ordner in einer Galerieübersicht dargestellt wird (`gallery`, `group` oder `transparent`)     |

> 💡 **Hinweis**
>
> Datums- und Uhrzeitangaben werden im internationalen Standardformat **ISO 8601** gespeichert (z. B.
> `2026-09-04T18:00:00+02:00`).
> Das Format enthält die Zeitzone und kann daher unabhängig von den regionalen Einstellungen oder der
> Serverkonfiguration eindeutig interpretiert werden.

> ℹ️ **Kompatibilität**
>
> Bereits vorhandene Metadatendateien mit Datumsangaben im bisherigen Format (bis einschließlich Version 1.3.0)
> werden weiterhin unterstützt. Beim nächsten Speichern einer Galerie werden die Datumswerte automatisch im
> ISO-8601-Format gespeichert.

### overview_mode

Der Wert `overview_mode` steuert, wie ein Ordner innerhalb der Galerie dargestellt bzw. interpretiert wird.

| Wert          | Bedeutung |
|---------------|-----------|
| `gallery`     | Der Ordner wird als normale Galerie dargestellt. |
| `group`       | Der Ordner dient als Galeriegruppe. Die enthaltenen Unterordner werden als einzelne Galerien angezeigt. |
| `transparent` | Der Ordner wird in der Galerie-Struktur übersprungen. Seine Unterordner werden direkt in die übergeordnete Ebene übernommen. Dies eignet sich beispielsweise für rein organisatorische Zwischenordner. |

> 💡 **Hinweis**
>
> Ordner mit `overview_mode: group` dienen ausschließlich der Strukturierung der Galerie. Bilder, die sich direkt in
> einem solchen Ordner befinden, werden derzeit weder in der Galerie-Übersicht noch in einer Galerieansicht angezeigt.
> Sollen Bilder dargestellt werden, sollten sie in einem Unterordner mit overview_mode: gallery (oder ohne
> explizite Angabe) abgelegt werden.

#### Beispiel für `transparent`

Folgende Ordnerstruktur:

```text
2026/
└── Fotos/
    ├── _metadata.yml
    ├── Freitag/
    ├── Samstag/
    └── Sonntag/
```

mit folgender `_metadata.yml`:

```yaml
overview_mode: transparent
```

führt im Frontend dazu, dass der Ordner **Fotos** nicht angezeigt wird. Stattdessen erscheinen dessen Unterordner
direkt unter **2026**:

```text
2026
├── Freitag
├── Samstag
└── Sonntag
```

Dadurch lassen sich zusätzliche Zwischenordner ausschließlich zur besseren Organisation im Dateisystem verwenden, ohne
dass sie im Frontend sichtbar werden.

### Coverbild nur als Vorschaubild verwenden

In manchen Fällen dient ein Ordner hauptsächlich als Einstieg in weitere Untergalerien. Soll der Ordner dennoch als normale Galerie dargestellt werden, kann ein eigenes Coverbild hinterlegt werden, ohne dass dieses innerhalb der Galerie erscheint.

Dazu kann das Feld

```yaml
hide_cover_in_gallery: true
```

gesetzt werden.

Beispiel:

```text
Produkte/
├── cover.jpg
├── Fahrräder/
├── Roller/
└── Zubehör/
```

```yaml
cover: cover.jpg
hide_cover_in_gallery: true
```

Im Frontend erscheint der Ordner Produkte mit cover.jpg als Vorschaubild.

Beim Öffnen der Galerie wird das Coverbild jedoch nicht angezeigt, obwohl es physisch weiterhin Bestandteil des Ordners ist.
Stattdessen sieht der Besucher direkt die Untergalerien Fahrräder, Roller und Zubehör.

> 💡 **Hinweis**
>
> Im Gegensatz zu `overview_mode: group` bleibt der Ordner eine normale Galerie mit eigener URL und eigenem Galerieeintrag.
> Lediglich das als Coverbild verwendete Bild wird innerhalb der Galerie ausgeblendet.

### Manuelle Bearbeitung oder Backend-Editor

Die `_metadata.yml` kann jederzeit mit einem beliebigen Texteditor bearbeitet oder neu erstellt werden.

Alternativ stellt das Bundle einen komfortablen Backend-Editor zur Verfügung, der dieselben Informationen grafisch
bearbeitet und anschließend wieder in die `_metadata.yml` schreibt.

Beide Wege können beliebig kombiniert werden. Änderungen, die manuell an der Datei vorgenommen werden, sind im
Backend sofort sichtbar. Ebenso können Dateien zunächst manuell angelegt und später bequem über den Backend-Editor
gepflegt werden.

## Backend-Konfiguration

### Frontend-Modul

Die Galerie wird wie jedes andere Contao-Modul über ein Frontend-Modul eingebunden.

Das Frontend-Modul definiert die [Galerie-Wurzel](#galerie-struktur) und steuert die Darstellung der Galerie im Frontend über die folgenden Einstellungen:

| Einstellung                              | Beschreibung |
|------------------------------------------|--------------|
| **Galerie-Wurzel**                       | Wurzelordner der Galerie. Alle Unterordner werden automatisch als Galerie-Struktur interpretiert. |
| **Galeriebildgröße**                     | Bildgröße der Bilder innerhalb einer Galerie. |
| **Coverbildgröße**                       | Bildgröße der Vorschaubilder in der Galerie-Übersicht. |
| **Meldung bei leeren Galerien anzeigen** | Zeigt eine frei definierbare Meldung an, wenn eine veröffentlichte Galerie weder sichtbare Untergalerien noch sichtbare Bilder enthält. |
| **Galerie-Viewer**                       | Legt fest, ob die Bilder mit der Contao-Lightbox oder mit PhotoSwipe geöffnet werden. |
| **Übersichts-Template**                  | Twig-Template für die Darstellung der Galerie-Übersicht. |
| **Galerie-Template**                     | Twig-Template für die Darstellung einer einzelnen Galerie. |

Ein Frontend-Modul definiert gleichzeitig eine **Galerie-Wurzel**. Alle konfigurierten Galerie-Wurzeln werden
automatisch vom Metadaten-Editor erkannt.

Dadurch können auch mehrere unabhängige Galerien innerhalb einer Contao-Installation verwaltet werden.

> 💡 **Hinweis**
>
> Sind keine Frontend-Module mit einer Galerie-Wurzel konfiguriert, stehen im Metadaten-Editor keine Galerien zur Auswahl.

#### Galerie-Viewer

Aktuell werden zwei Viewer unterstützt.

| Viewer | Beschreibung |
|----------|--------------|
| **Contao Lightbox** | Verwendet die klassische Lightbox von Contao. |
| **PhotoSwipe** | Verwendet PhotoSwipe als modernen Galerie-Viewer mit Touch- und Zoom-Unterstützung. |

> 💡 **Wichtig**
>
> Wird **PhotoSwipe** verwendet, muss im Seitenlayout zusätzlich das JavaScript-Template **`js_photoswipe`**
> aktiviert werden.

> 💡 **Wichtig**
>
> Wird die **Contao-Lightbox** verwendet, muss im Seitenlayout **jQuery** aktiviert sowie das jQuery-Template
> **`j_colorbox`** eingebunden werden.

#### Bildunterschriften

Wird **PhotoSwipe** als Galerie-Viewer verwendet, unterstützt das Bundle automatisch Bildunterschriften.

Die Bildunterschrift wird automatisch aus den Bildmetadaten ermittelt, die in der Contao-Dateiverwaltung gepflegt werden.
Dabei gilt folgende Reihenfolge:

1. Existiert innerhalb des Bildes ein `<figcaption>` (Feld "Untertitel" in der Dateiverwaltung von Contao), wird dessen Inhalt verwendet.
2. Andernfalls wird der Inhalt des `alt`-Attributes des Bildes (Feld "Alternativer Text" in der Dateiverwaltung von Contao) verwendet.

Dadurch können Bildunterschriften bequem über die Dateiattribute (Metadaten) von Contao gepflegt werden, ohne dass zusätzliche
Felder innerhalb der Galerie erforderlich sind.

Da `figcaption` HTML-Inhalte unterstützt, können Bildunterschriften neben reinem Text beispielsweise auch Links,
Hervorhebungen oder andere Formatierungen enthalten.

> 💡 **Hinweis**
>
> Die Contao-Lightbox unterstützt diese Funktion derzeit nicht. Bildunterschriften stehen ausschließlich bei Verwendung
> von **PhotoSwipe** zur Verfügung.

### Metadaten-Editor

Der Metadaten-Editor dient zur komfortablen Bearbeitung der [`_metadata.yml`-Dateien](#metadaten-_metadatayml).

Auf der linken Seite wird die komplette Galerie-Struktur aller konfigurierten Galerie-Wurzeln als Baum dargestellt.

Nach Auswahl eines Ordners werden auf der rechten Seite dessen Metadaten angezeigt und können direkt bearbeitet werden.

Alle Änderungen werden unmittelbar wieder in die entsprechende [`_metadata.yml`](#metadaten-_metadatayml) geschrieben. Dadurch können der
Backend-Editor und eine manuelle Bearbeitung der Dateien jederzeit beliebig miteinander kombiniert werden.

> 💡 **Hinweis**
>
> Aus Gründen der Datenkonsistenz kann als Coverbild ausschließlich eine Datei aus dem jeweiligen Galerieordner
> verwendet werden.

## Frontend

### Routing

Für die Darstellung der Galerien genügt **eine einzige Contao-Seite** mit einem eingebundenen **Folder Gallery**-Frontend-Modul. Je nach URL zeigt das Modul automatisch entweder die Galerie-Übersicht oder die entsprechende Galerie an.

Die Erweiterung erzeugt keine eigenen Seiten und es müssen auch keine einzelnen Galerieseiten im Seitenbaum angelegt
werden. Stattdessen wird die URL automatisch anhand der Ordnerstruktur im Dateisystem ausgewertet.

Aus der folgenden Ordnerstruktur

```text
files/
└── galerie/
    ├── 2026/
    │   ├── Freitag/
    │   ├── Samstag/
    │   └── Sonntag/
    └── 2025/
```

ergeben sich beispielsweise automatisch folgende URLs:

```text
/galerie/
/galerie/2026/freitag
/galerie/2026/samstag
/galerie/2026/sonntag
/galerie/2025
```

Es sind keinerlei zusätzliche Seiten oder Weiterleitungen erforderlich.

> 💡 **Hinweis**
>
> Die URLs werden vollständig aus der Ordnerstruktur abgeleitet. Wird ein Ordner umbenannt oder verschoben,
> ändert sich automatisch auch die entsprechende URL.

### Galerie-Übersicht

Wird die Galerie-Wurzel aufgerufen, erzeugt die Erweiterung automatisch eine Galerie-Übersicht.

Für jeden sichtbaren Ordner werden – abhängig von den Metadaten – unter anderem folgende Informationen dargestellt:

- Coverbild
- Titel
- Beschreibung
- Link zur Galerie

Ist ein Ordner als [`group`](#overview_mode) konfiguriert, wird dieser als Überschrift dargestellt und seine Unterordner werden
darunter gruppiert angezeigt.

Ordner mit [`overview_mode: transparent`](#overview_mode) erscheinen dagegen nicht in der Übersicht. Stattdessen werden deren Unterordner
direkt in die übergeordnete Ebene übernommen.

### Galerieansicht

Beim Aufruf einer Galerie werden automatisch alle Bilder des entsprechenden Ordners dargestellt.

**Die Bilder werden dabei vollständig über die Bildpipeline von Contao erzeugt (siehe auch [Bildgrößen](#bildgrößen)).**
Dadurch stehen automatisch sämtliche Funktionen von Contao wie responsive Bilder, Bildgrößen und verschiedene
Ausgabeformate (z. B. WebP oder AVIF) ohne zusätzliche Konfiguration zur Verfügung.

Enthält eine Galerie weitere Unterordner, werden diese oberhalb der Bilder ebenfalls angezeigt und können direkt
geöffnet werden. Dadurch lassen sich beliebig tiefe Galerie-Strukturen aufbauen.

### Leere Galerien

Manchmal werden Galerien bereits veröffentlicht, obwohl die eigentlichen Bilder erst zu einem späteren Zeitpunkt hochgeladen
werden. Dies kommt beispielsweise bei Veranstaltungen vor, wenn Fotografen ihre Bilder zeitversetzt bereitstellen.

Für diesen Fall kann im Frontend-Modul optional eine Meldung konfiguriert werden.

Wird die Option aktiviert, erscheint diese Meldung automatisch, wenn eine Galerie

- veröffentlicht ist,
- keine sichtbaren Untergalerien besitzt und
- keine sichtbaren Bilder enthält.

Dadurch können Besucher beispielsweise darüber informiert werden, dass die Bilder in Kürze veröffentlicht werden.

> 💡 **Hinweis**
>
> Bilder, die ausschließlich als Coverbild verwendet werden (`hide_cover_in_gallery: true`), gelten hierbei nicht
> als sichtbare Bilder.

### Anpassung der Darstellung

Das Bundle orientiert sich bewusst an den bestehenden Mechanismen von Contao und lässt sich an vielen Stellen erweitern.

Unter anderem lassen sich

- eigene Twig-Templates verwenden,
- eigene CSS-Regeln ergänzen,
- Bildgrößen aus Contao nutzen,
- der Galerie-Viewer wählen,
- die Darstellung vollständig an das eigene Theme anpassen.

Da sämtliche Bilder über die Contao-Bildpipeline erzeugt werden, profitieren auch individuelle Anpassungen automatisch
von den Bildformaten und Optimierungen des Contao-Kerns.

#### Twig-Templates

Alle Templates können wie gewohnt über das Contao-Template-System überschrieben werden.

##### Frontend-Modul

| Template | Beschreibung |
|-----------|--------------|
| `frontend_module/folder_gallery.html.twig` | Einstiegspunkt des Frontend-Moduls. Entscheidet automatisch, ob die Galerie-Übersicht oder eine einzelne Galerie dargestellt wird. |

##### Komponenten

| Template | Beschreibung |
|-----------|--------------|
| `component/gallery_folder.html.twig` | Darstellung eines Galerie-Ordners innerhalb der Übersicht. Das Template wird rekursiv für alle Unterordner verwendet. |
| `component/gallery_content.html.twig` | Darstellung einer einzelnen Galerie mit Beschreibung, Unterordnern und Bildern. |

> 💡 **Hinweis**
>
> Änderungen an `gallery_folder.html.twig` wirken sich automatisch auf alle Ebenen der Galerie-Struktur aus.

#### CSS-Variablen

Das Standard-Stylesheet verwendet CSS-Variablen, um die wichtigsten Layout- und Designparameter einfach anpassen zu können.

Alle Variablen werden innerhalb der Klasse `.module-folder-gallery` definiert und können problemlos im eigenen Theme überschrieben werden.

##### Abstände

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-spacing` | `1rem` | Standardabstand innerhalb der Galerie. |
| `--gallery-section-spacing` | `2rem` | Abstand zwischen größeren Bereichen (z. B. Beschreibung, Unterordner und Bilder). |

##### Galerie-Übersicht

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-overview-column-width` | `300px` | Mindestbreite einer Kachel in der Galerie-Übersicht. |
| `--gallery-overview-gap` | `1.5rem` | Abstand zwischen den Kacheln der Übersicht. |

##### Galerie

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-content-column-width` | `200px` | Mindestbreite der Bilder innerhalb einer Galerie. |
| `--gallery-content-gap` | `1rem` | Abstand zwischen den Bildern. |

##### Karten

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-card-background` | `transparent` | Hintergrund einer Kartenansicht in der Galerie-Übersicht. |
| `--gallery-card-border` | `none` | Rahmen einer Karte. |
| `--gallery-card-border-radius` | `var(--gallery-border-radius)` | Abrundung der Karte. |
| `--gallery-card-padding` | `0` | Innenabstand des Inhaltsbereichs einer Karte. |
| `--gallery-card-gap` | `0.5rem` | Abstand zwischen Vorschaubild und Inhaltsbereich einer Karte. |
| `--gallery-card-shadow` | `none` | Standardschatten einer Karte. |
| `--gallery-card-shadow-hover` | `none` | Schatten einer Karte beim Überfahren mit der Maus. |

##### Bilder

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-border-radius` | `0.5rem` | Abrundung der Vorschaubilder. |
| `--gallery-image-aspect-ratio` | `1` | Seitenverhältnis der Vorschaubilder (z. B. `1`, `4 / 3` oder `16 / 9`). |

##### Typografie

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-title-size` | `clamp(0.9rem, 1.2vw + 0.5rem, 1.5rem)` | Schriftgröße des Galerietitels. |
| `--gallery-title-weight` | `600` | Schriftstärke des Galerietitels. |
| `--gallery-meta-size` | `clamp(0.65rem, 1.1vw + 0.5rem, 1rem)` | Schriftgröße der Metadaten (z. B. Anzahl der Bilder). |
| `--gallery-meta-weight` | `400` | Schriftstärke der Metadaten. |
| `--gallery-meta-color` | `#666` | Textfarbe der Metadaten. |

##### Hover-Effekte

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-hover-scale` | `1.04` | Vergrößerung des Bildes beim Überfahren mit der Maus. |
| `--gallery-hover-brightness` | `0.95` | Helligkeit des Bildes beim Hover-Effekt. |
| `--gallery-hover-translate-y` | `0` | Vertikale Verschiebung einer Karte beim Hover-Effekt. |
| `--gallery-transition-duration` | `0.2s` | Dauer der Hover-Animationen. |

##### PhotoSwipe

Die Darstellung der Bildunterschriften von PhotoSwipe kann vollständig über CSS-Variablen angepasst werden.

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--pswp-caption-width` | `min(90%, 50rem)` | Breite der Bildunterschrift. |
| `--pswp-caption-max-width` | `calc(100vw - 3rem)` | Maximale Breite der Bildunterschrift. |
| `--pswp-caption-bottom` | `2.5rem` | Abstand zum unteren Fensterrand. |
| `--pswp-caption-padding` | `1rem 2rem` | Innenabstand der Bildunterschrift. |
| `--pswp-caption-background` | `rgba(25,25,25,.55)` | Hintergrundfarbe. |
| `--pswp-caption-border` | `none` | Rahmen der Bildunterschrift. |
| `--pswp-caption-radius` | `.75rem` | Abrundung der Bildunterschrift. |
| `--pswp-caption-backdrop-filter` | `blur(10px)` | Hintergrundunschärfe. |
| `--pswp-caption-box-shadow` | `0 .5rem 2rem rgba(0,0,0,.35)` | Schatten der Bildunterschrift. |
| `--pswp-caption-text-color` | `#fff` | Textfarbe. |
| `--pswp-caption-text-align` | `left` | Textausrichtung. |
| `--pswp-caption-text-wrap` | `balance` | Optimierter Zeilenumbruch für längere Texte. |
| `--pswp-caption-line-height` | `1.6` | Zeilenhöhe. |
| `--pswp-caption-transition-duration` | `.2s` | Dauer der Ein- und Ausblendanimation. |
| `--pswp-caption-transition-timing-function` | `ease` | Timing-Funktion der Animation. |

#### Bildgrößen

Das Bundle verwendet ausschließlich die in Contao konfigurierten Bildgrößen.

Im [Frontend-Modul](#frontend-modul) können unabhängig voneinander Bildgrößen für

- Coverbilder der Galerie-Übersicht
- Bilder innerhalb einer Galerie

ausgewählt werden.

Dadurch stehen sämtliche Funktionen der Contao-Bildpipeline wie responsive Bilder, verschiedene Ausgabeformate und
Bildzuschnitte automatisch zur Verfügung.

## Sitemap

Das Bundle integriert sich automatisch in die von Contao erzeugte `sitemap.xml`.

Alle über das **Folder Gallery**-Frontend-Modul erreichbaren Galerien werden automatisch als zusätzliche
Sitemap-Einträge aufgenommen. Dabei werden sämtliche sichtbaren Galerie-URLs berücksichtigt – unabhängig davon,
wie tief sie innerhalb der Ordnerstruktur verschachtelt sind.

Aus der folgenden Ordnerstruktur

```text
files/
└── galerie/
    ├── 2026/
    │   ├── Freitag/
    │   ├── Samstag/
    │   └── Sonntag/
    └── 2025/
```

werden beispielsweise automatisch folgende zusätzliche Sitemap-Einträge erzeugt:

```text
https://your-website-domain.com/galerie/2026
https://your-website-domain.com/galerie/2026/freitag
https://your-website-domain.com/galerie/2026/samstag
https://your-website-domain.com/galerie/2026/sonntag
https://your-website-domain.com/galerie/2025
```

Dadurch können Suchmaschinen sämtliche Galerien ohne weitere Konfiguration finden und indexieren.

> 💡 **Hinweis**
>
> Es ist keine zusätzliche Konfiguration erforderlich. Die Erweiterung ergänzt die von Contao erzeugte Sitemap
> automatisch.


## FAQ

### Im Metadaten-Editor werden keine Galerien angezeigt.

Prüfen Sie, ob mindestens ein [Frontend-Modul](#frontend-modul) vom Typ **Folder Gallery** konfiguriert wurde und eine Galerie-Wurzel ausgewählt ist.

Der [Metadaten-Editor](#metadaten-editor) ermittelt seine Galerie-Struktur ausschließlich aus den konfigurierten Frontend-Modulen.

### Meine Galerie wird im Frontend nicht angezeigt.

Prüfen Sie insbesondere folgende Punkte:

- Existiert die Galerie innerhalb der konfigurierten [Galerie-Wurzel](#frontend-modul)?
- Befindet sich die Galerie innerhalb eines veröffentlichten Zeitraums (Liegt der aktuelle Zeitpunkt innerhalb von `published_from` und `published_until`)?
- Ist der Ordner nicht versehentlich auf `overview_mode: transparent` gesetzt?

### PhotoSwipe bzw. die Lightbox öffnet sich nicht.

Prüfen Sie die [Konfiguration des Seitenlayouts](#galerie-viewer).

- Für **PhotoSwipe** muss das JavaScript-Template `js_photoswipe` aktiviert sein.
- Für die **Contao-Lightbox** müssen **jQuery** sowie das Template `j_colorbox` aktiviert sein.

### Kann ich ein eigenes Coverbild verwenden, das in der Galerie selbst nicht angezeigt wird?

Mit

```yaml
hide_cover_in_gallery: true
```
wird das ausgewählte Coverbild ausschließlich als Vorschaubild der Galerie verwendet. Innerhalb der Galerie selbst wird dieses Bild ausgeblendet.

Dies eignet sich insbesondere für Galerien, die hauptsächlich weitere Untergalerien enthalten und dennoch mit einem eigenen Vorschaubild dargestellt werden sollen.

### Meine Galerie ist sichtbar, aber es werden keine Bilder angezeigt.

Wenn im Frontend-Modul die Option Meldung bei leeren Galerien anzeigen aktiviert wurde, erscheint die konfigurierte Meldung
automatisch, sobald eine veröffentlichte Galerie weder sichtbare Bilder noch sichtbare Untergalerien enthält.

Dies eignet sich insbesondere für Galerien, deren Bilder erst zu einem späteren Zeitpunkt hochgeladen werden.

### Werden die Bilder in einer Datenbank gespeichert?

Nein.

Das Bundle arbeitet ausschließlich mit den Dateien innerhalb des `files/`-Verzeichnisses. Die Datenbank enthält lediglich
die Konfiguration des [Frontend-Moduls](#frontend-modul).

### Wo werden Bildunterschriften gepflegt?

Bei Verwendung von **PhotoSwipe** werden Bildunterschriften automatisch aus den Bildinformationen übernommen.

Existiert ein Untertitel (`figcaption`), wird dieser verwendet. Andernfalls verwendet das Bundle den Alternativtext (alt).

Dadurch können Bildunterschriften direkt über die Dateiattribute in Contao gepflegt werden, ohne dass zusätzliche Felder
innerhalb der Galerie erforderlich sind.

### Muss ich die `_metadata.yml` manuell bearbeiten?

Nein.

Die Metadaten können sowohl direkt in der [`_metadata.yml`](#metadaten-_metadatayml) als auch über den integrierten [Metadaten-Editor](#metadaten-editor) gepflegt
werden. Beide Arbeitsweisen können beliebig kombiniert werden.

### Werden Galerien automatisch in die Sitemap aufgenommen?

Ja.

Alle über ein **Folder Gallery**-Frontend-Modul erreichbaren Galerien werden automatisch in die von Contao
erzeugte `sitemap.xml` aufgenommen. Eine zusätzliche Konfiguration ist nicht erforderlich.

### Kann ich die Erweiterung später wieder entfernen?

Ja.

Die Erweiterung speichert sämtliche Informationen direkt im Dateisystem und ergänzt lediglich einige
Konfigurationsfelder im [Frontend-Modul](#frontend-modul).

Die eigentlichen Bilder und Ordner bleiben unverändert erhalten und können anschließend problemlos mit der
Contao-Standardgalerie oder einer anderen Galerie-Erweiterung weiterverwendet werden.

## Mitwirken

Fehlerberichte, Verbesserungsvorschläge und Pull Requests über GitHub sind jederzeit willkommen.

Falls Sie Fragen oder Ideen zur Erweiterung haben, freuen wir uns über ein Issue oder eine Diskussion auf GitHub.

## Lizenz

Dieses Bundle steht unter der **LGPL-3.0-or-later**.

Weitere Informationen finden Sie in der Datei `LICENSE`.
