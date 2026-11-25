# Simsalabit

Website of Simsalabit, websites with a bit of magic (Zürich). Live at https://simsalabit.ch (hosted by Infomaniak).

- `src/page.html` is the source of the whole site (German default, English switch); `python3 src/build.py` builds `index.html` from it.
- `assets/` holds the pictures, the 3D bee, the fonts and three.js. Everything the page needs is on the site itself: no other servers.
- `anfrage.php` receives the contact form and emails it to magic@simsalabit.ch. Its settings are in `anfrage-config.php` (see below).
- See `CLAUDE.md` for the full guide.

## Publishing on Infomaniak

The site needs Infomaniak **Web Hosting** (the paid plan with PHP). The free *Starter* hosting that comes with a domain
serves plain HTML only, so the contact form could not send mail there.

1. Build and pack:
   ```
   python3 src/build.py
   python3 src/pack.py
   ```
   This writes `simsalabit-upload.zip` with everything the public site needs (`index.html`, `anfrage.php`, `.htaccess`,
   the icons, `robots.txt`, `sitemap.xml`, `assets/`). The page source and these notes stay out.
2. Upload the **contents** of the zip into the site's root folder on Infomaniak (Manager > Web Hosting > the site > File manager,
   or by FTP). `index.html` and `.htaccess` must sit directly in that folder. Delete old files there first if the folder is not empty.
3. In `anfrage-config.php` on the server, enter the password of the mailbox `magic@simsalabit.ch` in `smtp_pass`
   (Manager > Mail service). The form then sends through Infomaniak's mail server, logged in as that mailbox, which is what
   Infomaniak recommends. Without a password the form uses PHP `mail()`, which Infomaniak switches off by default: it must be
   switched on for the site in the Manager, together with a sender address of the domain.
4. Make sure the site has its SSL certificate (Infomaniak installs Let's Encrypt automatically) and that `simsalabit.ch` points
   to the hosting. `.htaccess` sends every visitor to `https://simsalabit.ch/`.
5. Check on the live site: open it on a phone and a computer, scroll the whole page and watch the bee, switch to English,
   send a test request through the form and look for the mail in the mailbox. Share the link in WhatsApp or iMessage once
   to see the preview picture.

## Working on the site

```
python3 src/build.py            # src/page.html -> index.html
python3 -m http.server 8800     # then open http://localhost:8800/
php -S localhost:8801           # the same, but the contact form works too (needs php)
```
Commit the source and the built `index.html` together. `anfrage-config.php` (holds the password) and the zip are not in Git.
