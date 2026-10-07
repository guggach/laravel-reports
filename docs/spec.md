# guggach/laravel-reports — Spezifikation

Status: Entwurf 0.1 · Stand: 2026-10-07
Die ursprüngliche Ideensammlung liegt unverändert in [`docs/spec-notes.md`](spec-notes.md).

---

## 0. Kurzfassung

`guggach/laravel-reports` ist ein Laravel-Paket, mit dem ein Entwickler **druckfertige Reports** definiert (Rechnung, Liste, Auswertung), sie **serverseitig mit PHP/Blade rendert** und als **HTML/PDF** (später Word/Excel) ausgibt. Das Besondere ist ein **Band-/Seitenmodell** mit Kopf-/Fusszeilen, verschachtelten Gruppen und optionaler **strikter Platzierung** (Rechnung, Einzahlungsschein) – genau das, was reine HTML→PDF-Tools nicht leisten.

Leitprinzipien:

1. **Der Report wird immer serverseitig gerendert** – mit PHP und/oder Blade. Der Entwickler hat dort vollständige Freiheit über das Markup.
2. **Filterformular und HTML-Anzeige-Frame sind optional** und stack-abhängig (Blade/Livewire, Inertia Vue/React). Die Report-Erzeugung selbst hängt **nicht** vom Frontend-Stack ab.
3. **PHP-first:** Reportdefinitionen sind PHP-Klassen. JSON/XML ist eine spätere, dünne Serialisierungs-Schicht auf dasselbe interne Modell.
4. **Ein Renderer (Chromium), zwei Modi:** *Flow* (Browser paginiert) und *Strict* (fixe Bandhöhen, arithmetische Seitenaufteilung).
5. **Layouts:** wiederverwendbare, einbindbare Layouts (Blade/Vue/React) sichern ein einheitliches Aussehen (Logo, Kopf/Fuss, Typografie) über mehrere Reports hinweg.

---

## 1. Ausgangslage

Laravel ist hervorragend für Webseiten und CRUD-Formulare, diese sind aber schlecht druckbar: keine Seitenvoransicht, keine Kopf-/Fusszeilen, keine verlässliche Seitenaufteilung, kein PDF.

Reporttypen, die das Paket abdecken soll:

- **Einzelformular** – ein Datensatz, Felder über die Seite verteilt (z. B. Rechnung, Anschreiben, Einzahlungsschein).
- **Liste** – Loop über eine Datenmenge, Excel-ähnlich, mehrzeilig pro Detailzeile (z. B. Preisliste, Buchungsliste).
- **Klassischer Spaltenreport** – Sonderform der Liste: Spalten mit Label, Wert (Feldname oder Closure), Formatierung, Ausrichtung und optionaler Summenzeile; schnell definierbar, ohne eigenes Band-Markup.

---

## 2. Abgrenzung zu bestehenden Paketen

| Ansatz | Was er liefert | Was fehlt |
|---|---|---|
| `barryvdh/laravel-dompdf`, `snappy/wkhtmltopdf` | HTML → PDF | Kein Band-/Seitenmodell, kein CSS-Grid/Flex, eingeschränktes Layout |
| `elgibor-solution/laravel-report-builder` | Metadaten-getriebene **Tabelle**, Queries, Filter-Operatoren, Aggregate, Exporte, History, Security | Keine Bänder, keine Kopf-/Fusszeile, keine verschachtelten Gruppen-Bänder, keine strikte Seitenplatzierung; Definitionen in der DB, ein generischer View |
| `jimmyjs/laravel-report-generator` | Fluentes `of($title,$meta,$query,$columns)`, Spalten mit Closures, `groupBy`+`showTotal`, `stream/download/store`, PDF (dompdf/snappy) + Excel/CSV | Kein Band-/Seitenmodell, keine Kopf-/Fusszeile, keine Strict-Platzierung; rein spaltenbasiert |

**Differenzierer dieses Pakets:** das Band-/Seitenlayout (Page-Bänder, verschachtelte Gruppen, drei Reportarten inkl. Spaltenreport, Strict-Placement) **plus** volle Blade-Freiheit im Report selbst und einbindbare Layouts.

Gute Ideen aus dem Umfeld werden übernommen (Feld-/Parameter-DTOs, Type→Operator→Format-Map, `ReportSource`-Klassen, swappable Renderer/Exporter, Source als Trust-Boundary, fluente Ausgabe-Verben und das Spaltenreport-Muster), aber das Paket bleibt eigenständig.

