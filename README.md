hosted on : https://upsun-deployment-xiwfmii-4hmmivhvcx5r4.fr-4.platformsh.site/about?locale=fr
# Symfony Portfolio

A small, localized Symfony portfolio website built with Symfony 7.4 and Twig.

This README provides quick setup, development, and maintenance notes specific to this repository.

## Requirements

- PHP 8.2+
- Composer
- (Optional) Symfony CLI for `symfony server:start`
- (Optional) Node.js & npm if you want to build local frontend assets

## Quick install

From the repository root:

```powershell
composer install
php bin/console cache:clear
# start dev server (prefer Symfony CLI when available)
symfony server:start
# or: php -S localhost:8000 -t public/
```

## Project structure highlights

- `public/` — web root and static assets (CSS/JS). CSS lives in `public/css/style.css`.
- `templates/portfolio/` — Twig templates (base layout and page templates).
- `src/Controller/PortfolioController.php` — main page actions and helper `renderTranslated()` used across pages.
- `src/EventSubscriber/LocaleSubscriber.php` — reads `?locale=xx` and sets request locale at runtime.
- `config/routes.yaml` — authoritative route declarations mapping to controller actions.
- `translations/` — `messages.fr.yaml` and `messages.en.yaml` for translations.
- `config/packages/framework.yaml` — contains `default_locale` and translator config.

## Localization

- The app uses `?locale=xx` to switch language at runtime (the `LocaleSubscriber` reads this query parameter).
- Translation files are under `translations/messages.*.yaml` and use the `messages` domain.
- Links and templates preserve the `locale` query parameter where appropriate.

## Theme & UI notes

- A simple dark/light theme toggle is implemented and persisted in `localStorage` under the key `site_theme`.
- CSS variables and a `.light-theme` class live in `public/css/style.css`.

## Common developer tasks

- Clear cache after template or config changes:

```powershell
php bin/console cache:clear
```

- Install node deps and build (optional, when working on local asset pipeline):

```powershell
npm install
# then run your preferred build tooling
```

## Where to change text, routes and page content

- Update translation strings in `translations/messages.fr.yaml` and `translations/messages.en.yaml`.
- Page templates are in `templates/portfolio/` (edit Twig templates to change layout or content).
- Routes live in `config/routes.yaml`; controller methods are in `src/Controller/PortfolioController.php`.

## Troubleshooting

- If translations or Twig changes don't appear, clear the cache: `php bin/console cache:clear` and hard-refresh the browser.
- If the site looks off in light mode, `public/css/style.css` contains theme overrides for Bootstrap utilities.

## Notes for contributors

- This project autowires services from the `App\\` namespace via `config/services.yaml`.
- Keep translation domain `messages` and the `page_title` variable consistent in Twig templates when adding new pages.

If you'd like, I can expand this README with deployment instructions, contribution guidelines, or add a short checklist for verifying translations and theme accessibility.
