# MovieREV

Laravel 13.34 on PHP `^8.3`. Single app, no packages/workspace. `main` only — no branches, no PRs, no CI.

## What the app actually is

A small Laravel app around a public movie dashboard, not the stock skeleton. `routes/web.php` holds `GET /` (`HomeController`), the guest auth routes + `POST /logout` (`AuthController`), and `GET /movies/{movie}` (`MovieController`). The catalog is **not stored locally** (see "Movie catalog (TMDB)") and `database/` has no migrations — a single `.sql` export stands in — so don't reach for `php artisan migrate`.

The views are Figma exports in two flavours:

- `resources/views/welcome.blade.php` (dashboard) and `movies/show.blade.php` (movie page) are **responsive** adaptations of their exports: shared `partials/header.blade.php` / `partials/backdrop.blade.php`, `max-w-6xl` main, `min-h-screen`, and the house dark/purple gradient language.
- `login.blade.php` / `register.blade.php` keep the export as a **fixed absolute canvas** (`w-[1201px] h-[670px]` of absolutely positioned divs). Those magic numbers are the design; don't "fix" them.
- Styling comes from the **Tailwind CDN** (`https://cdn.tailwindcss.com`) with an inline `tailwind.config` in `layouts/app.blade.php` mapping design tokens to CSS variables. **This bypasses Vite entirely — the views no longer call `@vite`.** Local `vite.config.js` / `resources/css/app.css` / `resources/js/app.js` are dead weight for these pages; the served stylesheets are static files in `public/`.
- `corePlugins: { preflight: false }` is deliberate, so Tailwind never applies its `box-sizing` reset. `public/globals.css` restores it by hand and pins `overflow: hidden` on html/body — which `layouts/app.blade.php` then undoes with its own inline `html, body { overflow: auto; height: auto }`, so every page that uses the layout scrolls. Read `globals.css`'s header comment before touching layout.

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

## Docker (dev)

`docker/docker-compose.yml` runs the whole stack: `app` (PHP 8.3 + `php artisan serve` on `:8000`), `db` (MySQL 8.4, host port `:3307`), and `phpmyadmin` (`:8080`).

```powershell
docker compose -f docker/docker-compose.yml up -d        # start / recreate
docker compose -f docker/docker-compose.yml down         # stop (keeps volumes)
docker compose -f docker/docker-compose.yml down -v      # stop and wipe db_data + vendor
```

- The repo is **bind-mounted** into `app` (`..:/var/www/html`), so editor changes are served live — no rebuild. `vendor/` is a separate named volume holding the image's Linux install, so **composer/npm changes need `down` + `up --build`**.
- The app container's `CMD` runs `php artisan serve … --no-reload`, and that flag is **load-bearing**. With reload on, `serve` passes only a small whitelist of variables (and unsets the rest) to the child `php -S` process, so the `DB_*` values compose sets under `environment:` never reach it. The server then falls back to the bind-mounted host `.env` (`127.0.0.1:3307`), which is not reachable from inside the container, and every page 500s with `SQLSTATE[HY000] [2002] Connection refused`. `--no-reload` passes the whole environment through so `DB_HOST=db` / `DB_PORT=3306` win.
- Compose resolves relative paths against `docker/`; the build context is `..` (repo root) with `dockerfile: docker/dockerfile`.

## Movie catalog (TMDB)

**The catalog is not stored locally.** There is no `movies` table and no `Movie` model: `routes/web.php` → `HomeController@index` reads the list from the TMDB API on every request.