---

## 3. Grundbegriffe und Modell

### 3.1 Objekte

- **Report** – eine PHP-Klasse (`App\Reports\...`), die Datenquelle, Bänder, Filter, Gruppierung, Aggregate und Seiteneinrichtung definiert.
- **ReportDefinition** – internes, unveränderliches DTO-Modell. Eingänge: PHP-Builder, später JSON-Loader. Ausgänge: Renderer, Validierung, Schema für die Filterformulare.
- **ReportSource** – liefert die Datenmenge + Felddefinitionen (`ReportField`, `ReportParameter`).
- **Band** – ein Block auf voller Breite mit fester Höhe (`height`) oder Mindesthöhe (`minHeight`) und Umbruch-Regeln.
- **Detail** – das zentrale Band, das pro Datensatz einmal gerendert wird.
- **Gruppe** – Sortier-/Gruppierungsschlüssel; löst Gruppenkopf/-fuss je Level aus (verschachtelbar).
- **Aggregat / virtuelles Rechenfeld** – wird bei jedem Detail aktualisiert; in Gruppenfüssen und im Report-Fuss verfügbar.
- **Filter** – Eingaben des Aufrufers oder (optional) des Filterformulars; schränken die Datenmenge ein.
- **Preset** – gespeicherte Kombination aus Filtern + Sortierung.
- **Output** – Ausgabeformat (HTML, PDF, später Word/Excel) inkl. optionaler Speicherung.
- **Layout** – wiederverwendbares äusseres Gerüst (Logo, Kopf-/Fuss-Chrome, Typografie, Farben, CSS), das ein Report einbindet. Wird serverseitig als Blade-View/Component gepflegt (Vue/React nur für den Vorschau-Frame).

### 3.2 Bänderstapel

Reihenfolge pro Report/Seite (von aussen nach innen):

```
Page Header                      (nur seitenorientiert)
  Report Start Title
  Grid Header (pro Seite wiederholt)
    Group Header Level 1
      Group Header Level 2
        ...
          Detail  ← pro Datensatz, zentral
        ...
      Group Footer Level 2
    Group Footer Level 1
  Report End Footer
Page Footer
```

### 3.3 Zwei Reportarten

| | Einzelformular | Liste |
|---|---|---|
| Datenmenge | 1 Datensatz | n Datensätze |
| Detail-Band | einmal, freies Layout | pro Datensatz, typ. Gridzeile |
| Bruchverhalten | „nicht mitten durchbrechen" pro Feldgruppe | pro Detailzeile kein Umbruch |
| Gesamtseiten | bekannt nach Pagination | oft erst nach Pagination |
| Modus | meist **Strict** | meist **Flow** |

Ein **klassischer Spaltenreport** ist die einfachste Ausprägung der Liste: Spalten = Label + Feld/Closure + Format + Ausrichtung, plus optionale Fuss-Summe. Er nutzt intern dieselbe Band-Pipeline (Grid-Header, Detailzeile, Report-Fuss), benötigt aber kein eigenes Blade-Markup.

---

## 4. Architektur

### 4.1 Schichten

```
Definition (PHP/DTO/JSON)
      │
Data (ReportSource → Query/Collection)
      │
Pipeline: filtern → sortieren → gruppieren → aggregieren
      │
Rendering: Blade-Bänder (server-side, PHP/Blade)
      │
Pagination-Assembler:  Flow  |  Strict
      │
Output-Adapter: HTML · PDF · (Word · Excel · CSV)
```

### 4.2 Zentrale Contracts / Klassen (Vorschlag)

