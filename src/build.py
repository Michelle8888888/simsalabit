# Builds the website: python3 src/build.py  (src/page.html -> index.html)
import base64, pathlib
root = pathlib.Path(__file__).resolve().parent.parent
src = root / 'src'
s = (src / 'page.html').read_text()
# pictures are separate files in assets/, so they load in parallel and stay sharp
for k, f in (('__PHOTO__', 'michaela.webp'), ('__PEONY0__', 'peony0.webp'), ('__PEONY1__', 'peony1.webp'), ('__PEONY2__', 'peony2.webp'), ('__WISF__', 'wisteria-full.webp')):
    s = s.replace(k, 'assets/' + f)
# the still bee goes straight into the page so it shows up with the first byte
s = s.replace('__BEEPH__', 'data:image/webp;base64,' + base64.b64encode((src / 'bee-still.webp').read_bytes()).decode())
assert '__P' not in s and '__BEEPH__' not in s and '__WISF__' not in s
(root / 'index.html').write_text(s)
print('index.html', len(s), 'characters')
