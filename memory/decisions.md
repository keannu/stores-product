# Architectural Decisions

## Frontend Routing: Vue Router with HTML5 History Mode
- Using `createWebHistory()` (clean URLs, no `#` hash)
- Laravel catch-all route `Route::get('/{any}', ...)` in `routes/web.php` forwards all URLs to the Blade shell
- **Consequence**: Any new Laravel API routes must be defined before the catch-all or prefixed (e.g., `/api/...`)

## SPA Shell: Single Blade View
- `resources/views/welcome.blade.php` is the only Blade view — it provides the `<div id="app">` mount point
- All page rendering is done by Vue components

## File Organization Convention
Every feature gets its own named subdirectory on both the Laravel and Vue sides.

**Vue** (`resources/js/views/<Feature>/`):
- `resources/js/views/Login/Login.vue`
- New features follow the same pattern: `resources/js/views/<Feature>/<Feature>.vue`

**Laravel** (`app/Http/Controllers/<Feature>/`):
- `app/Http/Controllers/Login/LoginController.php`
- New features follow the same pattern: `app/Http/Controllers/<Feature>/<Feature>Controller.php`

**Services** (`app/Services/<Feature>/`):
- `app/Services/Login/LoginService.php`
- New features follow the same pattern: `app/Services/<Feature>/<Feature>Service.php`

**Models** (`app/Models/<Feature>/`):
- `app/Models/Login/Login.php`
- New features follow the same pattern: `app/Models/<Feature>/<Feature>.php`

**Router**: register each new view in `resources/js/router/index.js`
**Routes**: add Laravel API/form endpoints in `routes/web.php` before the catch-all

## Database: MySQL
- Primary DB is MySQL (configured via `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`)
- Tests use in-memory SQLite (`:memory:`) via `phpunit.xml`

## Vue Build: Full ESM Bundler
- `vite.config.js` aliases `vue` to `vue/dist/vue.esm-bundler.js` to include the runtime template compiler