```
Guggach\Reports\ReportsServiceProvider
Guggach\Reports\Facades\Reports                // Reports::render(), ::run(), ::presets()

Guggach\Reports\Report                          // Basisklasse, die der Entwickler erbt
Guggach\Reports\Sources\ReportSource            // Basisklasse für Datenquellen
Guggach\Reports\Sources\ReportField
Guggach\Reports\Sources\ReportParameter

Guggach\Reports\Definition\ReportDefinition     // DTO
Guggach\Reports\Definition\ReportBuilder
Guggach\Reports\Definition\Bands\Band
Guggach\Reports\Definition\Bands\PageHeader | PageFooter
Guggach\Reports\Definition\Bands\ReportStart | ReportEnd
Guggach\Reports\Definition\Bands\GridHeader
Guggach\Reports\Definition\Bands\GroupHeader | GroupFooter
Guggach\Reports\Definition\Bands\Detail
Guggach\Reports\Definition\PageSetup
Guggach\Reports\Definition\ReportMode            // enum: Flow | Strict

Guggach\Reports\Layouts\Layout                   // Layout-Basisklasse (Slots, CSS, Vererbung)
Guggach\Reports\Layouts\LayoutRegistry

Guggach\Reports\Engine\ReportEngine             // Orchestrierung der Pipeline
Guggach\Reports\Engine\GroupResolver
Guggach\Reports\Engine\AggregateResolver
Guggach\Reports\Engine\Paginator                 // Flow- und Strict-Implementierung
Guggach\Reports\Engine\RenderContext             // Datenkontext für Blade-Bänder

Guggach\Reports\Aggregates\Aggregate             // Interface
Guggach\Reports\Aggregates\Sum | Avg | Count | Min | Max

Guggach\Reports\Filters\FilterBag
Guggach\Reports\Filters\FilterField
Guggach\Reports\Filters\PresetRepository

Guggach\Reports\Contracts\Renderer               // html | pdf | ...
Guggach\Reports\Contracts\Exporter               // word | excel | csv (v2)
Guggach\Reports\Output\OutputOptions
```

### 4.3 Rendering = immer server-side

Der Reportinhalt wird mit **Blade** (oder reinem PHP) gerendert. Jedes Band erhält einen `RenderContext` (Report, Datensatz, Gruppenzustand, Aggregate, Seiteninfo, Filter). Der Entwickler schreibt das Markup frei.

Wichtig: Die optionalen UI-Stubs (Abschnitt 11) rendern **nur** das Filterformular und den umgebenden Frame. **Sie rendern nie den Report selbst.**

---

## 5. Definition und API

### 5.1 Report-Basisklasse (Skizze)

```php
namespace App\Reports;

use Guggach\Reports\Report;
use Guggach\Reports\Definition\ReportBuilder;

final class PriceListReport extends Report
{
    public function key(): string
    {
        return 'price_list';
    }

    public function name(): string
    {
        return 'Preisliste';
    }

    /** Datenquelle (PHP-first, Eloquent/Query-Builder). */
    public function source(): \Guggach\Reports\Sources\ReportSource
    {
        return new \App\Reports\Sources\PriceSource($this->filters());
    }

    /** Band-, Filter- und Aggregatdefinition. */
    public function define(ReportBuilder $r): void
    {
        $r->mode(ReportMode::Flow)
          ->pageSetup(PageSetup::a4()->portrait()->marginsMm(20, 15, 20, 15));

        $r->detail(view: 'reports.price-row', height: 6)   // Blade-View, volle Freiheit
          ->repeatGridHeader(view: 'reports.price-grid-head');

        $r->pageHeader(view: 'reports.header')->height(15)->hideOnFirstPage();
        $r->pageFooter(view: 'reports.footer')->height(12);   // enthält Seitenzahlen

        $r->group('category')
          ->header(view: 'reports.category-head')
          ->footer(view: 'reports.category-foot')
          ->aggSum('price', as: 'category_total');

        $r->aggregate('grand_total', Sum::class, field: 'price', scope: 'report');
    }
}
```

### 5.2 Integrationsstufen

Ein Report kann auf zwei Stufen definiert werden – die Report-Engine kennt beide:

1. **Free-form (L1):** Der Entwickler liefert ein komplettes Blade-View. Das Paket kümmert sich um Filter, PDF-Konvertierung, Seite, Speicherung und den Frame. Maximale Freiheit, minimale Struktur.
2. **Banded (L2):** Der Entwickler deklariert Bänder; das Paket übernimmt Wiederholung (Grid-/Gruppenköpfe), Pagination, Aggregate und Platzhalter. Blade-Inhalte bleiben frei.

Beide Stufen nutzen denselben `ReportEngine` und dieselben Output-Adapter; L2 ergänzt den Band-Assembler.

### 5.3 Definitionen speichern

- Default-Basispfad: `resources/reports` (per Config änderbar).
- PHP-first: Der Entwickler legt Report-Klassen und Blade-Views dort ab bzw. in `App\Reports`.
- Ein optionaler JSON-Loader (v2) kompiliert Definitionsdateien in dasselbe `ReportDefinition`-DTO.

