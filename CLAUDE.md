# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Backend**: Laravel 13 (PHP 8.3)
- **Frontend**: Vue 3 (Composition API with `<script setup>`) + Tailwind CSS v4
- **Build tool**: Vite with `laravel-vite-plugin`
- **Database**: SQLite (default, configured in `.env`)
- **Testing**: PHPUnit 12 (tests use in-memory SQLite)

## Self-Improving CLAUDE.md
This file is persistent memory across sessions — if it's wrong, future sessions
will write wrong code. Keep it accurate:
- Delete outdated sections instead of leaving them stale
- Add new patterns as they're established
- When a decision changes, update `memory/decisions.md`, not just this file

## MANDATORY: Read Context Files Before Working
Before starting architectural work:
1. Read `memory/decisions.md` — past architectural decisions. Follow them
   unless the user explicitly changes direction.
4. Never re-decide something already decided — if it's documented, follow it.

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
- **Routes**: `routes/web.php` — currently only a root route returning the welcome view
- **Vue alias**: `vite.config.js` aliases `vue` to `vue/dist/vue.esm-bundler.js` (full build including template compiler)
- **Fonts**: Bunny Fonts (Instrument Sans) loaded via `laravel-vite-plugin/fonts`

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

**Model** (`app/Models/`)
- Owns the database structure, relationships, and query scopes
- Must contain no business logic — data definition and access only

New middlewares go in `app/Http/Middlewares/`, controllers in `app/Http/Controllers/`, services in `app/Services/`, models in `app/Models/`. Vue components should live in `resources/js/` (e.g., `resources/js/components/`).
