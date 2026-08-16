# Plan: Split API Routes into routes/api.php

**Status: IN PROGRESS**

## Goal
Move all REST API routes (data CRUD) out of `routes/web.php` into a new `routes/api.php`.
Keep only session-auth routes (login/logout) and Blade view catch-alls in `routes/web.php`.

## Changes

| Action | File | Detail |
|--------|------|--------|
| Create | `routes/api.php` | Dashboard CRUD routes without `/api` prefix (Laravel adds it automatically) |
| Modify | `routes/web.php` | Remove CRUD routes; keep login/logout + view catch-alls |
| Modify | `bootstrap/app.php` | Register `routes/api.php`; add `StartSession` to api middleware group so session-based `Auth::check()` works |
| Modify | `CLAUDE.md` | Update routes table and note convention for future routes |