### 5.4 Artisan-Vorlagen

```
php artisan make:report {name} --template=blank|column-list|basic-list|banded-list|invoice
php artisan make:report-source {name}
php artisan make:layout {name} --stack=blade|vue|react
php artisan reports:install {--stack=blade-livewire|inertia-vue|inertia-react}
php artisan reports:list
```

Die `make:report`-Vorlagen erzeugen typische Listen-, Spalten- und Rechnungs-Gerüste (Klasse + Blade-Bänder), die der Entwickler anpasst.

### 5.5 Layouts (einheitliches Aussehen)

Ein **Layout** ist das wiederverwendbare äussere Gerüst eines Reports: Logo, Absender, Kopf-/Fuss-Chrome, Typografie, Farben, Ränder und CSS/Fonts. Es stellt ein einheitliches Aussehen über mehrere Reports hinweg sicher.

**Definition und Einbindung**

```php
namespace App\Reports\Layouts;

use Guggach\Reports\Layouts\Layout;

final class CompanyLayout extends Layout
{
    public function view(): string        { return 'reports.layouts.company'; } // Blade-View
    public function baseLayout(): ?string { return 'reports.layouts.base'; }    // Vererbung wie @extends
}
```

```php
// im Report
public function layout(): string { return CompanyLayout::class; }

// oder im Builder
$r->layout(CompanyLayout::class);
```

**Was ein Layout bereitstellt**

- äusseres HTML-Gerüst (`<html>`, `<head>` mit `<style>`/Fonts, `<body>`) für HTML und PDF.
- benannte **Regionen/Slots**: `header`, `footer`, `logo`, `content`. Die Bänder und Detailinhalte werden in `content` gerendert.
- Vorgaben für **Page Header/Footer**, die ein Report überschreiben kann.
- die Anzeige der **Meta-Zeile** (aktive Filter/Sortierung).

**Layout vs. Bänder**

- *Layout* = Chrome, Theming und Seitengerüst (wiederverwendbar).
- *Bänder* = Inhalt und Wiederholungslogik (report-spezifisch).
- Ein Report ohne eigenes Layout nutzt das Paket-Default (`default`).

**Vorlagen und Stacks**

- Mitgeliefert: `default`, `letterhead` (Logo + Absender), `blank`; erzeugbar via `make:layout`.
- Ablage unter `resources/views/reports/layouts` bzw. dem Config-Pfad.
- Layouts werden **immer serverseitig (Blade/PHP)** gerendert – auch wenn Filter-Formular und Vorschau-Frame in Vue/React laufen (Leitprinzip 1). Für den Frame gibt es separate, stack-spezifische Stubs.

**Vererbung / Verschachtelung**

- Ein Layout kann ein Parent-Layout erweitern (wie Blade `@extends`), z. B. Firmen-Grundlayout + rechnungs-spezifischer Kopf.

---

## 6. Bänder im Detail

### 6.1 Eigenschaften eines Bands

| Property | Bedeutung |
|---|---|
| `height` | Fixe Höhe (Pflicht in **Strict**, optional in **Flow**) |
| `minHeight` | Mindesthöhe (Flow) |
| `keepTogether` | Band darf nicht mitten durchbrechen (Default `true` beim Detail) |
| `breakBefore` / `breakAfter` | Expliziter Seitenumbruch |
| `repeatOnNewPage` | Band nach Seitenumbruch wiederholen (Grid-Header) |
| `visibilities` | `hideOnFirstPage`, `hideOnLastPage`, `onlyOddPages`, `onlyEvenPages` |
| `pages` | explizite Seitenliste (z. B. `[1]`) |
| `view` / `closure` | Blade-View oder Closure, erhält den `RenderContext` |

Höhen-Einheit und Bezugssystem werden über `PageSetup` (mm, A4, Ränder) definiert.

### 6.2 Page Header / Footer

- Nur für **seitenorientierte** Outputs (PDF, Druck, Word). Endlose Outputs (HTML-Stream, Excel) überspringen sie.
- Kopf startet bei `top 0,0`, Fuss bei `bottom 0,0`, jeweils fixe Höhe.
- Mehrere Kopf-/Fusszeilen möglich (Default + Seite-1-Sonderfall, odd/even), Auswahl über `visibilities`/`pages`.
- **Platzhalter** für `{page}`, `{pages}`, optional `{title}`, die je Modus gefüllt werden (siehe 10).

