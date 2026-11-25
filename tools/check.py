# Quick check of the built site before pushing:  python3 tools/check.py
# Needs Playwright (pip install playwright && playwright install chromium), or set CHROME=/path/to/chrome.
# Screenshots go to tools/out/ (not committed). Exit code 1 if something is wrong.
import asyncio, os, pathlib, sys
from playwright.async_api import async_playwright

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
from serve import start

OUT = pathlib.Path(__file__).resolve().parent / 'out'
SIZES = [('phone', 390, 844), ('small-phone', 360, 740), ('laptop', 1440, 900)]
problems = []


def bad(msg):
    problems.append(msg); print('  PROBLEM:', msg)


async def check(browser, base, name, w, h, lang):
    pg = await browser.new_page(viewport={'width': w, 'height': h})
    errors, external = [], set()
    pg.on('pageerror', lambda e: errors.append(str(e)))
    pg.on('request', lambda r: external.add(r.url.split('?')[0]) if not r.url.startswith((base, 'data:', 'blob:')) else None)

    async def fake_netlify(route):  # the form posts to Netlify; locally we only pretend it worked
        if route.request.method == 'POST':
            await route.fulfill(status=200, body='ok')
        else:
            await route.continue_()
    await pg.route('**/*', fake_netlify)
    await pg.goto(base + '/index.html')
    await pg.wait_for_timeout(800)
    if lang == 'en':
        await pg.click('.lang button[data-lang="en"]')
    await pg.wait_for_timeout(3000)
    tag = f'{name}-{lang}'
    await pg.screenshot(path=str(OUT / f'{tag}-1-hero.png'))
    if not await pg.evaluate("document.getElementById('bee').classList.contains('ready')"):
        print('  note: the 3D bee did not start here (needs WebGL); check it in a real browser')
    fonts = await pg.evaluate("(async () => { await document.fonts.ready; return [...document.fonts].filter(f => f.status === 'loaded').length; })()")
    if fonts < 6:
        bad(f'{tag}: only {fonts} fonts loaded')

    # footer: the button must not touch the flower
    await pg.evaluate("scrollTo(0, document.body.scrollHeight)"); await pg.wait_for_timeout(1500)
    await pg.screenshot(path=str(OUT / f'{tag}-2-footer.png'))
    gap = await pg.evaluate("""(() => { const b = document.querySelector('.foot .btn-lilac').getBoundingClientRect(),
        f = document.querySelector('.foot .flower--up img').getBoundingClientRect(); return Math.round(f.top + f.height * .1 - b.bottom); })()""")
    if gap < -40:
        bad(f'{tag}: footer flower looks too close to the button (look at {tag}-2-footer.png)')

    # contact form: empty -> message, filled -> thank-you
    await pg.click('.foot .btn-lilac'); await pg.wait_for_timeout(400)
    await pg.click('.kf__send'); await pg.wait_for_timeout(200)
    if await pg.evaluate("document.querySelector('.kf__err').hidden"):
        bad(f'{tag}: empty form shows no message')
    await pg.fill('input[name=name]', 'Test Person'); await pg.fill('input[name=email]', 'test@example.ch')
    await pg.click('.kf__pk label:nth-of-type(2)')
    await pg.click('.kf__send'); await pg.wait_for_timeout(600)
    await pg.screenshot(path=str(OUT / f'{tag}-3-form-sent.png'))
    if await pg.evaluate("document.querySelector('.kf__done').hidden"):
        bad(f'{tag}: form did not show the thank-you')
    await pg.click('.kf__close'); await pg.wait_for_timeout(200)

    # legal texts in the right language, close button works
    for doc in ('impressum', 'datenschutz'):
        await pg.evaluate(f"location.hash = '{doc}'"); await pg.wait_for_timeout(250)
        want = f'doc-{doc}' + ('-en' if lang == 'en' else '')
        shown = await pg.evaluate("[...document.querySelectorAll('#legal article')].filter(a => !a.hidden).map(a => a.id).join()")
        if shown != want:
            bad(f'{tag}: #{doc} shows "{shown}" instead of "{want}"')
        await pg.click('.legal__x'); await pg.wait_for_timeout(200)
        if not await pg.evaluate("document.getElementById('legal').hidden"):
            bad(f'{tag}: the × does not close #{doc}')

    if errors:
        bad(f'{tag}: script errors: {errors}')
    if external:
        bad(f'{tag}: requests to other servers (fonts, CDNs ...): {sorted(external)}')
    await pg.close()


async def main():
    OUT.mkdir(exist_ok=True)
    base = start()
    async with async_playwright() as p:
        exe = os.environ.get('CHROME')
        browser = await p.chromium.launch(**({'executable_path': exe} if exe else {}), args=['--no-sandbox'])
        for name, w, h in SIZES:
            for lang in ('de', 'en'):
                print(f'{name} {w}x{h} {lang}')
                await check(browser, base, name, w, h, lang)
        await browser.close()
    print(f'\n{"All good." if not problems else str(len(problems)) + " problem(s)."} Screenshots: {OUT}')
    sys.exit(1 if problems else 0)

asyncio.run(main())
