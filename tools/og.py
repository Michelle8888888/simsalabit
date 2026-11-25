# Rebuilds the link preview picture assets/og.jpg (1200 x 630) from src/og.html:  python3 tools/og.py
# Needs Playwright and Pillow (or set CHROME=/path/to/chrome).
import asyncio, os, pathlib, sys
from playwright.async_api import async_playwright
from PIL import Image

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
from serve import start, ROOT


async def main():
    base = start()
    async with async_playwright() as p:
        exe = os.environ.get('CHROME')
        b = await p.chromium.launch(**({'executable_path': exe} if exe else {}), args=['--no-sandbox'])
        pg = await b.new_page(viewport={'width': 1200, 'height': 630})
        await pg.goto(base + '/src/og.html'); await pg.wait_for_selector('body[data-ready]', state='attached', timeout=15000)
        await pg.evaluate('document.fonts.ready'); await pg.wait_for_timeout(400)
        png = ROOT / 'tools' / 'og.png'
        await pg.screenshot(path=str(png)); await b.close()
    Image.open(png).convert('RGB').save(ROOT / 'assets' / 'og.jpg', quality=86, optimize=True, progressive=True)
    png.unlink()
    print('assets/og.jpg written')

asyncio.run(main())