### 6.3 Report Start Title / End Footer

- Optional. Einfachste Form: statische Elemente (Titel, Erklärungen).
- Für komplexere Fälle: Datenklasse/Query, die **genau einen** Datensatz liefert (kein Loop).
- Aggregate sind im **Start** noch leer (keine Details verarbeitet) → dort statisch/single-record halten; im **End Footer** stehen alle Aggregate zur Verfügung.

### 6.4 Grid Header

- Wird nach einem Seitenumbruch vor den weiteren Details erneut ausgegeben.
- Empfehlung in **Flow**: echtes `<table><thead>` – Chromium wiederholt es automatisch.
- In **Strict**: Band `repeatOnNewPage = true`; der Assembler setzt es an den Anfang jeder neuen Seite eines laufenden Detail-Blocks.

### 6.5 Gruppen Header/Footer Level 1…n

- Bedingung: Änderung an einem oder mehreren definierten Attributen; beliebig verschachtelt.
- Sortierung wird vom `GroupResolver` erzwungen (siehe 7.2).
- Datenbezug wie Report-Kopf/-Fuss (Query oder Aggregate).

---

## 7. Daten und Aggregate

### 7.1 Datenquelle

- `ReportSource` (Basisklasse) mit `query(): Builder|Collection` und `defineFields(): ReportField[]`.
- v1: Eloquent / Query-Builder.
- Spätere Adapter: Raw SQL (SQL Anywhere/ODBC) und temporale Queries (`db-temporal`), hinter demselben Interface.
- **Sicherheit:** Quellen und Feldnamen kommen ausschliesslich vom Entwickler. Enduser liefern nie Roh-SQL oder Spaltennamen (Allow-List).

### 7.2 Gruppierung und Sortierung

- Mehrere Gruppenschlüssel, in dieser Reihenfolge sortiert (stabil).
- Der `GroupResolver` gruppiert über *aufeinanderfolgende* Runs (nicht über global distinkte Werte) und liefert die Gruppen-Hierarchie.
- Filter- und Sortierfelder müssen zum Allow-List der `ReportField`s gehören; Felder, die Teil einer Gruppe sind, sind nicht frei sortierbar.

### 7.3 Aggregate / virtuelle Rechenfelder

```php
interface Aggregate
{
    public function add(mixed $value): void;
    public function value(): mixed;
    public function reset(): void;
}
```

- Registrierung mit Name, Klasse, Feld und **Scope**:
  - `scope: 'report'` → im Report-Fuss (und End-Footer) verfügbar.
  - `scope: 'group:1' … 'group:n'` → im jeweiligen Gruppenfuss verfügbar; wird beim Betreten der Gruppe zurückgesetzt.
- Semantik:
  - Gruppenfuss Li zeigt den Wert über die abgeschlossene Gruppe i.
  - Report-Fuss zeigt den Gesamtwert.
  - In einem Gruppenkopf Li liest man den bisherigen Running-Wert (Summe der vorangehenden Gruppen / Details), nicht den der aktuellen Gruppe.
- Standardaggregate: `Sum`, `Avg`, `Count`, `CountDistinct`, `Min`, `Max`; eigene Klassen möglich.

---

## 8. Filter und Presets

### 8.1 Feldtypen und Operatoren

