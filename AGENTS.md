# MovieREV

Laravel 13.34 on PHP 8.3 (`composer.json` requires `^8.3`; the box runs 8.3.33 ZTS). Single app, no packages/workspace. SQLite only — no MySQL/Postgres driver is configured or tested against.

`AGENTS.md` and `CLAUDE.md` are byte-identical stubs that shipped with the Laravel skeleton and contain nothing but "install Laravel Boost" boilerplate. Boost is **not** installed (`vendor/laravel/boost` absent, not in `composer.json`). Ignore that boilerplate; if you ever do install Boost, `php artisan boost:install` overwrites both files, so merge rather than clobber.

## Current state

Skeleton only. Real product code is one landing page: `resources/views/welcome.blade.php` + `resources/css/app.css`. `routes/web.php` is a single closure returning `view('welcome')`. No domain models, migrations, or controllers beyond the stock `User`.

- `welcome.blade.php` uses hand-written semantic classes (`.movie-app`, `.header`, `.main-container`, `.sidebar`, `.movie-list`). It is **not** styled yet — `app.css` still ends with `/* ...the rest of the CSS... */`.
- Tailwind v4 is wired into `vite.config.js` via `@tailwindcss/vite`, but `app.css` currently has no `@import 'tailwindcss'`, so **no Tailwind utilities compile**. The build output is the ~90 bytes of hand-written CSS. Don't write utility classes expecting them to work; add the `@import` first or extend the hand-written stylesheet.

## Commands

```sh
composer dev              # php artisan dev -> serve + queue:listen + vite, concurrently
php artisan dev:list      # show the 3 registered dev processes
composer test             # config:clear, then php artisan test
npm run build             # required before tests see any @vite asset (see below)
vendor/bin/pint           # formatter; no pint.json, so the default "laravel" preset
```

Single test / filtered run: `php artisan test --filter=the_application_returns_a_successful_response`, or `php artisan test tests/Feature`.

No CI. No pre-commit hooks. No phpstan/rector config despite `laravel/pao` supporting them.

## Gotchas

**`php artisan test` returns JSON, not human output.** `laravel/pao` is a dev dependency; when it detects an agent (it checks `OPENCODE`, `OPENCODE_CLIENT`, `CURSOR_AGENT`, `CLAUDECODE`, `CODEX_*`, ... in the environment) it rebinds `OutputStyle` and strips ANSI from every artisan command. Expect `{"tool":"phpunit","result":"failed","tests":2,...}` with a `failures[].message` holding the stack trace. Use `PAO_DISABLE=true` to get the normal PHPUnit table when reading output by eye; `PAO_FORCE=true` forces JSON when the env isn't detected.

**Feature tests fail on a fresh clone with `ViteManifestNotFoundException`.** `welcome.blade.php` calls `@vite`, and `public/build` is gitignored. `tests/Feature/ExampleTest.php` GETs `/`, so it 500s until someone runs `npm run build`. Run `npm run build` before trusting a test failure.

`vendor/bin/pint --test` currently fails on `routes/web.php` (missing trailing newline) — pre-existing, not something you introduced.

`.npmrc` sets `ignore-scripts=true`, so `npm install` will not run lifecycle scripts.

`npm run build` prints a warning that font `optimizedFallbacks` needs the optional `fontaine` package. Harmless; the build succeeds and self-hosts Instrument Sans 400/500/600.

## Environment

`.env` (gitignored) is already provisioned: `APP_KEY` set, `APP_DEBUG=true`. Dev drivers are all `database` — `SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` — so `php artisan migrate` is required before any page renders; stock migrations cover `users`, `cache`, and `jobs`. `database/database.sqlite` exists and is already migrated.

`phpunit.xml` overrides the environment for tests: in-memory SQLite (`:memory:`), `array` session/cache, `sync` queue, `file` maintenance. No migrations run automatically — add `RefreshDatabase` per test as needed.

Mail and broadcast are set to `log`/`null`. Nothing leaves the machine.

## Conventions

`.editorconfig` and `.gitattributes` enforce LF endings, 4-space indent, final newline, and trimmed trailing whitespace. `.gitattributes` sets per-type diff drivers (`.blade.php` as html, `.css` as css, `.php` as php), so read the rendered diff, not raw lines.

Routing is registered in `bootstrap/app.php` via `withRouting(web: ...)`, not in a separate `RouteServiceProvider`. Middleware and exception config go in the `withMiddleware` / `withExceptions` closures there.

No `.editorconfig`-adjacent formatter config for JS or Blade — there is no Prettier or Blade formatter. Match surrounding Blade indentation (4 spaces, blank line after each block tag) by hand.

## Git

Branch `main`, single commit, remote `origin` -> `github.com/Wincie-SND/MovieREV.git`. `package-lock.json` is untracked (not gitignored) while `composer.lock` is committed, so npm installs resolve fresh.