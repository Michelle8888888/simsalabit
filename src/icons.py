# Makes the icon files from favicon.svg:  python3 src/icons.py
#   favicon.ico (16, 32 and 48 px, for older browsers and Google), apple-touch-icon.png (180 px, iPhone home screen),
#   icon-192.png and icon-512.png (Android, listed in site.webmanifest).
# Needs node with Playwright (renders the SVG exactly like a browser) and Pillow (packs the .ico).
import pathlib, subprocess, tempfile
from PIL import Image
root = pathlib.Path(__file__).resolve().parent.parent
JS = r'''
const { chromium } = require('playwright');
const fs = require('fs');
const [,, svgPath, outDir] = process.argv;
const svg = fs.readFileSync(svgPath, 'utf8');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  // the iPhone rounds the corners itself, so its icon fills the whole square with the dark background
  for (const [name, size, fill] of [['i16', 16, 0], ['i32', 32, 0], ['i48', 48, 0], ['icon-192', 192, 0], ['icon-512', 512, 0], ['apple-touch-icon', 180, 1]]) {
    await page.setViewportSize({ width: size, height: size });
    await page.setContent(`<!DOCTYPE html><html><body style="margin:0;background:${fill ? '#15141A' : 'transparent'}">${svg.replace('<svg ', `<svg width="${size}" height="${size}" style="display:block" `)}</body></html>`);
    await page.screenshot({ path: `${outDir}/${name}.png`, omitBackground: !fill });
  }
  await browser.close();
})();
'''
with tempfile.TemporaryDirectory() as tmp:
    js = pathlib.Path(tmp) / 'icons.js'; js.write_text(JS)
    subprocess.run(['node', str(js), str(root / 'favicon.svg'), tmp], check=True)
    for n in ('icon-192', 'icon-512', 'apple-touch-icon'):
        (root / f'{n}.png').write_bytes((pathlib.Path(tmp) / f'{n}.png').read_bytes())
    ims = [Image.open(pathlib.Path(tmp) / f'i{s}.png').convert('RGBA') for s in (16, 32, 48)]
    # Pillow keeps only sizes up to the first image, so the 48 px one leads
    ims[-1].save(root / 'favicon.ico', format='ICO', sizes=[(16, 16), (32, 32), (48, 48)], append_images=ims[:-1])
for n in ('favicon.ico', 'apple-touch-icon.png', 'icon-192.png', 'icon-512.png'):
    print(n, (root / n).stat().st_size, 'bytes')
