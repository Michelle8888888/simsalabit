# Simsalabit website – guide for Claude Code

Michelle (Michaela Olsova, writes in English) builds the site of her own web design business **Simsalabit**
("Webseiten mit einem Hauch Magie", Zürich). The site is **German by default with an EN switch**. Swiss spelling: "ss", never "ß".

- Live site (branch `main`): https://simsalabit.netlify.app
- Free preview (PR `vorschau` → `main`, currently PR #2): https://deploy-preview-2--simsalabit.netlify.app (needs the Netlify login)
- Later: own domain simsalabit.ch on Infomaniak (not done yet).

## Repository layout
| Path | What |
|---|---|
| `src/page.html` | **The source of the whole page** (HTML, CSS, JS, German and English texts). Edit this. |
| `src/build.py` | `python3 src/build.py` → writes `index.html` (fills in picture paths, inlines `src/bee-still.webp`). Reproducible. |
| `index.html` | The built page. **Never hand-edit**, always rebuild. |
| `assets/` | Photo and flower pictures (`.webp`), `tpl/` (template flowers), `bee.glb` (compressed 3D bee, ~1 MB), `vendor/three/` (three.js 0.160, self-hosted, no CDN). |
| `_redirects` | Hides `src/`, `CLAUDE.md`, `README.md` on Netlify. |

## Workflow
1. Edit `src/page.html` → `python3 src/build.py` → check locally: `python3 -m http.server 8800` → http://localhost:8800/
   Check phones (360 to 430 px wide) and computers (1280 to 1920 px), in German and English, and scroll the whole page to watch the bee.
2. Commit to **`vorschau`** and push → the free Deploy Preview updates (no credits).
3. **Only when Michelle says a version is ready:** fast-forward `main` to `vorschau` and push = production deploy (costs 15 Netlify credits).
   Then open a new PR `vorschau` → `main`. Never push work in progress to `main`. GitHub: use `gh api` (REST).

## Rules (decided with Michelle, keep them)
- German texts say **"Webseite(n)"**, not "Website(s)". Hero line: "Ich gestalte Webseiten, die in Erinnerung bleiben" /
  "I create websites that people remember", in the logo serif, white with a purple glow, no full stop.
- **No sentence dashes (– or —) in visible texts** (they look AI-written). Use a comma or a colon.
- Prices (regular from 1 December 2026): Spark 2'500, Spell 4'500, Grand illusion 6'500 CHF. Pflegeplan ab CHF 90 pro Monat. Payment 80/20.
  Spell: "Logo- und Visitenkarten-Design in 5 Varianten". Grand illusion: whole brand identity (logo and business cards in 5 variants,
  colours, fonts, style guide) and a 30-day Instagram plan with 30 post designs (no Reels video).
- Impressum: Michaela Olsova, Albisriederstrasse 90, 8003 Zürich, magic@simsalabit.ch. **No legal form** (she is not an Einzelunternehmen yet).
  The phone number appears only in the Impressum.
- No list of target regions; only "Zürich, Schweiz" in the footer.
- Header: only the logo and the DE/EN switch, on a light band. Footer: title, "Schreiben Sie mir" button (mailto), brand,
  then one line with © 2026 Simsalabit, Impressum, Datenschutz.
- Ask before bigger design changes. Higgsfield (AI pictures and videos) costs credits: always ask first.

## The bee (handle with care)
- A 3D bee (three.js, GLTFLoader + meshopt) flies from stop to stop as the visitor scrolls. The route is the `STOPS` list in `src/page.html`.
  `findBloom` finds each flower's yellow centre. A still picture (`.bee-ph`) shows immediately and fades out when the 3D bee is ready (`bee-on`).
- Michelle's requirements: the bee lands **in the yellow centre**, alternates **left and right**, starts big in the hero and first lands on the hero flower,
  sits on the Spark, Spell and Grand illusion cards, stays a bit longer on the wisteria (`hold`), and moves smoothly. The page always starts at the top.
- After moving or resizing any flower, scroll the page and check that the bee still lands in its centre on phone and computer.
- The import map must stay **before** the `modulepreload` links (otherwise Chrome ignores it and the bee disappears).
- Parallax uses `data-speed`. The footer flower is measured from its resting place at the page bottom, so it never touches the button.

## Before launch (open points)
- Contact form (like the Bedacht site: PHP form handler on Infomaniak), today the button opens an e-mail.
- Move to Infomaniak under simsalabit.ch.
- Self-host the Google fonts (Poppins, Instrument Serif, JetBrains Mono), then delete the Google Fonts point in the Datenschutz text.
- Social preview picture (`og:image`, `og:title`) so shared links show a nice card.
