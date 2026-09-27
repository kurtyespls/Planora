# Planora Images

## System logo

`planora-logo.png` is the single source of truth for the Planora brand mark — the
file you drop in and never touch again. Everything else here is generated from it.

### How it is used

| Asset | Size | Used by |
|---|---|---|
| `planora-logo.png` | up to 512px | the master; source for every derived file |
| `planora-logo-sm.png` | 128×128 | every `<img class="brand-logo">` (landing, auth, planner, profile, admin sidebar) and the `<link rel="icon">` / `<link rel="apple-touch-icon">` tags |
| `../favicon.png` | 64×64 | shortcut icon |
| `../favicon.ico` | 16/32/48 | legacy browsers, direct `/favicon.ico` requests |
| `../apple-touch-icon.png` | 180×180 | iOS home screen |

The tile styling lives in `public/css/planora-design.css` (`.brand-mark`,
`.brand-mark .brand-logo`).

### Replacing the logo

1. Drop your new image here as `planora-logo.png` (any format — PNG, JPG or WebP).
2. Run the asset generator:

   ```
   php tools/generate-brand-assets.php
   ```

   It converts the master to a real PNG, caps it at 512px (keeping the untouched
   original as `planora-logo-original.<ext>`), reports whether the background is
   transparent, then regenerates `planora-logo-sm.png`, `favicon.png`,
   `favicon.ico` and `apple-touch-icon.png`.

   A 422×410 master lands at 266 KB; the generated 128×128 tile is ~32 KB, which
   is what the pages actually download.

### Fallback behaviour

Every `<img class="brand-logo">` sits on top of the `⌁` glyph inside `.brand-mark`
and carries `onerror="this.remove()"`. If the tile file is missing, the teal tile
with the glyph is shown instead — no broken image icons, no layout shift.

## `dagupan/`

City photography used by the landing page, auth art panels and hotel cards.
See `dagupan/README.md`.

