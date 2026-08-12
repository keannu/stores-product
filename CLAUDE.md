# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Backend**: Laravel 13 (PHP 8.3)
- **Frontend**: Vue 3 (Composition API with `<script setup>`) + Tailwind CSS v4
- **Build tool**: Vite with `laravel-vite-plugin`
- **Database**: SQLite (default, configured in `.env`)
- **Testing**: PHPUnit 12 (tests use in-memory SQLite)

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

New controllers go in `app/Http/Controllers/`, models in `app/Models/`. Vue components should live in `resources/js/` (e.g., `resources/js/components/`).
