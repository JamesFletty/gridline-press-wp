# Gridline Press — custom WordPress theme

**Live demo:** [gridline-press-demo.vercel.app](https://gridline-press-demo.vercel.app) — a static render of the theme's templates with sample content.

A classic (PHP template) theme written from scratch for a fictional art and architecture publisher with a gallery. Stark Swiss modernism: white, black and one cobalt, exposed 2px grid rules, oversized poster type, an asymmetric catalogue index and generated geometric book covers. No parent theme, no starter theme, no page builder, no ACF, no jQuery.

## Architecture

```
gridline-press/
├── style.css                 Theme header only (Text Domain: gridline, Requires PHP: 8.1)
├── theme.json                v3: three-colour palette, self-hosted Inter Tight (variable), poster type scale
├── functions.php             Thin loader, requires each inc/ module
├── inc/
│   ├── setup.php             Theme supports, menus, image sizes, meta description, SVG favicon
│   ├── assets.php            filemtime() cache busting, defer strategy, font preload
│   ├── content-types.php     gp_book + gp_event CPTs, gp_imprint taxonomy, schema-driven meta boxes
│   ├── catalog.php           Whitelisted ?imprint= / ?sort= query vars → one WP_Query args builder, pre_get_posts
│   ├── rest.php              GET /wp-json/gridline/v1/catalog, transient-cached, version-key invalidation
│   ├── blocks.php            gridline/upcoming-events dynamic block (meta_query on event date)
│   ├── hardening.php         Emoji/RSD/wlwmanifest/generator removal, XML-RPC off, user REST hidden, headers
│   └── template-tags.php     Book facts, deterministic geometric cover generator, fallback menu
├── blocks/upcoming-events/   block.json (apiVersion 3), no-build editor.js (ServerSideRender + controls), editor.asset.php
├── template-parts/           site-header, site-footer, book-tile, catalog-index
├── front-page.php  archive-gp_book.php  single-gp_book.php  single-gp_event.php  page.php  index.php  404.php
└── assets/
    ├── css/                  base.css · layout.css (12-col grid, rules) · components.css
    ├── js/                   main.js (drawer) · catalog.js (REST-powered filter/sort, only where the index renders)
    └── fonts/                Inter Tight variable woff2, self-hosted
```

## Custom content model

- **`gp_book`**: archive at `/catalogue/`. Meta: author, **ISBN-13 (checksum-validated, so an invalid number is never saved)**, edition, page count, year, format, price.
- **`gp_event`**: gallery launches, talks and exhibitions. Meta: **event date (strict `Y-m-d`)**, time, venue, kind.
- **`gp_imprint`**: hierarchical taxonomy shared by both (Architecture, Image, Field Notes).
- One schema per post type (`gridline_meta_schema()`) drives `register_post_meta` (exposed to REST), a shared meta box renderer and one save handler. Saving checks the nonce, skips autosaves and revisions, checks `edit_post`, and sanitizes each field.

## Notable features

- **Catalogue filtering on the server.** `?imprint=` is checked against real terms and `?sort=` against a whitelist (newest, oldest, A–Z, thickest). `gridline_catalog_args()` turns them into `WP_Query` args (a `tax_query` plus `meta_value_num`/title ordering). The archive's `pre_get_posts`, the REST endpoint and the front-page index all call that same function.
- **Cached read-only REST endpoint** `GET /wp-json/gridline/v1/catalog?imprint=&sort=` returns published books only, with `Cache-Control: public, max-age=300`. Results sit in a transient whose key includes a "catalogue version" option. Saving or deleting a book, or editing an imprint, bumps the version, so stale lists never outlive an edit.
- **Progressive enhancement.** The controls are plain links, so the catalogue works without JS. `catalog.js` fetches the endpoint once, then filters, re-orders and renumbers the server-rendered rows in place. It updates the count in a live region and keeps the URL shareable. If the fetch fails, the links simply navigate.
- **`gridline/upcoming-events` dynamic block** (count + heading controls) queries events dated today or later in the site timezone (`meta_query` with `>=`, ordered by `meta_value`). The front page uses the same renderer. Past events drop off automatically.
- **Generated covers:** five Swiss-style patterns (modular circle, Müller-Brockmann bars, quarter-circle tiles, diagonal band on a dot field, offset squares) × four colourways, seeded from each book, so every title has its own stable cover until a real cover image is set.

## Install

1. Download this repository as a ZIP (**Code → Download ZIP**), or clone it into `wp-content/themes/`.
2. In WordPress, go to **Appearance → Themes → Add New → Upload**, choose the ZIP and activate the theme.
3. Add Books, Events and Imprints. Visit **Settings → Permalinks** once after activating so `/catalogue/` and the REST route are flushed.

## Demo content

Gridline Press is a fictional business created to demonstrate the theme. Names, addresses, phone numbers (555), email addresses (`.example`), books, ISBNs, prices and events are sample content only.