| Typ | UI | Operatoren / Eingabe |
|---|---|---|
| `string` | Textfeld (Masken `*meier*`, `%meier%`, case-insensitive) | `like`, `=`, `!=`, mehrere Werte per OR |
| `number` (int/decimal) | Operator + Wert | `=`, `!=`, `<`, `<=`, `>`, `>=`, `between`, `in` (Liste) |
| `boolean` | Ja/Nein/Beide | `=`, `is_null` |
| `id` / FK | Suchfeld → Multiselect (Anzeige über `label`-Attribut) | `in` |
| `enum` | Select / Multiselect | `in` |
| `date` / `datetime` | Datum + Bereich + relative Presets („dieser Monat") | `=`, `before`, `after`, `between`, `is_null` |
| `relation` | Label-Multiselect | `in` |
| `json` | optional Key-Pfad | `is_null`, `is_not_null` (Key-Filter später) |

`ReportField` trägt Metadaten: `key`, `label`, `type`, `sortable`, `filterable`, `aggregatable`, `hidden`, `format`, optional `options`/`relation`.

### 8.2 Delegation statt Pflichtformular

Filter können auf drei Wegen kommen:

1. **Vom aufrufenden Programm** übergeben (`Reports::render($report, $filters)`).
2. Über ein **generiertes Filterformular** (optionaler UI-Stub).
3. Über ein **gespeichertes Preset**.

Das Paket selbst braucht daher kein Frontend. Die Filter-UI ist ein optionaler Aufsatz.

### 8.3 Filter-Generatoren

- Pro Feldtyp ein Generator, der das Formmodell laut `ReportField`-Metadaten aufbaut.
- Stacks:
  - Blade + Livewire (Default-Stub)
  - Inertia Vue
  - Inertia React
  - (Fallback: reines Blade ohne Livewire)
- Die Generatoren liefern ein **Schema** (JSON), damit die Stacks dasselbe Modell nutzen.

### 8.4 Presets

- Kombination aus Filtern + Sortierung, benannt.
- **Privat** (nur Ersteller), **geteilt** (alle User sichtbar), **global** (Admin/Entwickler, für User nicht löschbar).
- Ablage in der DB (Abschnitt 12). Rechte über ein konfigurierbares Gate; keine harte Abhängigkeit von `spatie/laravel-permission`.

---

## 9. Output und Formate

| Format | Priorität | Charakter | Adapter |
|---|---|---|---|
| HTML (Vorschau/Druck/Stream) | **v1** | endlos, kein Seitenzwang | Blade + Frame |
| PDF | **v1** | seitenorientiert; auch still/archivierend | Chromium (spatie/laravel-pdf / Browsershot) |
| CSV | v2 | Datenexport | eigener Exporter |
| Word (docx/ODF/RTF) | v2 | seitenorientiert, editierbar | PHPWord |
| Excel (xlsx) | v2 | Daten, kein Pixel-Layout | PhpSpreadsheet |

- **Fluente Ausgabe-Verben** (angelehnt an etablierte Pakete): `render()`, `stream()`, `download($filename)`, `store($path)`, `save()`, `make()` – gleiches Aufrufmuster über alle Formate.
- **Meta-Zeile:** Der Aufrufer kann ein `meta`-Array (z. B. «Zeitraum», «Sortierung») mitgeben; das Layout/der Page-Header zeigt es an.
- Optionales **Speichern/Archivieren**: Disk + Pfad aus Config; Eintrag in `report_outputs`.
- **Display-Frame:** Der Frame um den HTML-Output (Vorschau + Buttons: PDF, Drucken, Speichern, Export) ist stack-abhängig und gehört zu den UI-Stubs (Abschnitt 11). Er kann auch komplett wegfallen, wenn das Host-System selbst einbettet.

---

## 10. Technische Basis (Chromium, Höhen, Pagination)

### 10.1 Renderer

- **Ein** HTML→PDF-Renderer: Chromium (headless), Default über `spatie/laravel-pdf`, alternativ direkt Browsershot.
- Der Entwickler darf in Blade modernes **CSS Grid/Flexbox** verwenden.
- Spacing/Höhen in **mm** (bzw. konfigurierbarer Einheit) basierend auf `PageSetup`.

### 10.2 Modus *Flow* (Browser paginiert)

| Anforderung | Lösung |
|---|---|
| Grid-Header auf jeder Seite | echtes `<table><thead>` (Chromium wiederholt es) |
| Band nicht mitten durchbrechen | `break-inside: avoid` |
| Erzwungener Umbruch | `break-before: page` |
| Seitenzahl + Gesamtseiten | Chromium-Kopf-/Fusszeilen-Template (`pageNumber`/`totalPages`) |
| Flexible Höhen | natürliches Fliessen |

Grenzen (bewusst): Seite-1-Sonderheader und das Wiederholen von Gruppenköpfen über Seitenumbrüche sind nur eingeschränkt möglich.

### 10.3 Modus *Strict* (arithmetische Pagination)

- Report liefert **fixe Bandhöhen**. Der Assembler rechnet `Σ Höhe` und schneidet die Bänder in `.page`-Container (fixe Nutzhöhe = Papierhöhe − Ränder − Kopf − Fuss).
- **Kein** Headless-Messen, **kein** DOM-Nachbau.
- Vorteile: Seite-1-Sonderheader, odd/even, odd/even-Kopf, exakte Platzierung, wiederholte Grid-/Gruppenköpfe, Seitenzahlen inkl. Gesamtseiten **im selben Durchlauf** (Seitenzahl ist Folge der Berechnung).
- Regeln:
  - Bänder ohne `keepTogether` dürfen geteilt werden; mit `keepTogether` wird auf die nächste Seite geschoben.
  - Übersteigt ein Band die Nutzhöhe, ist es ein Definitionsfehler (Exception) oder – optional – ein Auto-Shrink-Hook.
  - Dynamische Textlängen sind Aufgabe des Entwicklers (fixe Höhe bzw. Overflow-Handling).

### 10.4 Seitenzahlen und Platzhalter

- **Flow:** native Chromium-Header/Footer.
- **Strict:** Platzhalter werden nach der (in PHP berechneten) Pagination ersetzt; Gesamtseiten sind bereits bekannt.
- Optional ein zweiter Rendering-Durchlauf, falls ein Platzhalter von der finalen Seitenzahl abhängt und im Inhalt steht.

### 10.5 Wahl des Modus

- **Flow** = einfache Listen, schnelle Vorschau, HTML, excel-artige Reports.
- **Strict** = Rechnungen, Einzahlungsscheine, alles mit Kopf-/Fusszeilen mit echtem Inhalt, Seite-1-Sonderfällen, festen Feldern und wiederholten Gruppenköpfen.
- Default aus Config; pro Report überschreibbar.

---

## 11. UI-Integration (optional)

- `php artisan reports:install --stack=...` veröffentlicht Config, Migrationen, Views und **Stack-Stubs**.
- Stubs enthalten:
  - **Filterformular** – Generatoren je Feldtyp.
  - **Display-Frame** – umschliesst den HTML-Output mit Buttons (PDF, Drucken, Speichern, Export).
- Es gibt **keine** Stack-Abhängigkeit für die Report-Erzeugung; ein Host ohne Stubs ruft einfach `Reports::render()`/`::run()` auf.
- Die Stubs liefern ihr Verhalten über ein gemeinsames Schema (Abschnitt 8.3).

---

## 12. Persistenz / DB-Schema

Migrationen des Pakets (Präfix konfigurierbar):

- `report_presets`
  - `id`, `user_id` (nullable für global), `report_key`, `name`, `is_shared`, `is_global`, `payload` (json: filters, sorts), `timestamps`
  - Regeln: `is_global` nicht durch User löschbar; `is_shared` für alle sichtbar.
- `report_outputs` (Archiv, optional)
  - `id`, `user_id`, `report_key`, `format`, `disk`, `path`, `parameters` (json), `timestamps`
- *Später:* `report_runs` (History/Scheduling) analog zu Ideen aus dem Umfeld.

User-Modell über Config; die Migration nutzt keinen festen Klassennamen.

---

## 13. Konfiguration (`config/reports.php`)

```php
return [
    'path'        => resource_path('reports'),   // Basis-Pfad der Definitionen
    'namespace'   => 'App\\Reports',             // PSR-4 der Report-Klassen
    'source_namespace' => 'App\\Reports\\Sources',

    'driver'      => 'browsershot',              // spatie/laravel-pdf
    'default_mode'=> 'flow',                     // flow | strict

    'paper' => [
        'size' => 'a4', 'orientation' => 'portrait',
        'unit' => 'mm', 'margins' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
        'dpi' => 96,
    ],

    'disk' => 'local',
    'archive_path' => 'reports/archive',

    'user_model' => App\Models\User::class,

    'permissions' => [
        'manage_global_presets' => null,          // Gate-Name oder null = jeder
    ],

    'stubs' => [
        'frontend' => null,                        // blade-livewire | inertia-vue | inertia-react
    ],

    'pdf' => [
        'options' => ['print_background' => true],
    ],

    'exports' => [
        'word'  => null,                           // v2: PHPWord
        'excel' => null,                           // v2: PhpSpreadsheet
    ],
];
```

---

## 14. Sicherheit und Qualität

- **Trust-Boundary:** Quellen und Felder sind vom Entwickler registriert; Filter werden gegen die Allow-List validiert, Werte gebunden (kein String-basiertes SQL).
- **Escaping:** Blade-Views escapen standardmässig; `{!! !!}` bleibt in Entwicklerverantwortung.
- **Rechte:** Preset-Rechte über Gate; optional Multi-Tenant-Scope (später).
- **Tests:** Orchestra Testbench + Pest (Unit: Pagination, Aggregate, Filter-Mapping, Group-Resolver; Feature: Render-Snapshots HTML, PDF-Smoke optional ohne Browser).
- **Qualität:** Larastan/PHPStan, Pint, Rector, Peck analog `guggach/laravel-image-manager`.

---

## 15. Paketstruktur

```
laravel-reports/
├─ config/reports.php
├─ database/migrations/
├─ resources/
│  ├─ views/            (Default-Layout, Default-Bänder, Frame, Filter-Views)
│  └─ stubs/            (blade-livewire | inertia-vue | inertia-react)
├─ routes/              (optionale Preview-/Download-Routen)
├─ src/
│  ├─ ReportsServiceProvider.php
│  ├─ Facades/Reports.php
│  ├─ Report.php
│  ├─ Sources/          (ReportSource, ReportField, ReportParameter)
│  ├─ Definition/       (DTO, Builder, Bands, PageSetup, ReportMode)
│  ├─ Layouts/          (Layout, LayoutRegistry)
│  ├─ Engine/           (ReportEngine, GroupResolver, AggregateResolver, Paginator, RenderContext)
│  ├─ Aggregates/
│  ├─ Filters/          (FilterBag, FilterField, PresetRepository)
│  ├─ Contracts/        (Renderer, Exporter, Aggregate)
│  ├─ Renderers/        (HtmlRenderer, PdfRenderer)
│  ├─ Export/           (v2: Word/Excel/Csv)
│  └─ Console/          (make:report, make:report-source, reports:install, reports:list)
├─ tests/
├─ composer.json
└─ docs/
```

---

## 16. Roadmap

**v1 – MVP**
- `ReportSource`, `ReportField`, `ReportParameter` (Eloquent/Query-Builder)
- `Report`-Basisklasse + `ReportBuilder` + DTO
- **Flow**-Modus, Blade-Bänder, Grid-Header via `<thead>`
- Page Header/Footer (simple), Seitenzahlen via Chromium
- Filter (Delegation + einfaches Blade-Formular), Sortierung
- Presets (DB: privat/geteilt/global)
- Output: HTML + PDF (inkl. still/archivierend)
- **Layouts**: einbindbares Default-Layout (`letterhead`) + `make:layout`
- **Klassischer Spaltenreport** (`column-list`) als schnelle Reportart
- `make:report --template=column-list|basic-list|blank`, `reports:install --stack=blade-livewire`

**v1.1 – Layout-Tiefe**
- **Strict**-Modus mit arithmetischer Pagination
- Verschachtelte Gruppen-Bänder, Aggregate mit Scope
- Page-Header-Sonderfälle (Seite 1, odd/even)
- Vorlagen `banded-list`, `invoice`

**v2 – Ausbau**
- Word (PHPWord) + Excel (PhpSpreadsheet) + CSV
- JSON-Loader aufs DTO
- Raw-SQL-/`db-temporal`-Adapter
- Inertia-Vue/React-Stubs, Run-History, Scheduling, Multi-Tenant

---

## 17. Offene Entscheidungen

Noch zu bestätigen (im Entwurf mit diesen Defaults angenommen):

1. **Filtertypen** – obige Tabelle inkl. `date/datetime`, `enum`, `relation`, `json` und NULL-Operatoren ist gesetzt? (Default: ja)
2. **Wo die Query definiert wird** – im Report/`ReportSource` (Default) oder übergibt die Host-App die Collection? (Default: Report definiert Query)
3. **Aggregat-Scope** – Scope `report` + `group:1..n`, Running-Semantik wie in 7.3? (Default: ja)
4. **Admin-Presets** – `is_global` + konfigurierbares Gate statt harter Permission-Abhängigkeit? (Default: ja)

Weitere offene Punkte:
- Paketname final `guggach/laravel-reports`, Namespace `Guggach\Reports`?
- Formal­ität der Strict-Überlauf-Politik: Exception vs. Auto-Shrink?
- Ob die Free-form-Stufe (L1) bereits in v1 enthalten sein soll.
- Lizenzhinweis PHPWord (LGPL-3.0) für v2-Doku.
- Layout-Umfang: ein einbindbares Layout pro Report (Default) oder zusätzlich überschreibbare Layout-Zonen pro Band/Seite?