- `app/Services/TmdbClient.php` wraps the API (`popular()`, `nowPlaying()`, `search()`, `find()`, `imageUrl()`, plus a once-a-day `/genre/movie/list` cache for the genre names, since list responses only carry `genre_ids`). It is the only place that talks HTTP.
- `app/Services/TmdbMovie.php` is the immutable value object the views actually render (`welcome.blade.php`, `<x-movie-card>`, `<x-review-card>`). It replaced the Eloquent model, so `$movie->title`, `posterSource()`, etc. still read the same. **TMDB scores films out of ten and the value object halves them**, because `<x-rating-stars>` and the featured hero are five-point. `TmdbMovie::safeImageUrl()` is the one sanitiser (absolute HTTP(S) or nothing) — it is why a poster snapshot from `reviews` can be rendered blindly.
- `app/Services/TmdbMovieDetail.php` is the movie page's value object: it wraps a `TmdbMovie` and adds what only `/movie/{id}` returns (genres, runtime, `rated`, tagline, director, billing block, backdrop, similar films), plus `runtimeLabel()` (`166` → `2h 46m`).
- `find(int $id)` requests details + credits + release dates + similar in **one** round trip via `append_to_response`, and returns `null` for a TMDB 404 while throwing on a transport/credential failure — so `MovieController` can answer **404 for "no such film"** and **503 for "TMDB is down"** instead of conflating them.
- Credentials live in `config/services.php` → `tmdb`: `TMDB_API_KEY` (v3 query param) or `TMDB_ACCESS_TOKEN` (v4 bearer, which wins when both are set), plus `TMDB_BASE_URL`, `TMDB_IMAGE_BASE_URL`, `TMDB_TIMEOUT`. **With neither credential set the dashboard renders its placeholder slots rather than failing** — that is also what keeps the test suite offline, because `phpunit.xml` blanks both (`Http::fake()` in `tests/Feature/DashboardTest.php` / `tests/Feature/MoviePageTest.php` stands in for the API). A movie page with no credential is a 404.
- `reviews` and `list_movies` point at a movie by **TMDB id** (`tmdb_id`), not a foreign key into `movies`. A review additionally snapshots `movie_title` and `poster_url` so review cards render without a join or a TMDB round trip.
- **The dashboard's "Recently Released" grid is TMDB's `/movie/now_playing`**, not community activity: with an empty `reviews` table a "Recently Reviewed" grid would be permanently empty. That is why `HomeController` no longer aggregates review snapshots (and why `TmdbMovie::fromReviewSnapshot()` is gone). Only the "Popular Reviews This Week" section is community-driven.
- **`GET /movies/{movie}`** is the movie page (`MovieController@show`, `movies/show.blade.php`): hero backdrop + poster, metadata line, `<x-rating-stars>`, the design's tab strip, Synopsis + a details table (director / release year / runtime / rated / genres), Cast & Crew, Reviews, Similar Films. `->whereNumber('movie')` keeps a non-numeric id a 404. Its "Write a review" button only jumps to the Reviews section and "+ Add to watch list" is inert (no review or list flow exists yet); those sections render an empty state.
- **`GET /` never 500s on a missing database.** Only the community sections read MySQL, and `HomeController::recentReviews()` / `MovieController::reviews()` wrap those reads in `try`/`catch`: a refused connection (MySQL on 3307 down) or a missing `reviews` table degrades to empty sections — logged through `report()` — instead of the `SQLSTATE[HY000] [2002]` 500 the pre-TMDB `Schema::hasTable('movies')` guard produced. `tests/Feature/DashboardTest.php` / `MoviePageTest.php` cover both the TMDB and the database failure paths; `/login`, `/register` and `/logout` still require MySQL.
- The header and the page backdrop are `partials/header.blade.php` / `partials/backdrop.blade.php`, shared by `welcome.blade.php` and `movies/show.blade.php` (they were inlined in `welcome` before the movie page existed). The nav's Films/Teasers targets are `route('home')` + fragment so they work from either page.
- Removed with the table: the `/admin/movies` CRUD routes, `Admin\MovieController`, the four `resources/views/admin/movies` views, the `Movie` model, the `admin` middleware alias + `EnsureAdmin`, and `MovieTest`. The `users.usertype` column stays (it is still `enum('admin','user')`), it simply no longer gates anything.
- **The schema change has to be applied to the live database too.** `database/movierev.sql` was hand-edited to drop `movies` and re-key `reviews` / `list_movies`, but that file is only a re-export: drop the `movies` table and the two foreign keys in phpMyAdmin, then re-export. Importing the current file produces a working app because every section has its own `DROP TABLE IF EXISTS`.

## Database

