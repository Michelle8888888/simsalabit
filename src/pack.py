# Packs everything the public site needs into simsalabit-upload.zip, ready for the upload to Infomaniak:
#   python3 src/build.py && python3 src/pack.py
# The zip holds index.html, anfrage.php with its settings file, .htaccess, the icons, robots.txt, sitemap.xml and assets/.
# The page source (src/), the notes (CLAUDE.md, README.md) and Git stay out.
import pathlib, zipfile
root = pathlib.Path(__file__).resolve().parent.parent
out = root / 'simsalabit-upload.zip'
files = ['index.html', 'anfrage.php', '.htaccess', 'favicon.ico', 'favicon.svg', 'apple-touch-icon.png', 'icon-192.png', 'icon-512.png',
         'site.webmanifest', 'robots.txt', 'sitemap.xml']
files += sorted(str(p.relative_to(root)) for p in (root / 'assets').rglob('*') if p.is_file())
cfg = root / 'anfrage-config.php'
if not cfg.is_file(): cfg = root / 'anfrage-config.example.php'   # the password is then filled in on the server
total = 0
with zipfile.ZipFile(out, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as z:
    for f in files:
        p = root / f
        if not p.is_file(): raise SystemExit(f'missing: {f} (run python3 src/build.py and python3 src/icons.py first)')
        z.write(p, f); total += p.stat().st_size
    z.write(cfg, 'anfrage-config.php'); total += cfg.stat().st_size
print(f'{out.name}: {len(files) + 1} files, {total / 1e6:.1f} MB unpacked, {out.stat().st_size / 1e6:.1f} MB zipped',
      '(settings from ' + cfg.name + ')')
