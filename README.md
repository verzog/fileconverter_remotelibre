# Remote LibreOffice document converter (fileconverter_remotelibre)

A Moodle **document converter** (`\core_files\converter`) that turns office
documents into PDF by posting them to a remote render service
([pptx_render_ynh](https://github.com/verzog/pptx_render_ynh)'s `/convert`
endpoint) instead of running LibreOffice/unoconv on the Moodle server.

Once configured as the site's document converter, features that rely on
doc→PDF conversion — notably the Assignment **"Annotate PDF"** feedback
(`assignfeedback_editpdf`) — offload the conversion to the remote box. (Moodle
still uses Ghostscript locally to rasterise the PDF for the annotation canvas;
this plugin only replaces the conversion step.)

## Requirements

- A reachable render service exposing `POST /convert` with bearer-token auth and
  returning `application/pdf` (see `pptx_render_ynh` ≥ 0.3.0).
- Moodle 4.1+.

## Install

1. Copy this directory to `files/converter/remotelibre` in your Moodle, or
   install the ZIP via *Site administration → Plugins → Install plugins*.
2. Complete the upgrade.
3. *Site administration → Plugins → Document converters → Remote LibreOffice* —
   set:
   - **Convert endpoint URL** — e.g. `https://render.example.org/render/convert`
   - **API key** — the render service's bearer token.
4. On *Document converters*, make sure **Remote LibreOffice** is enabled (and
   ordered ahead of unoconv if you want it preferred).

## Supported conversions

`pptx, ppt, odp, docx, doc, odt, rtf, txt, xlsx, xls, ods, csv, pdf → pdf`
(matching what the render service installs; a PDF is returned unchanged).

## How it works

`converter::start_document_conversion()` POSTs the source file's bytes to the
configured endpoint with `Authorization: Bearer <key>` and an `X-Filename`
header (so the service picks the right import filter), then stores the returned
PDF as the conversion's destination file. Conversions are synchronous, so
`poll_conversion_status()` only reports the already-set status.

## Privacy

To convert a document, its bytes are transmitted to the configured external
render service. To minimise what leaves Moodle, the request sends only a
**content hash plus the file extension** as the filename hint — never the real
filename (which can carry a student's name or the assignment title) — and adds
no separate Moodle user identifier. The document's own content may of course
still contain identifying information. The plugin stores no personal data in
Moodle itself; the transmission is declared in its privacy metadata.

Licensed GPL-3.0-or-later.
