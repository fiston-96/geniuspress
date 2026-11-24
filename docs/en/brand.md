# GeniusPress brand

## The symbol

**"The page under construction"**: three overlapping pages rising diagonally. The front page shows a layout (a banner and two blocks). The symbol says three things: we build pages, the site grows, and this is a page layout tool, not a simple document.

The symbol was designed not to resemble existing logos in the field. In particular, it avoids:
- the vertical bar followed by horizontal lines (Elementor);
- the rounded rising bars on a common baseline (Google Analytics, Power BI);
- the columns in a square (Trello).

A prior-art search in trademark databases (WIPO, EUIPO, OAPI, the DRC industrial property office) is still to be done before any filing.

## Files

All files are in `public/brand/`.

| File | Use |
|---|---|
| `geniuspress-mark.svg` | Color symbol, from 25 px |
| `geniuspress-mark-small.svg` | Simplified symbol for 24 px and below (favicon, small icons) |
| `geniuspress-mark-white.svg` | White symbol, on an indigo background or a dark photo |
| `geniuspress-mark-black.svg` | Single-color symbol, for black and white printing or engraving |
| `geniuspress-logo-light.svg` | Horizontal logo on a light background |
| `geniuspress-logo-dark.svg` | Horizontal logo on a dark background |
| `geniuspress-logo-stacked-light.svg`, `-dark.svg` | Stacked logo (symbol above the name) |
| `geniuspress-icon-192.png`, `-512.png` | App icons |
| `../favicon.svg`, `../favicon.ico`, `../apple-touch-icon.png` | Tab and home screen icons |

To regenerate the PNGs and the favicon after a change to the symbol:

```bash
php scripts/brand/generate-icons.php
```

In GeniusPress views, use the `x-logo` Blade component (parameters `size`, `wordmark`, `dark`). It automatically picks the simplified version at small sizes.

**To do**: the logos with the name use the Inter font as live text. For use outside the web (print, signage), a version with the text converted to outlines will be needed.

## Colors

| Name | Hex | Role |
|---|---|---|
| Indigo | `#4f46e5` | Main color, middle page, "Press" |
| Amber | `#f59e0b` | Front page: what is being built |
| Lavender | `#a5b4fc` | Back page; "Press" on a dark background |
| Night | `#0f172a` | "Genius" text, dark backgrounds |
| White | `#ffffff` | Front page content, light backgrounds |

## Typography

**Inter**, bold (700), with slight tightening (−2%). "Genius" is in Night and "Press" in Indigo. On a dark background: White and Lavender.

## Usage rules

- **Clear space**: leave an empty margin around the logo at least equal to the width of the front page (about 40% of the symbol's height).
- **Minimum sizes**: color symbol 25 px (below that, use the simplified version), horizontal logo 120 px wide.
- **Backgrounds**: the color symbol goes on white, light gray or Night. On indigo or on a photo, use the white version.

Do not:
- change the colors of the pages or their order;
- distort, slant or rotate the symbol;
- add shadows, gradients or outlines;
- write the name any other way than "GeniusPress" (not "Genius Press", "geniuspress" or "GENIUSPRESS");
- use the logo in a way that suggests a derivative project is the official project.

## License

GeniusPress code is licensed under AGPL-3.0, but **the name and logo are not covered by this license**. You may use them to talk about GeniusPress, to indicate that a site is "powered by GeniusPress", or for a compatible plugin. A derivative project (fork), however, must use a different name and a different logo.
