# MovieREV

Laravel 13.34 on PHP `^8.3`. Single app, no packages/workspace. `main` only — no branches, no PRs, no CI.

## What the app actually is

A single static landing page. `routes/web.php` is one closure returning `view('welcome')`. No models, migrations, controllers, or forms beyond the stock `User`. Don't invent architecture around it.

`resources/views/welcome.blade.php` is a **Figma export**, not hand-written markup:

- Fixed 1920x1080 canvas of absolutely positioned divs (`min-w-[1920px] h-[1080px]`). Pixel values are scaled 1.5x from a 1280x720 source. Don't "fix" the magic numbers.
- Styling comes from the **Tailwind CDN** (`https://cdn.tailwindcss.com`) with an inline `tailwind.config` mapping design tokens to CSS variables. **This bypasses Vite entirely — the view no longer calls `@vite`.** Local `vite.config.js` / `resources/css/app.css` / `resources/js/app.js` are dead weight for the page; the served stylesheets are static files in `public/`.
- `corePlugins: { preflight: false }` is deliberate, so Tailwind never applies its `box-sizing` reset. `public/globals.css` restores it by hand and `overflow: hidden` on html/body. Its header comment explains why in detail — read it before touching layout.

**Two different `styleguide.css` files exist and only one is live:**

- `public/styleguide.css` — served, referenced by `<link href="styleguide.css">`. Hand-tuned token values matching the 1.5x design (22.5px font, `--size-space-450`, etc.).
- `resources/css/styleguide.css` — the raw Figma export. Referenced by nothing. Editing it changes nothing.

If you change a token, edit `public/styleguide.css`. Confirm with a grep before assuming either is wired up.

## Toolchain gotcha: the wrong PHP and the wrong npm are on PATH

`php` resolves to `C:\Program Files\PHP\current\php.exe` (8.3.35), which is **missing `openssl`, `pdo_mysql`, `pdo_sqlite`, and `mbstring`**. Composer and Artisan both fail against it:

```
The openssl extension is required for SSL/TLS protection but is not available.
```

Use Laragon's PHP instead, which has the full extension set:

```powershell
$php = "C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe"
& $php artisan test
```

### Symptom: `could not find driver`

If the site 500s with

```
Illuminate\Database\QueryException  ...  could not find driver
  (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: movierev, ...)
```

**the database is fine — you are on the wrong PHP binary.** The Ignition page's `PHP 8.3.35` line is the tell: that's `C:\Program Files\PHP\current`, which has no `pdo_mysql`. Do not go chasing migrations. Confirm with:

```powershell
php -r "echo PHP_VERSION,' pdo_mysql=',(int)extension_loaded('pdo_mysql');"
```

Restart the server with Laragon's binary instead of fixing the DB:

```powershell
Get-Process php -EA SilentlyContinue | Stop-Process -Force   # if one is holding :8000
& $php artisan serve
```

Laragon's Apache (ports 80/443, `http://movierev.test`) is configured via `C:/laragon/etc/apache2/mod_php.conf` and already uses Laragon's PHP 8.3.33 — that path works out of the box. `php artisan serve` on `:8000` is the one that breaks, because it inherits PATH `php`. Either URL is fine once the binary is right.

Two further traps:

- **`composer dev` inherits whichever `php` is first on PATH**, so it will happily spawn the broken `php artisan serve`. Either run it from a Laragon terminal (which prepends its own PHP) or invoke `& $php artisan dev` directly.
- **`composer install` fails on a clean checkout** — Laragon's `php.ini` has `;extension=zip` commented out and there's no `unzip`/`7z` on PATH, so dist archives can't be extracted. `--prefer-source` fails too (Windows rejects `nul.env` in a test fixture). Workaround without touching the machine: `& $php -d "extension=...\ext\php_zip.dll" "C:\laragon\bin\composer\composer.phar" install`.
- **Use the system npm, not Laragon's.** `C:\laragon\bin\nodejs\node-v22` ships npm 10.9.8, which strips `libc` keys from `package-lock.json` and leaves you with a spurious 54-line diff. `C:\Program Files\nodejs` has npm 11.19.1, which leaves the lockfile clean. In PowerShell call `npm.cmd` explicitly — `npm` resolves to `npm.ps1` and dies on the execution policy.

