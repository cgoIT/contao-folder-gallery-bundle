# Contao Folder Gallery Bundle

[![](https://img.shields.io/packagist/v/cgoit/contao-folder-gallery-bundle.svg)](https://packagist.org/packages/cgoit/contao-folder-gallery-bundle)
![Dynamic JSON Badge](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FcgoIT%2Fcontao-folder-gallery-bundle%2Fmain%2Fcomposer.json\&query=%24.require%5B%22contao%2Fcore-bundle%22%5D\&label=Contao%20Version)
[![](https://img.shields.io/packagist/dt/cgoit/contao-folder-gallery-bundle.svg)](https://packagist.org/packages/cgoit/contao-folder-gallery-bundle)
[![CI](https://github.com/cgoIT/contao-folder-gallery-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/cgoIT/contao-folder-gallery-bundle/actions/workflows/ci.yml)

## Inhaltsverzeichnis

- Warum gibt es diese Erweiterung?
- Installation
- Schnellstart (5 Minuten bis zur ersten Galerie)
- Designprinzipien
- Metadaten (`_metadata.yml`)
- Backend
- Frontend
- FAQ
- Lizenz

Das **Contao Folder Gallery Bundle** verfolgt einen anderen Ansatz als klassische Galerie-Erweiterungen.

Anstatt Galerien im Backend anzulegen und Bilder einzelnen Datensätzen zuzuordnen, nutzt diese Erweiterung die bereits
vorhandene Ordnerstruktur im Dateisystem. Jeder Ordner entspricht genau einer Galerie. Zusätzliche Informationen wie
Titel, Beschreibung oder Veröffentlichungszeiträume werden direkt in einer `_metadata.yml` innerhalb des jeweiligen
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
einer `_metadata.yml` innerhalb des jeweiligen Ordners gespeichert.

Damit entfällt der größte Teil der wiederkehrenden Backend-Konfiguration.

> **Das Dateisystem ist die Quelle der Wahrheit.**
>
> Contao übernimmt die Darstellung der Galerien und ergänzt die vorhandene Ordnerstruktur lediglich um optionale Metadaten.

### Keine zusätzlichen Datenbanktabellen

Das Bundle verzichtet bewusst auf eigene Datenbanktabellen.

Eine Galerie besteht ausschließlich aus

- der vorhandenen Ordnerstruktur innerhalb von `files/`,
- den Bildern selbst,
- sowie optionalen `_metadata.yml`-Dateien.

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

3. Ein Frontend-Modul **Ordner-Galerie** erstellen.

4. Als **Galerie-Wurzel** den gewünschten Ordner (z. B. `files/gallery`) auswählen.

5. Das Frontend-Modul auf einer Seite einbinden.

Fertig. Das Bundle erzeugt daraus automatisch die Galerieübersicht sowie die einzelnen Galerieansichten.

> 💡**Tipp**
>
> Eine `_metadata.yml` ist optional. Ohne Metadaten verwendet das Bundle automatisch sinnvolle Standardwerte.
> Metadaten können jederzeit später ergänzt werden.

## Designprinzipien

Das Contao Folder Gallery Bundle wurde nach einigen einfachen Grundprinzipien entwickelt.

### Das Dateisystem ist die Quelle der Wahrheit

Die Galerie existiert bereits durch ihre Ordnerstruktur. Contao ergänzt diese lediglich um optionale Metadaten und stellt
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

Jeder Ordner repräsentiert dabei genau einen Eintrag der Galerie. Dieser kann entweder als **Galerie** oder
als **Galeriegruppe** dargestellt werden.

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

Ob ein Ordner als Galerie oder als Galeriegruppe dargestellt wird, wird in seiner `_metadata.yml` festgelegt.

Jeder Ordner kann zusätzlich eine `_metadata.yml` enthalten, in der beispielsweise

- Titel
- Beschreibung
- Coverbild
- Veröffentlichungszeitraum
- Sortierung

definiert werden können.

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

published_from: '04.09.2026 18:00'
published_until: '30.09.2076 23:59'

sort_order: asc
overview_mode: gallery
```

### Unterstützte Felder

| Feld | Beschreibung |
|------|--------------|
| `title` | Titel der Galerie oder Galeriegruppe |
| `description` | Beschreibung (HTML erlaubt) |
| `cover` | Dateiname des Coverbildes innerhalb des aktuellen Ordners |
| `published_from` | Galerie ist erst ab diesem Zeitpunkt sichtbar |
| `published_until` | Galerie ist nur bis zu diesem Zeitpunkt sichtbar |
| `sort_order` | Sortierreihenfolge der Unterordner (`asc` oder `desc`) |
| `overview_mode` | Legt fest, wie der Ordner behandelt wird (`gallery`, `group` oder `hidden`) |

### overview_mode

Der Wert `overview_mode` steuert, wie ein Ordner innerhalb der Galerie interpretiert wird.

| Wert | Bedeutung |
|------|-----------|
| `gallery` | Der Ordner wird als normale Galerie dargestellt. |
| `group` | Der Ordner dient als Galeriegruppe. Die enthaltenen Unterordner werden als einzelne Galerien angezeigt. |
| `hidden` | Der Ordner wird in der Galerie-Struktur übersprungen. Seine Unterordner werden direkt in die übergeordnete Ebene übernommen. Dies eignet sich beispielsweise für rein organisatorische Zwischenordner. |

> 💡 **Hinweis**
>
> Das Coverbild muss sich im gleichen Ordner befinden wie die `_metadata.yml`. In der Metadatendatei wird
> ausschließlich der Dateiname gespeichert.

> 💡 **Hinweis**
>
> Datums- und Uhrzeitangaben werden im Datumsformat gespeichert, das in den **Contao-Einstellungen** konfiguriert ist.

### overview_mode

Der Wert `overview_mode` steuert, wie ein Ordner innerhalb der Galerie interpretiert wird.

| Wert | Bedeutung |
|------|-----------|
| `gallery` | Der Ordner wird als normale Galerie dargestellt. |
| `group` | Der Ordner dient als Galeriegruppe. Die enthaltenen Unterordner werden als einzelne Galerien angezeigt. |
| `hidden` | Der Ordner wird in der Galerie-Struktur übersprungen. Seine Unterordner werden direkt in die übergeordnete Ebene übernommen. Dies eignet sich beispielsweise für rein organisatorische Zwischenordner. |

#### Beispiel für `hidden`

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
overview_mode: hidden
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

### Manuelle Bearbeitung oder Backend-Editor

Die `_metadata.yml` kann jederzeit mit einem beliebigen Texteditor bearbeitet oder neu erstellt werden.

Alternativ stellt das Bundle einen komfortablen Backend-Editor zur Verfügung, der dieselben Informationen grafisch
bearbeitet und anschließend wieder in die `_metadata.yml` schreibt.

Beide Wege können beliebig kombiniert werden. Änderungen, die manuell an der Datei vorgenommen werden, sind im
Backend sofort sichtbar. Ebenso können Dateien zunächst manuell angelegt und später bequem über den Backend-Editor
gepflegt werden.

## Backend

### Frontend-Modul

Die Galerie wird wie jedes andere Contao-Modul über ein Frontend-Modul eingebunden.

Das Frontend-Modul definiert die Galerie-Wurzel und steuert die Darstellung der Galerie im Frontend.

| Einstellung | Beschreibung |
|--------------|--------------|
| **Galerie-Wurzel** | Wurzelordner der Galerie. Alle Unterordner werden automatisch als Galerie-Struktur interpretiert. |
| **Galeriebildgröße** | Bildgröße der Bilder innerhalb einer Galerie. |
| **Coverbildgröße** | Bildgröße der Vorschaubilder in der Galerie-Übersicht. |
| **Galerie-Viewer** | Legt fest, ob die Bilder mit der Contao-Lightbox oder mit PhotoSwipe geöffnet werden. |
| **Übersichts-Template** | Twig-Template für die Darstellung der Galerie-Übersicht. |
| **Galerie-Template** | Twig-Template für die Darstellung einer einzelnen Galerie. |

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

### Metadaten-Editor

Der Metadaten-Editor dient zur komfortablen Bearbeitung der `_metadata.yml`-Dateien.

Auf der linken Seite wird die komplette Galerie-Struktur aller konfigurierten Galerie-Wurzeln als Baum dargestellt.

Nach Auswahl eines Ordners werden auf der rechten Seite dessen Metadaten angezeigt und können direkt bearbeitet werden.

Alle Änderungen werden unmittelbar wieder in die entsprechende `_metadata.yml` geschrieben. Dadurch können der
Backend-Editor und eine manuelle Bearbeitung der Dateien jederzeit beliebig miteinander kombiniert werden.

> 💡 **Hinweis**
>
> Aus Gründen der Datenkonsistenz kann als Coverbild ausschließlich eine Datei aus dem jeweiligen Galerieordner
> verwendet werden.

## Frontend

### Routing

Für die Darstellung der Galerien genügt eine einzige Contao-Seite mit einem eingebundenen **Folder Gallery**-Frontend-Modul.

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

Ist ein Ordner als `group` konfiguriert, wird dieser als Überschrift dargestellt und seine Unterordner werden
darunter gruppiert angezeigt.

Ordner mit `overview_mode: hidden` erscheinen dagegen nicht in der Übersicht. Stattdessen werden deren Unterordner
direkt in die übergeordnete Ebene übernommen.

### Galerieansicht

Beim Aufruf einer Galerie werden automatisch alle Bilder des entsprechenden Ordners dargestellt.

**Die Bilder werden dabei vollständig über die Bildpipeline von Contao erzeugt.** Dadurch stehen sämtliche Funktionen
von Contao wie responsive Bilder, Bildgrößen und verschiedene Ausgabeformate (z. B. WebP oder AVIF) ohne
zusätzliche Konfiguration zur Verfügung.

Enthält eine Galerie weitere Unterordner, werden diese oberhalb der Bilder ebenfalls angezeigt und können direkt
geöffnet werden.

### Eigene Anpassungen

Das Bundle orientiert sich bewusst an den bestehenden Mechanismen von Contao und lässt sich an vielen Stellen erweitern.

Unter anderem können

- eigene Twig-Templates verwendet werden,
- eigene CSS-Regeln ergänzt werden,
- Bildgrößen aus Contao genutzt werden,
- der Galerie-Viewer gewählt werden,
- die Darstellung vollständig an das eigene Theme angepasst werden.

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

Das Standard-Stylesheet verwendet CSS-Variablen, um die wichtigsten Layout-Parameter einfach anpassen zu können.

Alle Variablen werden innerhalb der Klasse `.module-folder-gallery` definiert und können problemlos im eigenen Theme
überschrieben werden.

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

##### Bilder

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-border-radius` | `0.5rem` | Abrundung der Bilder. |

##### Hover-Effekte

| Variable | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `--gallery-hover-scale` | `1.04` | Vergrößerung des Bildes beim Überfahren mit der Maus. |
| `--gallery-hover-brightness` | `0.95` | Helligkeit des Bildes beim Hover-Effekt. |
| `--gallery-transition-duration` | `0.2s` | Dauer der Hover-Animationen. |

#### Bildgrößen

Das Bundle verwendet ausschließlich die in Contao konfigurierten Bildgrößen.

Im Frontend-Modul können unabhängig voneinander Bildgrößen für

- Coverbilder der Galerie-Übersicht
- Bilder innerhalb einer Galerie

ausgewählt werden.

Dadurch stehen sämtliche Funktionen der Contao-Bildpipeline wie responsive Bilder, verschiedene Ausgabeformate und
Bildzuschnitte automatisch zur Verfügung.

## FAQ

### Im Metadaten-Editor werden keine Galerien angezeigt.

Prüfen Sie, ob mindestens ein Frontend-Modul vom Typ **Folder Gallery** konfiguriert wurde und eine Galerie-Wurzel ausgewählt ist.

Der Metadaten-Editor ermittelt seine Galerie-Struktur ausschließlich aus den konfigurierten Frontend-Modulen.

---

### Meine Galerie wird im Frontend nicht angezeigt.

Prüfen Sie insbesondere folgende Punkte:

- Existiert die Galerie innerhalb der konfigurierten Galerie-Wurzel?
- Liegt der aktuelle Zeitpunkt innerhalb von `published_from` und `published_until`?
- Ist der Ordner nicht versehentlich auf `overview_mode: hidden` gesetzt?

---

### PhotoSwipe bzw. die Lightbox öffnet sich nicht.

Prüfen Sie die Konfiguration des Seitenlayouts.

- Für **PhotoSwipe** muss das JavaScript-Template `js_photoswipe` aktiviert sein.
- Für die **Contao-Lightbox** müssen **jQuery** sowie das Template `j_colorbox` aktiviert sein.

---

### Muss ich die `_metadata.yml` manuell bearbeiten?

Nein.

Die Metadaten können sowohl direkt in der `_metadata.yml` als auch über den integrierten Metadaten-Editor gepflegt
werden. Beide Arbeitsweisen können beliebig kombiniert werden.

---

### Kann ich die Erweiterung später wieder entfernen?

Ja.

Die Erweiterung speichert sämtliche Informationen direkt im Dateisystem und ergänzt lediglich einige
Konfigurationsfelder im Frontend-Modul.

Die eigentlichen Bilder und Ordner bleiben unverändert erhalten und können anschließend problemlos mit der
Contao-Standardgalerie oder einer anderen Galerie-Erweiterung weiterverwendet werden.

## Lizenz

Dieses Bundle steht unter der **LGPL-3.0-or-later**.

Weitere Informationen finden Sie in der Datei `LICENSE`.
