# Simsalabit

Website of Simsalabit, websites with a bit of magic (Zurich).

- `index.html` is the whole site (German default, English switch), `assets/` holds the photo and the peony pictures.
- Live site: https://simsalabit.netlify.app

## How changes are published (saves Netlify credits)

1. All work goes to the branch **`vorschau`**. There is always an open pull request `vorschau` → `main`.
   Every push to `vorschau` updates its free Netlify Deploy Preview:
   `https://deploy-preview-<PR number>--simsalabit.netlify.app` (opening it needs the Netlify login).
   Deploy Previews cost no credits.
2. Only when Michelle says a version is ready: fast-forward `main` to `vorschau` and push.
   That is a production deploy and costs 15 Netlify credits. Then open a new pull request `vorschau` → `main`.
3. Never push work in progress to `main`.