Dev DB is **MySQL 8.4 in Docker** (`docker/docker-compose.yml`, service `db`): database `movierev`, user `root`, password `secret`, published on host port **3307** (3306 is left free for Laragon's MySQL, which the app no longer uses). The container seeds itself from `database/movierev.sql` the first time it starts against an empty `db_data` volume. **phpMyAdmin is part of the stack at `http://localhost:8080`** (`PMA_HOST=db`) and is the tool for schema/seed edits. `.env` / `.env.example` point at it: `DB_HOST=127.0.0.1`, `DB_PORT=3307`, `DB_PASSWORD=secret` (the `app` container overrides these with `DB_HOST=db`, port `3306`).

Start the DB before a host-run `php artisan serve`, or it 500s with `SQLSTATE[HY000] [2002]`:

```powershell
docker compose -f docker/docker-compose.yml up -d db
```

A host-run app (both `php artisan serve` and Laragon's Apache) also 500s on every DB-backed request — including `POST /register`, which looks like "the account wasn't saved" — with `SQLSTATE[HY000] [1045] Access denied for user 'root'@'…' (using password: NO)` when `.env`'s `DB_PASSWORD` is blank. The Docker MySQL always wants `secret` (what `.env.example` ships). Only the `app` container escapes this, because compose sets `DB_PASSWORD: secret` under `environment:`. Keep `.env` in step with `.env.example`.

The expected application schema has 5 tables — `users`, `reviews`, `lists`, `list_movies`, and `user_follows`. The `movies` table was **dropped**: the movie catalog is read from the TMDB API at runtime (see below), so `reviews` and `list_movies` reference a movie by its **TMDB id** instead of a foreign key. The 8 Laravel framework tables (`sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `migrations`) were dropped, and the app was moved off them (see below), so they are not required.

**`database/` holds exactly one thing: `database/movierev.sql` — the export of the 5 application tables**, intended to stay in sync with the live DB. The stock Laravel `migrations/`, `seeders/`, and `factories/` folders were deliberately deleted (the owner prefers a one-file database), so nothing in this repo can recreate framework tables. Therefore:

- **`php artisan migrate` and `php artisan db:seed` no longer exist** (no files to run — `migrate` errors with a missing-directory exception). Schema/seed changes are made in phpMyAdmin, then re-exported over `database/movierev.sql` so the file stays in sync with the live DB.
- **Importing `movierev.sql` on a fresh machine DOES produce a working app.** Session, cache and queue are no longer database-backed: `.env` / `.env.example` use `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` (and `config/session.php` / `config/cache.php` / `config/queue.php` fall back to the same), so the dropped `sessions` / `cache` / `jobs` tables are never touched at runtime. If you ever switch one of those back to `database`, you must re-create the matching framework tables first — a 5-table import would then 500 with `SQLSTATE[42S02] Table 'movierev.sessions' doesn't exist`, and with no migration files there is no artisan command that can fix it.
- **Existing Docker volumes do not re-run the SQL import.** After adding a schema table, create it in phpMyAdmin against the existing database and re-export `movierev.sql`; a fresh volume gets it from the export automatically.
- **Import gotchas**: the dump has no `CREATE DATABASE` / `USE`, so create/select `movierev` first. Each table section includes `DROP TABLE IF EXISTS`, so re-importing over existing tables is safe.
- `composer setup` no longer runs `migrate` (the line was removed from `composer.json`); it only does `.env` + `key:generate` + npm. The `Database\\Factories\\` / `Database\\Seeders\\` PSR-4 entries were left in `composer.json` — harmless while the folders are absent, and regenerated classes autoload if the folders ever come back.

The tests will *not* catch any of this: `phpunit.xml` forces `array` session/cache and in-memory SQLite, so they pass green while the real app is 500ing.

`phpunit.xml` overrides this for tests: `DB_CONNECTION=sqlite`, `:memory:`, with `array` session/cache and `sync` queue. No migrations run, and with the migration files gone `RefreshDatabase` would just give you empty tables — current tests don't touch the DB.

`database/database.sqlite` does not exist and isn't tracked; `database/.gitignore` (one line: `*.sqlite*`) keeps it that way. Nothing references it.

## Environment

`.env` is gitignored and must be created from `.env.example` (`composer setup` does this, plus `key:generate`). Without `APP_KEY` the feature test fails with `No application encryption key has been specified` — that is an unconfigured environment, not a code bug.

Routing, middleware, and exception config all live in the `withRouting` / `withMiddleware` / `withExceptions` closures in `bootstrap/app.php`. There is no `RouteServiceProvider`. `withExceptions` already renders JSON for `api/*`.

**"Remember me" depends on `SESSION_EXPIRE_ON_CLOSE=true`.** The login form's checkbox only controls the long-lived recaller cookie (`remember_web_…`, set via `Auth::attempt(..., $request->boolean('remember'))`); the ordinary session cookie is what keeps an *unticked* login alive. With Laravel's default `expire_on_close=false` that cookie outlives the browser for `SESSION_LIFETIME` minutes, so unticking "Remember me" would still leave the user signed in after a browser restart. `.env` / `.env.example` set `SESSION_EXPIRE_ON_CLOSE=true` (and `config/session.php` defaults to `true`) so the session cookie is a browser-session cookie and only a ticked "Remember me" survives a restart. Registration has no remember option, so `register()` uses plain `Auth::login($user)` — never `Auth::login($user, true)`.

## Conventions

`.editorconfig` enforces LF, 4-space indent, final newline, trimmed trailing whitespace. `.gitattributes` sets per-type diff drivers (`.blade.php` as html, `.css` as css), so read the rendered diff, not raw lines.

No Prettier and no Blade formatter — match the surrounding generated-Blade indentation by hand. `vendor/bin/pint` covers PHP only and currently passes clean.

`.npmrc` sets `ignore-scripts=true`, so `npm install` runs no lifecycle scripts.

## Instruction-file caveat

`CLAUDE.md` is untouched Laravel Boost boilerplate telling you to `composer require laravel/boost`. Boost is **not** installed. Ignore it — and if you ever install Boost, `php artisan boost:install` overwrites both `CLAUDE.md` and `AGENTS.md`, so merge rather than clobber.
