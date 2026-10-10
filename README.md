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
- [Erweiterbarkeit](#erweiterbarkeit)
- [Sitemap](#sitemap)
- [Datenschutz und Einwilligungen](#datenschutz-und-einwilligungen)
- [FAQ](#faq)
- [Diese Erweiterung im Einsatz](#diese-erweiterung-im-einsatz)
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

Dadurch werden die zusätzlichen Felder für das Frontend-Modul sowie das Feld zum Ausblenden einzelner Bilder in der
Dateiverwaltung angelegt.

> ⚠️ **Wichtig**
>
> Das Bundle legt keine eigenen Datenbanktabellen für Galerien an. Die Datenbankmigration erweitert lediglich das
> Frontend-Modul (`tl_module`) sowie die Dateiverwaltung (`tl_files`) um ein einzelnes Sichtbarkeits-Feld (siehe
> [Einzelne Bilder aus der Galerie ausblenden](#einzelne-bilder-aus-der-galerie-ausblenden)).

### Update von Version 1.10.1 oder älter

Bis einschließlich Version 1.10.1 hat der [Metadaten-Editor](#metadaten-editor) die `_metadata.yml` von
**geschützten** Galerie-Ordnern versehentlich unterhalb von `public/files/` statt im eigentlichen Ordner unter
`files/` gespeichert. Öffentliche Ordner waren davon nicht betroffen.

Beim nächsten Aufruf von `contao:migrate` werden solche Dateien automatisch in den richtigen Galerie-Ordner
verschoben, und die nur dafür angelegten Verzeichnisse unter `public/files/` werden entfernt.

Existiert im Galerie-Ordner bereits eine abweichende `_metadata.yml`, bleibt diese unverändert. Die verschobene
Datei wird dann als `_metadata.orphaned.yml` daneben abgelegt und die Migration nennt die betroffenen Ordner. Die
Inhalte können anschließend von Hand zusammengeführt werden; danach kann die `_metadata.orphaned.yml` gelöscht
werden.

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

> 💡 **Ausnahme**
>
> Ob ein einzelnes Bild in der Galerie ausgeblendet werden soll, wird direkt am jeweiligen Bild in der
> Contao-Dateiverwaltung (`tl_files`) gepflegt (siehe [Einzelne Bilder aus der Galerie ausblenden](#einzelne-bilder-aus-der-galerie-ausblenden)).
> Diese Information lässt sich nicht sinnvoll in der ordnerbezogenen [`_metadata.yml`](#metadaten-_metadatayml) abbilden,
> da sie sich auf ein einzelnes Bild und nicht auf den gesamten Ordner bezieht. Es wird dafür aber keine eigene
> Datenbanktabelle angelegt, sondern lediglich ein zusätzliches Feld der ohnehin vorhandenen Dateiverwaltung genutzt.

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

### Öffentliche und geschützte Ordner

Die Galerie-Wurzel kann sowohl ein öffentlicher als auch ein geschützter Ordner sein.

Bei geschützten Ordnern sind die Originalbilder nicht direkt über das Web erreichbar. Die Vorschau- und Galeriebilder
erzeugt Contao über die Bildpipeline unter `assets/images/`, sie werden daher wie gewohnt angezeigt. Damit auch die
Großansicht im [Galerie-Viewer](#galerie-viewer) funktioniert, muss eine [Lightbox-Bildgröße](#lightbox-bildgröße)
im Frontend-Modul oder im Seitenlayout festgelegt sein. Ohne diese Einstellung verlinkt der Viewer auf das
Originalbild.

> ⚠️ **Wichtig**
>
> Auch Bilder aus geschützten Ordnern werden durch die Galerie öffentlich angezeigt sowie in die
> [Sitemap](#sitemap) und die strukturierten Daten aufgenommen. Ein geschützter Ordner eignet sich also nicht, um
> Bilder vor Besuchern zu verbergen. Dafür gibt es den [Veröffentlichungszeitraum](#unterstützte-felder) und die
> Option [In Ordner-Galerie verbergen](#einzelne-bilder-aus-der-galerie-ausblenden).

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
| **Lightbox-Bildgröße**                   | Bildgröße der Großansicht im Galerie-Viewer. Ohne Angabe wird die Lightbox-Bildgröße des Seitenlayouts verwendet (siehe [Lightbox-Bildgröße](#lightbox-bildgröße)). |
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

> 💡 **Hinweis**
>
> Wird **PhotoSwipe** verwendet, werden die benötigten JavaScript- und CSS-Dateien automatisch geladen. Eine
> zusätzliche Auswahl eines JavaScript-Templates im Seitenlayout ist nicht erforderlich.

> 💡 **Wichtig**
>
> Wird die **Contao-Lightbox** verwendet, muss im Seitenlayout **jQuery** aktiviert sowie das jQuery-Template
> **`j_colorbox`** eingebunden werden.

#### Lightbox-Bildgröße

Für die Großansicht sollte immer eine **Lightbox-Bildgröße** festgelegt sein, entweder im Frontend-Modul oder zentral
im Seitenlayout. Die Angabe im Modul hat Vorrang.

Ist keine Lightbox-Bildgröße festgelegt, verlinkt die Großansicht auf das unveränderte Originalbild unter `files/`.
Das bedeutet:

- Bei [geschützten Ordnern](#öffentliche-und-geschützte-ordner) ist das Originalbild nicht erreichbar, die
  Großansicht bleibt leer.
- Bei öffentlichen Ordnern werden die Originaldateien ausgeliefert, oft mehrere MB groß und mit allen EXIF-Daten
  (z. B. Kameramodell, Aufnahmezeit oder GPS-Koordinaten).
- Kann das JavaScript des Viewers nicht geladen werden, öffnet ein Klick auf ein Bild ebenfalls das Originalbild.

Mit einer Lightbox-Bildgröße erzeugt Contao die Großansicht über die Bildpipeline unter `assets/images/`. Auch ohne
JavaScript wird dann nur diese verkleinerte Version geöffnet.

Ist im Frontend-Modul keine Lightbox-Bildgröße festgelegt, weist das Backend beim Speichern darauf hin, dass sie dann
in allen Seitenlayouts gesetzt sein muss, auf denen das Modul verwendet wird.

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

### Einzelne Bilder aus der Galerie ausblenden

Einzelne Bilder können unabhängig von den Metadaten des jeweiligen Ordners aus der Galerie ausgeblendet werden.

Dazu steht in der Contao-Dateiverwaltung für jedes Bild die Option **In Ordner-Galerie verbergen** zur Verfügung.

Wird die Option aktiviert, erscheint das Bild weder in der Galerie-Übersicht noch in einer Galerieansicht. Die Datei
selbst bleibt dabei unverändert im Dateisystem erhalten, sie wird lediglich bei der Darstellung der Galerie übersprungen.

> 💡 **Hinweis**
>
> Im Gegensatz zu den übrigen Galerie-Informationen wird diese Einstellung nicht in der
> [`_metadata.yml`](#metadaten-_metadatayml), sondern direkt am jeweiligen Bild in der Contao-Dateiverwaltung
> (`tl_files`) gespeichert. Dadurch lässt sich die Sichtbarkeit eines Bildes unabhängig vom jeweiligen Ordner pflegen.

> ℹ️ **Hinweis**
>
> Wird zusätzlich die Erweiterung
> [`contao-folder-gallery-download-extension-bundle`](https://github.com/cgoIT/contao-folder-gallery-download-extension-bundle)
> verwendet, ist ein verborgenes Bild automatisch auch nicht im ZIP-Download der Galerie enthalten.

### Einzelne Bilder hervorheben

Einzelne Bilder können in der Galerieansicht größer dargestellt werden, etwa um ein besonders gelungenes Foto
zwischen vielen Aufnahmen zu betonen.

Dazu steht in der Contao-Dateiverwaltung für jedes Bild die Option **In Ordner-Galerie groß zeigen** zur Verfügung.
Wie bei [In Ordner-Galerie verbergen](#einzelne-bilder-aus-der-galerie-ausblenden) wird sie direkt am Bild
(`tl_files`) gespeichert, die Datei selbst bleibt unverändert.

Ein hervorgehobenes Bild erhält im Template `gallery_content` zusätzlich die Klasse
`gallery-content__image--highlighted`. Das mitgelieferte Stylesheet zeigt es ab 768 px Breite über 2 × 2 Rasterfelder
an, die Anzahl lässt sich mit der CSS-Variable `--gallery-highlight-span` ändern. Die Reihenfolge der Bilder bleibt
erhalten, nachfolgende Bilder rücken in entstehende Lücken nach (`grid-auto-flow: dense`).

Projekte mit eigenem Template können die Hervorhebung mit `content.isHighlighted(image)` abfragen:

```twig
<div class="gallery-content__image{{ content.isHighlighted(image) ? ' gallery-content__image--highlighted' }}">
```

> 💡 **Hinweis**
>
> Wählen Sie für die Galerie eine Bildgröße, die auch bei der doppelten Kachelbreite scharf genug ist, sonst wirken
> hervorgehobene Bilder unscharf.

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

Das Bundle orientiert sich bewusst an den bestehenden Mechanismen von Contao und lässt sich über Templates und CSS an
das eigene Theme anpassen.

Unter anderem lassen sich

- eigene Twig-Templates verwenden,
- eigene CSS-Regeln ergänzen,
- Bildgrößen aus Contao nutzen,
- der Galerie-Viewer wählen,
- die Darstellung vollständig an das eigene Theme anpassen.

Für funktionale Erweiterungen stellt das Bundle zusätzlich eigene [Extension Points](#erweiterbarkeit) bereit.

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
| `--gallery-highlight-span` | `2` | Anzahl der Rasterspalten und -zeilen, über die ein [hervorgehobenes Bild](#einzelne-bilder-hervorheben) reicht. |

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

#### PhotoSwipe anpassen

Die PhotoSwipe-Initialisierung des Bundles kann über zwei JavaScript-Events erweitert bzw. angepasst werden.
Dadurch ist es nicht erforderlich, die interne PhotoSwipe-Initialisierung zu überschreiben.

##### PhotoSwipe-Optionen

Vor dem Erzeugen der `PhotoSwipeLightbox`-Instanz wird das Event
`folder-gallery:photoswipe:options` ausgelöst. Die aktuellen Optionen stehen in `event.detail` zur Verfügung
und können dort verändert werden.

Beispielsweise kann der Selector für die Galerieelemente angepasst werden:

```js
document.addEventListener('folder-gallery:photoswipe:options', (event) => {
    event.detail.children = 'div.gallery_item > a';
});
```

Die Änderungen gelten nur für die jeweilige PhotoSwipe-Instanz.

Einige Optionen werden anschließend von Folder Gallery selbst gesetzt, insbesondere `gallery` und `pswpModule`.
Diese Werte können daher über diesen Extension Point nicht überschrieben werden.

##### Übersetzte Beschriftungen

Die Beschriftungen der PhotoSwipe-Bedienelemente (Schließen, Zoom, Zurück, Weiter, Fehlermeldung) stammen aus den
Übersetzungen des Bundles (`contao_folder_gallery`, Schlüssel `folder_gallery.photoswipe_*`) und werden vom Template
als `data-pswp-*`-Attribute am Container ausgegeben. Das Skript übernimmt sie als PhotoSwipe-Optionen `closeTitle`,
`zoomTitle`, `arrowPrevTitle`, `arrowNextTitle` und `errorMsg`. Eigene Texte lassen sich daher über die
Übersetzungen oder im Event `folder-gallery:photoswipe:options` setzen, das nach den Übersetzungen ausgelöst wird.

> Projekte mit überschriebenem Template `gallery_content` müssen die `data-pswp-*`-Attribute aus dem Block `images`
> übernehmen, sonst bleiben die englischen PhotoSwipe-Standardtexte.

##### PhotoSwipe-Instanz erweitern

Nachdem die `PhotoSwipeLightbox`-Instanz erzeugt wurde, wird das Event
`folder-gallery:photoswipe:afterInit` ausgelöst. Die Instanz und das zugehörige Galerie-Element stehen in
`event.detail` zur Verfügung.

Damit können beispielsweise eigene Filter oder weitere PhotoSwipe-Events registriert werden:

```js
document.addEventListener('folder-gallery:photoswipe:afterInit', (event) => {
    const { lightbox } = event.detail;

    lightbox.addFilter('itemData', (itemData) => {
        // Eigene Anpassungen
        return itemData;
    });
});
```

Die beiden Events sind als öffentliche Extension Points vorgesehen. Die interne Implementierung von Folder Gallery
kann dadurch erweitert werden, ohne die mitgelieferte PhotoSwipe-Initialisierung oder ein Template überschreiben zu
müssen.

#### Bildgrößen

Das Bundle verwendet ausschließlich die in Contao konfigurierten Bildgrößen.

Im [Frontend-Modul](#frontend-modul) können unabhängig voneinander Bildgrößen für

- Coverbilder der Galerie-Übersicht
- Bilder innerhalb einer Galerie

ausgewählt werden.

Dadurch stehen sämtliche Funktionen der Contao-Bildpipeline wie responsive Bilder, verschiedene Ausgabeformate und
Bildzuschnitte automatisch zur Verfügung.

## Erweiterbarkeit

Das Bundle stellt Extension Points bereit, über die andere Contao-Erweiterungen zusätzliche Funktionen integrieren können.

### Aktionen innerhalb einer Galerie

Innerhalb einer geöffneten Galerie können zusätzliche Aktionen angezeigt werden. Das Bundle stellt
dafür das Interface `GalleryContentActionInterface` bereit.

Eine Erweiterung kann dieses Interface implementieren und über Dependency Injection als Service
registrieren. Der `GalleryContentActionProvider` sammelt automatisch alle registrierten
Implementierungen ein und stellt die erzeugten Aktionen im `GalleryContentViewModel` zur Verfügung.

Die Action wird durch das unveränderliche `GalleryContentAction`-DTO beschrieben:

| Eigenschaft | Typ | Beschreibung                                                                                                            |
|-------------|---|-------------------------------------------------------------------------------------------------------------------------|
| `type` | `string` | Identifiziert die Action. Der Wert wird als zusätzliche CSS-Klasse `gallery-content__action--{{ type }}` am Link ausgegeben und kann zur individuellen Gestaltung verwendet werden. |
| `label`     | `string` | Beschriftung der Action                                                                                                 |
| `url`       | `string` | Ziel-URL der Action                                                                                                     |
| `title`     | `string\|null` | Optionales `title`-Attribut                                                                                             |
| `target`    | `string\|null` | Optionales `target`-Attribut                                                                                            |
| `rel`       | `string\|null` | Optionales `rel`-Attribut                                                                                               |

> 💡 **Hinweis**
>
> Der `type` dient ausschließlich zur Identifikation und individuellen Gestaltung der Action. Das Bundle
> stellt selbst keine Icons für Actions bereit.
>
> Dadurch können beispielsweise über CSS-Pseudo-Elemente eigene Icons ergänzt werden:
>
> ```css
> .gallery-content__action--download::before {
>     content: '↓';
>     margin-right: .4em;
> }
> ```
>
> Welche Darstellung eine Action erhält, bleibt vollständig dem jeweiligen Theme überlassen.

Eine Implementierung von `GalleryContentActionInterface` kann abhängig von der aktuellen Galerie
entscheiden, ob eine Action angeboten werden soll. Sie gibt dazu entweder eine
`GalleryContentAction` oder `null` zurück.

> 💡 **Hinweis**
>
> Die Action beschreibt ausschließlich die Daten des Links.
>
> Die Actions werden innerhalb eines eigenen Action-Bereichs der Galerieansicht ausgegeben. Das Standard-Template
> stellt dabei bewusst nur den Link und seine grundlegenden HTML-Attribute bereit. Eine optische Hervorhebung,
> beispielsweise durch Icons, Farben oder Buttons, ist nicht Bestandteil der Action-API.
>
> Über den `type` kann eine Action im eigenen Theme gezielt angesprochen werden.

Beispielsweise kann eine Erweiterung eine Download-Funktion für eine Galerie bereitstellen:

```php
<?php

declare(strict_types=1);

namespace App\Gallery;

use Contao\PageModel;
use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentAction;
use Cgoit\ContaoFolderGalleryBundle\Action\GalleryContentActionInterface;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryFolder;
use Cgoit\ContaoFolderGalleryBundle\Model\GalleryOverview;

final class DownloadGalleryAction implements GalleryContentActionInterface
{
    public function createAction(
        GalleryOverview $overview,
        GalleryFolder $folder,
        PageModel $page,
    ): GalleryContentAction|null {
        return new GalleryContentAction(
            type: 'download',
            label: 'Alle Bilder herunterladen',
            url: '/gallery/download/'.$folder->getPath(),
        );
    }
}
```

Mit dieser Action wird im Standard-Twig-Template ein Link mit der CSS-Klasse `gallery-content__action--download` angezeigt.

Die Implementierung wird anschließend als Symfony-Service registriert:

```yaml
services:
    App\Gallery\DownloadGalleryAction:
        autowire: true
        autoconfigure: true
```

Jede Implementierung von `GalleryContentActionInterface` kann pro Galerie entweder genau eine Action
oder `null` zurückgeben. Dadurch kann eine Erweiterung eine Action abhängig von der aktuellen Galerie
bedingungsabhängig bereitstellen.

### Beispiel: Download einer Galerie als ZIP

Ein typischer Anwendungsfall für diesen Extension Point ist das Herunterladen aller Bilder einer Galerie
als ZIP-Datei.

Die ZIP-Erweiterung kann die Action bereitstellen und die URL auf einen eigenen Controller bzw. eine
eigene Route verweisen:

```text
Galerie
├── Beschreibung
├── Untergalerien
│
├── [ Alle Bilder herunterladen ]
│
└── Bilder
```

Die Erstellung des ZIP-Archivs und dessen Auslieferung bleiben vollständig Aufgabe der jeweiligen
Erweiterung. Das Folder Gallery Bundle muss dafür weder ZIP-Dateien erzeugen noch einen bestimmten
Download-Mechanismus kennen.

Dadurch können auch andere galeriebezogene Funktionen über denselben Extension Point ergänzt werden,
ohne dass diese Bestandteil des Folder Gallery Bundles selbst werden müssen.

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


## Datenschutz und Einwilligungen

Eine Galerie macht alle sichtbaren Bilder eines Ordners öffentlich. Das gilt besonders für Schulen, Vereine und
Veranstaltungen, bei denen Personen erkennbar abgebildet sind.

> ⚠️ **Hinweis**
>
> Dieser Abschnitt ist eine technische Orientierung und keine Rechtsberatung. Ob und unter welchen Bedingungen Fotos
> veröffentlicht werden dürfen, klärt die verantwortliche Stelle, bei Schulen in der Regel zusammen mit dem
> Datenschutzbeauftragten.

### Was wird veröffentlicht?

Sobald ein Galerie-Ordner veröffentlicht ist, erscheinen seine Bilder

- in der Galerie-Übersicht und in der Galerieansicht,
- in der [Sitemap](#sitemap) und in den strukturierten Daten (`ImageGallery`), die Suchmaschinen auslesen,
- als verkleinerte Kopien unter `assets/images/`, die Contao über die Bildpipeline erzeugt.

Auch Bildunterschriften, Alternativtexte und die Beschreibung der Galerie sind öffentlich. Namen von Kindern sollten
dort nur stehen, wenn eine Einwilligung ausdrücklich dafür vorliegt.

### Nicht öffentliche Ordner

Ein [geschützter Ordner](#öffentliche-und-geschützte-ordner) verhindert nur, dass die **Originaldateien** per URL
abrufbar sind. Die angezeigten Bilder der Galerie sind trotzdem öffentlich. Wichtig dabei:

- Lege eine [Lightbox-Bildgröße](#lightbox-bildgröße) fest. Ohne sie verlinkt die Großansicht bei öffentlichen
  Ordnern auf das Originalbild, einschließlich aller EXIF-Daten (z. B. GPS-Koordinaten).
- Um Bilder vor Besuchern zu verbergen, eignen sich der
  [Veröffentlichungszeitraum](#unterstützte-felder) und die Option
  [In Ordner-Galerie verbergen](#einzelne-bilder-aus-der-galerie-ausblenden), nicht der geschützte Ordner.

### Fotos von Kindern und Einwilligungen

Bei Fotos von Minderjährigen ist in der Regel die Einwilligung der Erziehungsberechtigten erforderlich, und sie lässt
sich jederzeit widerrufen. Aus dem Aufbau des Bundles ergibt sich ein praktischer Ablauf:

1. **Erst prüfen, dann veröffentlichen.** Lege für neue Galerien zunächst ein
   `published_from` in der Zukunft fest oder lade die Fotos in einen Ordner außerhalb der Galerie-Wurzel hoch. Prüfe sie
   auf fehlende Einwilligungen und veröffentliche die Galerie erst danach.
2. **Einzelne Bilder sofort entfernen.** Aktiviere für ein Bild **In Ordner-Galerie verbergen**. Es verschwindet aus
   Galerie, Sitemap und strukturierten Daten, ohne dass die Datei gelöscht werden muss.
3. **Befristet veröffentlichen.** Mit `published_until` lässt sich eine Galerie nach Ablauf einer Einwilligung (z. B.
   am Ende des Schuljahres) automatisch ausblenden.
4. **Dauerhaft löschen.** Hat jemand die Einwilligung widerrufen, lösche die Datei in der Dateiverwaltung. Leere
   anschließend den Bildcache (Systemwartung), damit auch die verkleinerten Kopien unter `assets/images/` verschwinden.
   Bereits von Suchmaschinen oder anderen Diensten zwischengespeicherte Kopien lassen sich so nicht entfernen, dort
   muss die Löschung ggf. gesondert beantragt werden.

## FAQ

### Im Metadaten-Editor werden keine Galerien angezeigt.

Prüfe, ob mindestens ein [Frontend-Modul](#frontend-modul) vom Typ **Folder Gallery** konfiguriert wurde und eine Galerie-Wurzel ausgewählt ist.

Der [Metadaten-Editor](#metadaten-editor) ermittelt seine Galerie-Struktur ausschließlich aus den konfigurierten Frontend-Modulen.

### Meine Galerie wird im Frontend nicht angezeigt.

Prüfe insbesondere folgende Punkte:

- Existiert die Galerie innerhalb der konfigurierten [Galerie-Wurzel](#frontend-modul)?
- Befindet sich die Galerie innerhalb eines veröffentlichten Zeitraums (Liegt der aktuelle Zeitpunkt innerhalb von `published_from` und `published_until`)?
- Ist der Ordner nicht versehentlich auf `overview_mode: transparent` gesetzt?

### PhotoSwipe bzw. die Lightbox öffnet sich nicht.

Prüfe zunächst die [Konfiguration des Seitenlayouts](#galerie-viewer).

- Für **PhotoSwipe** werden die benötigten Assets automatisch geladen, sobald PhotoSwipe als Galerie-Viewer
  verwendet wird. Ein zusätzliches JavaScript-Template ist nicht erforderlich.
- Für die **Contao-Lightbox** müssen **jQuery** sowie das Template `j_colorbox` aktiviert sein.
- Bleibt die Großansicht leer, obwohl sich der Viewer öffnet, fehlt bei einem geschützten Galerie-Ordner meist die
  [Lightbox-Bildgröße](#lightbox-bildgröße).

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

### Ein einzelnes Bild wird nicht in der Galerie angezeigt.

Prüfe in der Contao-Dateiverwaltung, ob für das betreffende Bild die Option **In Ordner-Galerie verbergen**
aktiviert ist.

Ist diese Option aktiviert, wird das Bild bewusst weder in der Galerie-Übersicht noch in einer Galerieansicht
dargestellt (siehe [Einzelne Bilder aus der Galerie ausblenden](#einzelne-bilder-aus-der-galerie-ausblenden)).

Soll das Bild wieder angezeigt werden, deaktiviere die Option und speichere die Datei erneut.

### Werden die Bilder in einer Datenbank gespeichert?

Nein.

Das Bundle arbeitet ausschließlich mit den Dateien innerhalb des `files/`-Verzeichnisses. In der Datenbank wird
lediglich die Konfiguration des [Frontend-Moduls](#frontend-modul) sowie – pro Bild optional – die Sichtbarkeit in
der Galerie (siehe [Einzelne Bilder aus der Galerie ausblenden](#einzelne-bilder-aus-der-galerie-ausblenden))
gespeichert.

### Wo werden Bildunterschriften gepflegt?

Bei Verwendung von **PhotoSwipe** werden Bildunterschriften automatisch aus den Bildinformationen übernommen.

Existiert ein Untertitel (`figcaption`), wird dieser verwendet. Andernfalls verwendet das Bundle den Alternativtext (alt).

Dadurch können Bildunterschriften direkt über die Dateiattribute in Contao gepflegt werden, ohne dass zusätzliche Felder
innerhalb der Galerie erforderlich sind.

### Muss ich die `_metadata.yml` manuell bearbeiten?

Nein.

Die Metadaten können sowohl direkt in der [`_metadata.yml`](#metadaten-_metadatayml) als auch über den integrierten [Metadaten-Editor](#metadaten-editor) gepflegt
werden. Beide Arbeitsweisen können beliebig kombiniert werden.

### In einem Galerie-Ordner liegt eine `_metadata.orphaned.yml`.

Diese Datei entsteht beim [Update von Version 1.10.1 oder älter](#update-von-version-1101-oder-älter), wenn für einen
geschützten Galerie-Ordner zwei unterschiedliche `_metadata.yml` existierten. Wirksam ist die `_metadata.yml`, die
`_metadata.orphaned.yml` enthält die zuletzt über den Metadaten-Editor gespeicherten Werte. Übernimm die gewünschten
Angaben in die `_metadata.yml` und lösche danach die `_metadata.orphaned.yml`.

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

## Diese Erweiterung im Einsatz

Manchmal sagt ein Blick auf eine fertige Seite mehr als jede Beschreibung. Die folgenden Websites setzen das
**Contao Folder Gallery Bundle** produktiv ein:

- [foto.weitzeldesign.com](https://foto.weitzeldesign.com/) – Bereich *Portfolio*
- [gickelskerb.de](https://gickelskerb.de/) – Bereich *Galerie*

Ein herzliches Dankeschön an die Betreiber dieser Seiten dafür, dass wir sie hier nennen dürfen!

Du nutzt die Erweiterung ebenfalls und hättest nichts dagegen, hier aufgeführt zu werden? Dann freuen wir uns
sehr über eine kurze Rückmeldung – am einfachsten über ein
[Issue](https://github.com/cgoIT/contao-folder-gallery-bundle/issues) oder eine
[Diskussion](https://github.com/cgoIT/contao-folder-gallery-bundle/discussions) auf GitHub. Jedes weitere Beispiel
hilft anderen dabei, sich ein Bild von den Möglichkeiten der Erweiterung zu machen.

## Mitwirken

Fehlerberichte, Verbesserungsvorschläge und Pull Requests über GitHub sind jederzeit willkommen.

Falls du Fragen oder Ideen zur Erweiterung hast, freuen wir uns über ein Issue oder eine Diskussion auf GitHub.

## Lizenz

Dieses Bundle steht unter der **LGPL-3.0-or-later**.

Weitere Informationen findest du in der Datei `LICENSE`.
