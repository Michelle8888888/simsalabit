# Simsalabit website – guide for Claude Code

Michelle (Michaela Olsova, writes in English) builds the site of her own web design business **Simsalabit**
("Webseiten mit einem Hauch Magie", Zürich). The site is **German by default with an EN switch**. Swiss spelling: "ss", never "ß".

- Live site: https://simsalabit.ch, hosted by **Infomaniak** (Web Hosting with PHP). Netlify was only used for previews while
  designing and is gone: nothing on the site refers to it any more.
- The only outside parties are Infomaniak (hosting and mailbox) and Claude (building the site). The page loads nothing from other servers.

## Repository layout
| Path | What |
|---|---|
| `src/page.html` | **The source of the whole page** (HTML, CSS, JS, German and English texts). Edit this. |
| `src/build.py` | `python3 src/build.py` → writes `index.html` (fills in picture paths, inlines `src/bee-still.webp`). Reproducible. |
| `index.html` | The built page. **Never hand-edit**, always rebuild. |
| `assets/` | Photo and flower pictures (`.webp`), `tpl/` (template flowers), `bee.glb` (compressed 3D bee, ~1 MB), `vendor/three/` (three.js 0.160, self-hosted, no CDN), `fonts/` (Poppins, Instrument Serif, JetBrains Mono as woff2, self-hosted, OFL licences), `og.jpg` (link preview picture). |
| `src/og.html` | Source of `assets/og.jpg` (1200 x 630): open it through the local server and take a 1200 x 630 screenshot (Playwright), save as JPEG. |
| `anfrage.php` | Receives the contact form and emails it to Michelle (see Contact form). `anfrage-config.example.php` is the template of its settings file `anfrage-config.php` (holds the mailbox password, not in Git). |
| `.htaccess` | Infomaniak (Apache): https and no www, hides `src/`, the notes and the form settings, file types, 30-day caching of assets, compression. |
| `favicon.ico`, `favicon.svg`, `apple-touch-icon.png`, `icon-192.png`, `icon-512.png`, `site.webmanifest` | The icons (sparkle on dark), made from `favicon.svg` by `python3 src/icons.py` (Playwright + Pillow). |
| `robots.txt`, `sitemap.xml` | For search engines; the sitemap names https://simsalabit.ch/. |
| `assets/signatur/` | `michaela.jpg` (300 px) and `logo.png` (600 px, transparent, rendered from the site's logo) for Michelle's email signature; `src/signatur.html` is the signature itself (open in a browser, copy, paste into Infomaniak Mail). |
| `src/pack.py` | `python3 src/pack.py` → `simsalabit-upload.zip` with everything the public site needs (not in Git). |

## Workflow
1. Edit `src/page.html` → `python3 src/build.py` → check locally: `python3 -m http.server 8800` → http://localhost:8800/
   (or `php -S localhost:8801` to try the form as well; mails then go to the local sendmail, or set `sendmail_path` to a script).
   Check phones (360 to 430 px wide) and computers (1280 to 1920 px), in German and English, and scroll the whole page to watch the bee.
2. Commit source and built `index.html` together and push to the working branch.
3. Publishing is a manual upload to Infomaniak (see README.md): `python3 src/pack.py` → upload the contents of `simsalabit-upload.zip`
   into the site's root folder. Only when Michelle says a version is ready. The browser keeps pictures, fonts and the bee for 30 days
   (`.htaccess`), so a changed picture needs a new file name.

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
- **The animations play for everyone**, also with "Reduce motion" switched on in the phone settings (Michelle's decision):
  no `prefers-reduced-motion` rules anywhere, and the bee does not check it either.

## Contact form
- The footer button "Schreiben Sie mir" and the "Spark/Spell/Grand illusion wählen" buttons open the form overlay `#anfrage` (the package buttons preselect their package).
- Fields: Name and E-Mail (required), Telefon, Adresse, Paket, Geschäft, Webseiten-Idee, plus hidden `sprache` (de/en) and `zeit`
  (when the form was opened). Honeypot `bot-field`.
- The page posts it with `fetch` to **`anfrage.php`** (same folder) and expects JSON `{ok: true}`; on failure it shows the mailto fallback.
  `anfrage.php` cleans every field (valid UTF-8, no control characters, length limits), drops forms with a filled honeypot silently,
  and emails the rest as plain text to magic@simsalabit.ch with the visitor as Reply-To. A form sent within 3 seconds of opening is
  still delivered, with "[Bitte prüfen]" in the subject. Nothing is stored on the server.
- Sending: with `smtp_pass` in `anfrage-config.php` it logs in at mail.infomaniak.com (port 465 TLS, or 587 STARTTLS) as that mailbox,
  the way Infomaniak recommends; without a password it falls back to PHP `mail()`, which Infomaniak keeps off unless switched on in the
  Manager for the site (with a sender address of the domain). Errors go to the PHP error log, visitors only see a generic message.
- Local test: `php -S localhost:8801 -d sendmail_path=/path/to/script` and post the form; a fake SMTP server works too with
  `'smtp_verify' => false` in a temporary `anfrage-config.php` (port 465 = TLS from the start, any other port = STARTTLS).
- No external requests at all: fonts, three.js and pictures are all on the site. Keep it that way (the Datenschutz text says so).

## Impressum and Datenschutz
- Both exist in German (`#doc-impressum`, `#doc-datenschutz`) and English (`#doc-impressum-en`, `#doc-datenschutz-en`); the open one follows the language switch.
  The English versions carry the note that the German version applies. Change both languages together.
- The Datenschutz text describes the **Infomaniak** setup: hosting and mailbox in Switzerland, Infomaniak named as the processor with a link to
  its Datenschutzrichtlinien (DE: infomaniak.com/de/agb/datenschutzrichtlinien, EN: infomaniak.com/en/legal/confidentiality-policy),
  form entries emailed straight to her mailbox and not stored on the server, nothing disclosed abroad, no other providers. Keep it true.

## The bee (handle with care)
- A 3D bee (three.js, GLTFLoader + meshopt) flies from stop to stop as the visitor scrolls. The route is the `STOPS` list in `src/page.html`.
  `findBloom` finds each flower's yellow centre. A still picture (`.bee-ph`) shows immediately and fades out when the 3D bee is ready (`bee-on`).
- Michelle's requirements: the bee lands **in the yellow centre**, alternates **left and right**, starts big in the hero and first lands on the hero flower,
  sits on the Spark, Spell and Grand illusion cards, stays a bit longer on the wisteria (`hold`), and moves smoothly. The page always starts at the top.
- After moving or resizing any flower, scroll the page and check that the bee still lands in its centre on phone and computer.
- The import map must stay **before** the `modulepreload` links (otherwise Chrome ignores it and the bee disappears).
- Parallax uses `data-speed`. The footer flower is measured from its resting place at the page bottom, so it never touches the button.

## Review slider (04)
- The reviews scroll sideways natively (`overflow-x: auto`, `scroll-snap-type: x mandatory`, one review per swipe); the script only follows
  the scroll position and moves the box for the dots, the arrow keys, a mouse drag and a short touch swipe. **Never move the cards with a
  transform again:** browsers that translate the page for the visitor (Chrome, Safari) only translate text that scrolls into view, and
  cards shifted by a transform inside a clipped box stayed untranslated (Michelle's mother in Slovakia saw German reviews on a translated page).

## Launch on Infomaniak (what Michelle still does herself)
- Book Infomaniak **Web Hosting** (PHP) for simsalabit.ch; the free Starter hosting cannot run `anfrage.php`.
- Upload the contents of `simsalabit-upload.zip` into the site's root folder, enter the mailbox password in `anfrage-config.php` there.
- Check that the certificate is active, then on the live site: the bee on phone and computer, DE and EN, a test request through the form,
  and the link preview (share the link once in WhatsApp or iMessage). `og:url`, `og:image`, the canonical link and the sitemap already say
  https://simsalabit.ch/.
