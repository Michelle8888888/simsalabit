# Simsalabit website – guide for Claude Code

Michelle (Michaela Olsova, writes in English) builds the site of her own web design business **Simsalabit**
("Webseiten mit einem Hauch Magie", Zürich). The site is **German by default with an EN switch**. Swiss spelling: "ss", never "ß".

- Live site (branch `main`): https://simsalabit.netlify.app
- Free preview: the open PR `vorschau` → `main` (PR #3 since 25 Nov 2026) → https://deploy-preview-3--simsalabit.netlify.app (needs the Netlify login).
  When a PR is merged by a production deploy, the next one gets a new number: update this line and `PUBLISHING.md`.
- Later: own domain simsalabit.ch on Infomaniak (not done yet).

## Repository layout
| Path | What |
|---|---|
| `src/page.html` | **The source of the whole page** (HTML, CSS, JS, German and English texts). Edit this. |
| `src/build.py` | `python3 src/build.py` → writes `index.html` (fills in picture paths, inlines `src/bee-still.webp`). Reproducible. |
| `index.html` | The built page. **Never hand-edit**, always rebuild. |
| `assets/` | Photo and flower pictures (`.webp`), `tpl/` (template flowers), `bee.glb` (compressed 3D bee, ~1 MB), `vendor/three/` (three.js 0.160, self-hosted, no CDN), `fonts/` (Poppins, Instrument Serif, JetBrains Mono as woff2, self-hosted, OFL licences), `og.jpg` (link preview picture). |
| `src/og.html` | Source of `assets/og.jpg` (1200 x 630): open it through the local server and take a 1200 x 630 screenshot (Playwright), save as JPEG. |
| `tools/check.py` | `python3 tools/check.py`: checks phone/small phone/laptop in DE and EN (no script errors, no outside requests, fonts, footer button vs flower, contact form, Impressum/Datenschutz in both languages) and saves screenshots to `tools/out/`. Needs Playwright (`pip install playwright && playwright install chromium`) or `CHROME=/path/to/chrome`. |
| `tools/og.py` | Rebuilds `assets/og.jpg` from `src/og.html`. `tools/serve.py` serves the site locally (`python3 tools/serve.py` → http://localhost:8800/). |
| `PUBLISHING.md` | Log of every production deploy (15 credits each). |
| `_redirects` | Hides `src/`, `tools/`, `CLAUDE.md`, `README.md` on Netlify. |

## Workflow
1. Edit `src/page.html` → `python3 src/build.py` → `python3 tools/check.py` → look at the screenshots in `tools/out/`.
   For the bee, open http://localhost:8800/ (`python3 tools/serve.py`) in a real browser and scroll the whole page, on a phone size and a computer size.
2. Commit to **`vorschau`** and push → the free Deploy Preview updates (no credits).
3. **Only when Michelle says a version is ready:** fast-forward `main` to `vorschau` and push = production deploy (costs 15 Netlify credits).
   Then open a new PR `vorschau` → `main` (it needs at least one new commit on `vorschau`) and log the deploy in `PUBLISHING.md`.
   Never push work in progress to `main`. GitHub: use `gh api` (REST).

## Copy concept (decided 25 Nov 2026, keep it)
- **Magic is the brand voice** (Simsalabit, "Webseiten mit einem Hauch Magie", Spark/Spell/Grand illusion, "KI ist mein Zauberstab", footer title).
  **Blooming is the client's result. The busy bee is the client.** Bee/bloom wording appears only in these places, don't add more and no "Nektar":
  - Hero title: "Webseiten, die Ihr Geschäft zum Blühen bringen" / "Websites that make your business flourish"
  - Hero subtitle: "Sie sind die fleissige Biene, ich übernehme die Online-Magie" / "You are the busy bee, I take care of the online magic"
  - 01: "Alles, was Ihr Geschäft zum Blühen bringt"; value Freiheit starts "Sie sind die fleissige Biene in Ihrem Geschäft."
  - Form thank-you: "Ihre Anfrage ist zu mir geflogen."
- Other titles: 02 "Ihre Webseite, ganz ohne Stress"; footer "Bereit für eine magische Webseite?" / "Ready for a magical website?"
- The site is for all small businesses, not only beauty studios (Spell: "für die meisten Geschäfte").
- "Online-Magie", "E-Mail", "Online-Buchung" keep their hyphen (German spelling); only sentence dashes are banned.

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

## Contact form
- The footer button "Schreiben Sie mir" and the "Spark/Spell/Grand illusion wählen" buttons open the form overlay `#anfrage` (the package buttons preselect their package).
- Fields: Name and E-Mail (required), Telefon, Adresse, Paket, Geschäft, Webseiten-Idee, plus hidden `sprache` (de/en). Honeypot `bot-field`.
- It posts to **Netlify Forms** (form name `anfrage`, free on credit plans). Netlify sends each request to magic@simsalabit.ch.
- When the site moves to Infomaniak, Netlify Forms stops working: add a PHP handler there (like the Bedacht site) and point the form at it.
- No external requests at all: fonts, three.js and pictures are all on the site. Keep it that way (the Datenschutz text says so).

## Impressum and Datenschutz
- Both exist in German (`#doc-impressum`, `#doc-datenschutz`) and English (`#doc-impressum-en`, `#doc-datenschutz-en`); the open one follows the language switch.
  The English versions carry the note that the German version applies. Change both languages together.
- The Datenschutz text already describes the **Infomaniak** setup Michelle decided on (hosting in Switzerland, form requests go only to her Infomaniak mailbox,
  nothing disclosed abroad, no Netlify). While the site still runs on Netlify, form entries actually go through Netlify Forms, so the move should not wait long.

## The bee (handle with care)
- A 3D bee (three.js, GLTFLoader + meshopt) flies from stop to stop as the visitor scrolls. The route is the `STOPS` list in `src/page.html`.
  `findBloom` finds each flower's yellow centre. A still picture (`.bee-ph`) shows immediately and fades out when the 3D bee is ready (`bee-on`).
- Michelle's requirements: the bee lands **in the yellow centre**, alternates **left and right**, starts big in the hero and first lands on the hero flower,
  sits on the Spark, Spell and Grand illusion cards, stays a bit longer on the wisteria (`hold`), and moves smoothly. The page always starts at the top.
- After moving or resizing any flower, scroll the page and check that the bee still lands in its centre on phone and computer.
- The import map must stay **before** the `modulepreload` links (otherwise Chrome ignores it and the bee disappears).
- Parallax uses `data-speed`. The footer flower is measured from its resting place at the page bottom, so it never touches the button.

## Before launch (open points)
- Move to Infomaniak under simsalabit.ch: then add the PHP form handler, and change `og:url` and `og:image` in `src/page.html` to https://simsalabit.ch/.