## Commands

```sh
composer dev          # php artisan dev -> serve + queue:listen + vite, concurrently
php artisan dev:list  # list the 3 registered dev processes
composer test         # config:clear, then php artisan test
npm run build         # vite build
vendor/bin/pint       # formatter, default "laravel" preset (no pint.json)
```

Single test: `php artisan test --filter=test_the_application_returns_a_successful_response`, or `php artisan test tests/Feature`.

`php artisan test` **returns JSON, not a PHPUnit table.** `laravel/pao` detects an agent (checks `OPENCODE`, `CLAUDEECODE`, `CURSOR_AGENT`, `CODEX_*`, ...) and rebinds `OutputStyle` to strip ANSI:

```json
{"tool":"phpunit","result":"failed","tests":2,"errors":1,"error_details":[{"test":"...","message":"..."}]}
```

Set `PAO_DISABLE=true` for the normal green/red table; `PAO_FORCE=true` forces JSON when the env isn't detected.

## `public/build` is committed

`public/build` is tracked (commit ceeeccf). Because asset filenames are content-hashed, every rebuild **rewrites the filename and leaves the old hash as a deleted file** — commit both the `A` and the `D`, or `manifest.json` ends up referencing an asset that isn't in the tree. Use `git add -A public/build`.

The view no longer calls `@vite`, so nothing currently depends on this directory; it exists so fresh clones don't need a build step. Build with the **system** npm (see the npm note above) or you will also churn `package-lock.json`.

The `fontaine` warning about `optimizedFallbacks` during `npm run build` is harmless.

## Database

Dev DB is **MySQL via Laragon/phpMyAdmin** (commit 56af374): database `movierev`, user `root`, empty password, port 3306. It is migrated (users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs).

Because `SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` all default to `database`, **an unmigrated database makes every page 500** with `SQLSTATE[42S02] Table 'movierev.sessions' doesn't exist` — the `sessions` table is created by `0001_01_01_000000_create_users_table.php`, not a file of its own. Fix with `php artisan migrate`. The tests will *not* catch this: `phpunit.xml` forces `array` session/cache and in-memory SQLite, so they pass green while the real app is broken.

`phpunit.xml` overrides this for tests: `DB_CONNECTION=sqlite`, `:memory:`, with `array` session/cache and `sync` queue. No migrations run automatically — add `RefreshDatabase` per test as needed.

`DatabaseSeeder` is idempotent (`updateOrCreate`), so `db:seed` can be re-run freely.

`database/database.sqlite` does not exist and isn't tracked; nothing references it.

## Environment

`.env` is gitignored and must be created from `.env.example` (`composer setup` does this, plus `key:generate`). Without `APP_KEY` the feature test fails with `No application encryption key has been specified` — that is an unconfigured environment, not a code bug.

Routing, middleware, and exception config all live in the `withRouting` / `withMiddleware` / `withExceptions` closures in `bootstrap/app.php`. There is no `RouteServiceProvider`. `withExceptions` already renders JSON for `api/*`.

## Conventions

`.editorconfig` enforces LF, 4-space indent, final newline, trimmed trailing whitespace. `.gitattributes` sets per-type diff drivers (`.blade.php` as html, `.css` as css), so read the rendered diff, not raw lines.

No Prettier and no Blade formatter — match the surrounding generated-Blade indentation by hand. `vendor/bin/pint` covers PHP only and currently passes clean.

`.npmrc` sets `ignore-scripts=true`, so `npm install` runs no lifecycle scripts.

## Instruction-file caveat

`CLAUDE.md` is untouched Laravel Boost boilerplate telling you to `composer require laravel/boost`. Boost is **not** installed. Ignore it — and if you ever install Boost, `php artisan boost:install` overwrites both `CLAUDE.md` and `AGENTS.md`, so merge rather than clobber.