# Plan: Dashboard

**Status: DONE**

## 1. Dashboard Page (Frontend Only)

### Goal
Create the dashboard Blade view and wire it up minimally so it renders at `/dashboard`. Backend service/controller/middleware will be added in a follow-up.

### Files
| Action | File | Detail |
|--------|------|--------|
| Create | `resources/views/dashboard.blade.php` | Server-rendered dashboard UI |
| Modify | `routes/web.php` | Add `GET /dashboard` above catch-all (no auth middleware yet) |
| Modify | `resources/js/views/Login/Login.vue` | Change `router.push(data.redirect)` → `window.location.href = data.redirect` |
| Modify | `resources/css/app.css` | Add `@source '../../resources/views/**/*.blade.php'` |

---

## 2. Side Navigation Bar

### Goal
Add a side navigation bar to the dashboard with four nav items.

### Nav Items
| Label | Path |
|-------|------|
| Stores | /stores |
| Products | /products |
| Orders | /orders |
| Users | /users |

### Files
| Action | File | Detail |
|--------|------|--------|
| Modify | `resources/views/dashboard.blade.php` | Restructure body into sidebar + main content layout; active state highlights current URL |

---

## 3. Mobile Collapsible Side Navigation

### Goal
On mobile, hide the sidebar by default and expose a hamburger icon in the top nav that toggles it open as an overlay drawer. Desktop behaviour (always-visible fixed sidebar) stays unchanged.

### Approach
- Sidebar: `md:translate-x-0` always visible on desktop; `-translate-x-full` by default on mobile, toggled to `translate-x-0` via JS
- Overlay backdrop shown behind the sidebar on mobile; clicking it closes the drawer
- Hamburger button (`md:hidden`) placed left of the brand in the header
- Main content: `md:ml-56 ml-0`
- No extra dependencies — plain JS class toggling with CSS transitions

### Files
| Action | File | Detail |
|--------|------|--------|
| Modify | `resources/views/dashboard.blade.php` | Add hamburger button, overlay div, update sidebar/main classes, add `toggleSidebar()` |

---

## 4. Migrate Dashboard to Vue SPA Component

### Goal
Follow the same pattern as the login page — `dashboard.blade.php` becomes a thin SPA shell and all UI moves into `Dashboard.vue`.

### Blade → Vue Conversions
| Blade | Vue |
|-------|-----|
| `@php $navItems = [...]` | `const navItems = [...]` in `<script setup>` |
| `@foreach` | `v-for` |
| `request()->path()` active check | `useRoute().path` |
| Conditional classes via `{{ }}` | `:class` binding |
| Sidebar vanilla JS toggle | `ref(false)` + reactive `:class` |
| `handleLogout` inline `<script>` | `async function` in `<script setup>` using axios |

### Files
| Action | File | Detail |
|--------|------|--------|
| Create | `resources/js/views/Dashboard/Dashboard.vue` | All dashboard UI as a Vue component |
| Modify | `resources/views/dashboard.blade.php` | Replace with thin SPA shell (same pattern as `login.blade.php`) |
| Modify | `resources/js/router/index.js` | Add `/dashboard` → `Dashboard.vue` route |

---

## 5. Separate JS Entry Points per Blade Shell

### Goal
Each blade shell mounts to its own element id (`#login`, `#dashboard`) with a dedicated JS entry point, keeping the apps isolated.

### Files
| Action | File | Detail |
|--------|------|--------|
| Create | `resources/js/login.js` | Mounts Vue app to `#login` |
| Create | `resources/js/dashboard.js` | Mounts Vue app to `#dashboard` |
| Modify | `resources/views/login.blade.php` | `id="app"` → `id="login"`, load `login.js` |
| Modify | `resources/views/dashboard.blade.php` | `id="app"` → `id="dashboard"`, load `dashboard.js` |
| Modify | `vite.config.js` | Add `login.js` and `dashboard.js` to Vite `input` array |
