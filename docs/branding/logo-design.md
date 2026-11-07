# ScholarMatch logo

Approved design: **Scholar Path**, selected on 7 November 2026.

The open book represents education. The rising path through the centre represents guidance toward future opportunities.

- Approved asset: [scholarmatch-logo.png](../../public/images/scholarmatch-logo.png)
- Format: PNG with a white background.
- Generated using the built-in image generation tool.
- The approved image is preserved exactly as generated.

## Website placement

- The public Home page has a responsive header with the horizontal book-and-name logo, a Home link, and Login/Get Started actions for guests or Dashboard for signed-in users. The About page and its navigation links have been removed.
- The logo links to Home on public pages. A compact version also appears in the public footer and the student desktop/mobile sidebar headers.
- The account navigation uses the same compact brand component.
- [scholarmatch-mark.svg](../../public/images/scholarmatch-mark.svg) is a lightweight vector adaptation of the approved blue book/path symbol for compact headers and the browser tab icon. Its paths remain readable at small sizes; the wordmark in horizontal headers is live text.
- All application layouts include the same SVG browser icon.
- Login and registration retain the approved stacked transparent PNG below.

## Authentication page asset

The login, registration, and other guest authentication pages use [scholarmatch-logo-blue.png](../../public/images/scholarmatch-logo-blue.png), prepared from the approved logo using the built-in image generation tool. Both book pages use the same blue, as requested on 7 November 2026. The background is transparent and the canvas is framed around the full symbol and wordmark. The guest layout displays it at a responsive width of 192–224 pixels. The earlier [blue-and-indigo transparent version](../../public/images/scholarmatch-logo-transparent.png) is retained as a prior version.

### Blue recoloring prompt

```text
Use case: precise-object-edit
Asset type: transparent ScholarMatch logo for the existing login and registration pages.
Input image 1: edit target, the current transparent ScholarMatch logo.
Primary request: Change ONLY the purple/indigo right-hand book page to the exact same deep blue used on the left-hand book page. The completed logo should have a matching blue left page and right page. Sample the existing left page blue and apply that same color consistently to the right page.
Invariants: Keep the original book silhouette, central rising path cutout, lower page strokes, wordmark "ScholarMatch", typography, spacing, proportions, canvas dimensions and framing, antialiasing, and all existing transparency exactly as in the input. Keep the left page, bottom strokes and wordmark unchanged. The central path and background must remain genuinely transparent. Do not add any purple, new gradients, glow, shadows, borders, effects, or text. This is a single recoloring operation, not a redesign.
```

### Background extraction prompt

```text
Use case: background-extraction
Asset type: transparent PNG logo for an existing Laravel login page.
Input image 1: edit target, the user-approved ScholarMatch logo.
Primary request: Remove ONLY the white/off-white background surrounding the blue logo and wordmark and the white background visible in the path and page separations. Make those background areas genuinely transparent. Crop the canvas to closely contain the entire existing book/path symbol and "ScholarMatch" wordmark with a modest even transparent margin.
Invariants: Preserve the exact existing blue and indigo shapes, proportions, letterforms, typography, spacing, line details, and colors. Preserve the wordmark text exactly as "ScholarMatch". This is asset preparation, not a redesign. Both the open book symbol and full wordmark must be present, arranged exactly as in the input. Do not add, redraw, restyle, or rearrange anything. Do not introduce shadows, outlines, glow, new gradients, or decorations. Preserve antialiased edges and the original design. Output the complete logo against a truly transparent background, tightly framed.
```

## Generation prompt

```text
Use case: logo-brand
Asset type: concept preview for the ScholarMatch web application's logo.
Primary request: Develop the chosen "Scholar Path" concept: a simple open book with a rising path flowing through its centre, representing education, guidance, and access to scholarship opportunities. This is a scholarship recommendation platform for Malaysian students.
Style/medium: polished, flat, vector-friendly brand mark; professional and approachable; precise geometry with slightly softened corners.
Symbol: two clearly recognisable open-book pages. Integrate a single clear upward-moving path into the central negative space, gently ascending from the spine toward the top. The book and path must form one coherent, compact symbol. Keep the silhouette simple and legible at small sizes; very few shapes; consistent visual weight.
Color palette: existing brand deep blue #1E40AF as the dominant color; restrained indigo #4338CA accent if useful. White background.
Composition/framing: a clean, spacious, square concept presentation with a substantial centered symbol above the wordmark. Carefully balanced scale and spacing. Present ONE resolved logo concept, not a grid of alternatives or a mockup.
Text (verbatim): "ScholarMatch"
Typography: modern sans-serif comparable to Inter SemiBold; title case exactly ScholarMatch; excellent kerning; deep blue wordmark. No tagline or additional text.
Constraints: 2D, solid fills, clean crisp edges, no tiny decorative details, no gradients, no drop shadows, no 3D effects, no texture, no photographic surroundings.
Avoid: graduation cap, currency symbols, stars, laurels, shields, checkmarks, generic stock clip-art, visual clutter, watermarks.
```

