Project: Symfony portfolio (private)

Quick goal
- Help an AI agent make small, safe changes to this Symfony 7.4 portfolio app (localization, templates, routes, simple controllers, assets).

Essentials
- PHP: >=8.2 (see `composer.json`). Symfony packages pinned to 7.4.*.
- Routes: primary route file is `config/routes.yaml` (this repo also has route attributes in controllers but YAML is authoritative).
- Controllers: `src/Controller/PortfolioController.php` contains the main page actions and a `renderTranslated()` helper used across pages.
- Templates: `templates/portfolio/` (base layout: `templates/portfolio/base.html.twig`) — keep keys `page_title` and `locale` consistent.
- Services: `config/services.yaml` autowires/autoconfigures `App\` namespace (`src/`), so registering most services manually is unnecessary.

Localization & Translation patterns
- Default locale and translator config: `config/packages/framework.yaml` (`default_locale: 'fr'`, translator `default_path: translations`, `fallbacks: ['en']`).
- Runtime locale override: `?locale=xx` is used throughout. `src/EventSubscriber/LocaleSubscriber.php` reads `?locale` and sets the request locale (priority 20).
- Controllers call `TranslatorInterface->trans(..., 'messages', $locale)` and use translation keys like `home.title`, `home.intro`. Look at `translations/messages.fr.yaml` and `translations/messages.en.yaml`.

Routing & controller notes
- Routes are declared in `config/routes.yaml` mapping names to `App\Controller\PortfolioController::<action>`.
- Controller routes also include PHP 8 attributes, but changes to routing should be made in `config/routes.yaml` unless you intentionally prefer attribute-driven routing.

Assets & front-end
- Static files live in `public/` and `css/style.css`. `build/` contains added vendor bundles but templates load Bootstrap via CDN in `templates/portfolio/base.html.twig`.
- `package.json` lists `bootstrap` as a dependency if you need local asset builds; not required for small edits because templates use CDN.
- Composer `auto-scripts` run `cache:clear` and `assets:install` on install/update (see `composer.json`).

Developer commands (examples)
- Install deps: `composer install`
- Clear cache: `php bin/console cache:clear`
- Start dev server: `symfony server:start` (if Symfony CLI installed) or `php -S localhost:8000 -t public/`
- Install npm deps (optional for local build): `npm install`

Conventions & patterns to preserve
- Keep translation domain `messages` and the `page_title` variable in Twig templates.
- Respect `kernel.default_locale` and the `?locale` query param flow (see `LocaleSubscriber` and `PortfolioController::renderTranslated`).
- Services are autowired/autoconfigured. If you add a service that must implement an interface like `EventSubscriberInterface`, autoconfigure will register it automatically unless explicit config is needed.

Where to look when making changes
- Application entry: `public/index.php`
- Routing: `config/routes.yaml`
- Controllers: `src/Controller/PortfolioController.php`
- Event subscribers: `src/EventSubscriber/LocaleSubscriber.php`
- Services config: `config/services.yaml`
- Framework settings: `config/packages/framework.yaml`
- Templates: `templates/portfolio/*`
- Translation files: `translations/messages.*.yaml`

Examples
- To add a new translated page: create a controller action in `PortfolioController`, add a route in `config/routes.yaml`, create `templates/portfolio/<page>.html.twig`, and add keys in `translations/messages.fr.yaml` and `translations/messages.en.yaml`.

Safety & testing
- This repo has no test suite. After changes, run `php bin/console cache:clear` and open pages in a browser (or `curl`) to verify rendering and locale behavior.

If anything here is incorrect or you want the agent to follow stricter rules (commit style, PR templates, tests), tell me what to add.
