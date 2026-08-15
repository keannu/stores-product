# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Backend**: Laravel 13 (PHP 8.3)
- **Frontend**: Vue 3 (Composition API with `<script setup>`) + Tailwind CSS v4
- **Routing (frontend)**: Vue Router 5 in HTML5 history mode (`createWebHistory()`)
- **Build tool**: Vite with `laravel-vite-plugin`
- **Database**: MySQL (`DB_CONNECTION=mysql` in `.env` — host `127.0.0.1:3306`, database `stores-product`)
- **Testing**: PHPUnit 12 — tests run against in-memory SQLite (`phpunit.xml` overrides `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)

> Dev runs on MySQL but tests run on SQLite. Migrations must work on both — watch for
> `enum`, `->after()`, JSON columns, and multi-column `dropColumn`, which behave
> differently or fail outright on SQLite.

## Self-Improving CLAUDE.md
This file is persistent memory across sessions — if it's wrong, future sessions
will write wrong code. Keep it accurate:
- Delete outdated sections instead of leaving them stale
- Add new patterns as they're established
- When a decision changes, update `memory/decisions.md`, not just this file
- **After completing any feature or significant change**, update `memory/decisions.md` with what was decided and why — not just when something changes

## MANDATORY: Read Context Files Before Working
Before starting architectural work:
1. Read `memory/decisions.md` — past architectural decisions. Follow them
   unless the user explicitly changes direction.
2. Never re-decide something already decided — if it's documented, follow it.

## MANDATORY: Create a Plan File Before Any Changes
Before writing or modifying any code:
1. Create a plan file at `plan/<feature-or-task-name>.md` describing what will be done and why.
2. The plan must list every file that will be created or modified and what change will be made to each.
3. Only begin executing after the plan file exists.
4. Delete the plan file once the task is fully complete.

## Commands

### Full dev environment (all services concurrently)
```bash
composer dev
```
This runs: PHP server, queue listener, Pail log viewer, and Vite dev server.

### Individual commands
```bash
php artisan serve          # PHP dev server
npm run dev                # Vite HMR
npm run build              # Production build
```

### Tests
```bash
composer test              # Clears config cache then runs PHPUnit
php artisan test --filter TestName  # Run a single test
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh --seed
```

### Code style
```bash
./vendor/bin/pint          # Laravel Pint (PHP code formatter)
```

### First-time setup
```bash
composer setup             # Installs deps, generates key, migrates, builds assets
```

## Architecture

This is an early-stage Laravel + Vue 3 SPA setup running via Laragon.

- **Vue entry point**: `resources/js/app.js` mounts `App.vue` into `#app`
- **Blade shell**: `resources/views/welcome.blade.php` — the single Blade view that loads Vite assets and provides the `#app` mount point
- **Vue router**: `resources/js/router/index.js` — registers `/` → `views/Home.vue` and `/login` → `views/Login/Login.vue`
- **Vue alias**: `vite.config.js` aliases `vue` to `vue/dist/vue.esm-bundler.js` (full build including template compiler)
- **Fonts**: Bunny Fonts (Instrument Sans) loaded via `laravel-vite-plugin/fonts`
- **HTTP client**: `axios` — CSRF token set globally from `<meta name="csrf-token">` in `Login.vue` (and should be set once globally in `app.js` for future features)
- **Alerts/modals**: `sweetalert2` (SweetAlert2) — used in `Login.vue` for success, error, and validation feedback

### Server Routes (`routes/web.php`)

| Method | Path | Handler | Middleware |
| --- | --- | --- | --- |
| POST | `/login` | `Login\LoginController@login` | `Login\ValidateLoginRequest` |
| POST | `/logout` | `Login\LoginController@logout` | — |
| GET | `/{any}` | returns the `welcome` Blade shell | — |

**CRITICAL — route ordering**: `/{any}` is a catch-all serving the SPA shell so Vue
Router's history mode works on deep links. Every new server route MUST be declared
**above** it, or the catch-all swallows the request and returns HTML instead.

### Database Schema

**`stores`** — core store entity
- `id` (PK, auto-increment)
- `store_name` (indexed)
- `description` (nullable text)
- `address` (indexed)
- `owner_name`
- `mobile_number`
- `email` (indexed)
- `created_at` / `updated_at`

**`users`** — belongs to a store (many users → one store)
- `store_id` (nullable FK → `stores.id`, sets null on store delete)
- `role` — string, used for post-login redirect (`'admin'` | `'customer'`)

### Backend Layer Structure (MVC + Service Layer)

**Middleware** (`app/Http/Middlewares/`)
- Handles request validation and guards before reaching the controller
- Applied per-route in `routes/web.php` using `->middleware(ClassName::class)`
- Must contain no business logic — only validate and pass through or reject

**Controller** (`app/Http/Controllers/`)
- Receives validated requests and returns API responses
- Injects the Service layer via constructor injection
- Must contain no business logic — delegate everything to Services

**Service** (`app/Services/`)
- Owns all business and application logic for a given feature
- Injects the Model layer via constructor injection
- Must contain no HTTP concerns — no Request or Response objects
- **Known deviation — do not copy**: `app/Services/Login/LoginService.php` accepts
  `Illuminate\Http\Request` and calls `$request->session()`, violating the rule above.
  New services must take plain arrays/scalars/DTOs. This one needs refactoring.

**Model** (`app/Models/`)
- Owns the database structure, relationships, and query scopes
- Must contain no business logic — data definition and access only
- Uses Laravel 13 **PHP attributes**, not the legacy protected-array style —
  `#[Fillable([...])]` and `#[Hidden([...])]` above the class (see `app/Models/User.php`).
  Do not write `protected $fillable = [...]`.

### Authentication

Session-based via `Auth::attempt()`. Login matches on the **`email`** column. Session is regenerated on login and invalidated on logout.

**Login flow** (`LoginService::login`):
1. Check rate limiter — if exceeded, return 429 immediately.
2. Check email existence via `User::where('email', ...)->exists()` — if not found, return 404 (`"No account found with that email address."`).
3. Attempt `Auth::attempt()` — if it fails, hit the rate limiter and return 401 with remaining attempts.
4. On the 5th failure the rate limiter locks the account; return 429 (`"Account locked..."`).
5. On success, clear the rate limiter, regenerate the session, and return 200 with a `redirect` URL.

**Rate limiting**: 5 attempts max, 1-hour lockout (`RateLimiter`, key `login:<email>`).

**Role-based redirect** (post-login):
- `admin` → `user->store->admin_redirect_link` (falls back to `/`)
- `customer` → `user->store->customer_redirect_link` (falls back to `/`)
- Any other role → `/`

**Validation** (`ValidateLoginRequest` middleware): `email` required + valid email format, `password` required string. Returns 422 on failure.

### File Organization

Every feature gets its own named subdirectory on both sides (see `memory/decisions.md`).
Adding feature `Foo` means creating and registering all of:

```
app/Http/Middlewares/Foo/ValidateFooRequest.php
app/Http/Controllers/Foo/FooController.php
app/Services/Foo/FooService.php
app/Models/Foo.php                      # flat — see note below
resources/js/views/Foo/Foo.vue
```

Then register the Vue route in `resources/js/router/index.js` and the server route in
`routes/web.php` **above the `/{any}` catch-all**.
