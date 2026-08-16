# Users CRUD Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Status: IN PROGRESS**

**Goal:** Build a full CRUD management page for users inside the dashboard, following the existing MVC + Service architecture.

**Architecture:** Middleware → Controller → Service → Model. All API routes prefixed with `/dashboard`. Vue Router handles `/dashboard/users` client-side within the existing dashboard SPA. A shared `DashboardLayout.vue` is extracted so both Dashboard and Users pages share the same header + sidebar without code duplication.

**Tech Stack:** Laravel 13, PHPUnit 12 (SQLite in-memory), Vue 3 Composition API, Tailwind CSS v4, axios, SweetAlert2, Vue Router 5.

---

## Task 1: Migration — add `deleted_at` to users

**Files:**
- Create: `database/migrations/2026_08_15_000003_add_soft_deletes_to_users_table.php`

**Step 1: Create the migration file**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
```

**Step 2: Run the migration**

```bash
php artisan migrate
```

Expected: migration runs without error, `deleted_at` column added to `users`.

**Step 3: Run tests to confirm nothing is broken**

```bash
php artisan config:clear && php artisan test
```

Expected: all existing tests pass.

---

## Task 2: Add SoftDeletes to User model

**Files:**
- Modify: `app/Models/User.php`

**Step 1: Add the trait**

Add `SoftDeletes` to the `use` statement and import:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

// In class body, change:
use HasFactory, Notifiable;
// to:
use HasFactory, Notifiable, SoftDeletes;
```

**Step 2: Run tests**

```bash
php artisan config:clear && php artisan test
```

Expected: all existing tests pass.

**Step 3: Commit**

```bash
git add database/migrations/2026_08_15_000003_add_soft_deletes_to_users_table.php app/Models/User.php
git commit -m "add soft deletes to users table and model"
```

---

## Task 3: UserService — scaffold + `index` method + tests

**Files:**
- Create: `app/Services/Users/UserService.php`
- Create: `tests/Unit/Services/Users/UserServiceTest.php`

**Step 1: Write the failing tests for `index`**

```php
<?php

namespace Tests\Unit\Services\Users;

use App\Models\User;
use App\Services\Users\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function test_index_returns_paginated_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->service->index();
        $data     = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(3, $data['data']);
        $this->assertSame(3, $data['meta']['total']);
        $this->assertSame(1, $data['meta']['current_page']);
    }

    public function test_index_filters_by_name(): void
    {
        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        $response = $this->service->index('Alice');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('Alice', $data['data'][0]['name']);
    }

    public function test_index_filters_by_email(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);
        User::factory()->create(['email' => 'bob@example.com']);

        $response = $this->service->index('alice');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('alice@example.com', $data['data'][0]['email']);
    }

    public function test_index_does_not_include_soft_deleted_users(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->service->index();
        $data     = $response->getData(true);

        $this->assertSame(0, $data['meta']['total']);
    }
}
```

**Step 2: Run to confirm failure**

```bash
php artisan test --filter UserServiceTest
```

Expected: FAIL — `UserService` class not found.

**Step 3: Create `UserService` with `index`**

```php
<?php

namespace App\Services\Users;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserService
{
    public function index(string $search = '', int $page = 1): JsonResponse
    {
        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $paginated = $query->paginate(15, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }
}
```

**Step 4: Run tests**

```bash
php artisan test --filter UserServiceTest
```

Expected: 4 tests pass.

---

## Task 4: UserService — `store` method + tests

**Files:**
- Modify: `app/Services/Users/UserService.php`
- Modify: `tests/Unit/Services/Users/UserServiceTest.php`

**Step 1: Add failing tests**

```php
use Illuminate\Support\Facades\Hash;

public function test_store_creates_a_user(): void
{
    $response = $this->service->store([
        'name'     => 'Alice',
        'email'    => 'alice@example.com',
        'password' => 'secret123',
        'role'     => 'customer',
        'store_id' => null,
    ]);

    $this->assertSame(201, $response->getStatusCode());
    $this->assertDatabaseHas('users', [
        'name'  => 'Alice',
        'email' => 'alice@example.com',
        'role'  => 'customer',
    ]);
}

public function test_store_hashes_the_password(): void
{
    $this->service->store([
        'name'     => 'Alice',
        'email'    => 'alice@example.com',
        'password' => 'secret123',
        'role'     => 'customer',
        'store_id' => null,
    ]);

    $user = User::where('email', 'alice@example.com')->first();
    $this->assertTrue(Hash::check('secret123', $user->password));
}
```

**Step 2: Run to confirm failure**

```bash
php artisan test --filter UserServiceTest
```

Expected: 2 new tests fail — `store` method not found.

**Step 3: Add `store` method to `UserService`**

```php
public function store(array $data): JsonResponse
{
    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => $data['password'],
        'role'     => $data['role'],
        'store_id' => $data['store_id'] ?? null,
    ]);

    return response()->json($user, 201);
}
```

Note: `password` is cast to `hashed` in the User model's `casts()`, so Laravel hashes it automatically on `create`.

**Step 4: Run tests**

```bash
php artisan test --filter UserServiceTest
```

Expected: all 6 tests pass.

---

## Task 5: UserService — `update` method + tests

**Files:**
- Modify: `app/Services/Users/UserService.php`
- Modify: `tests/Unit/Services/Users/UserServiceTest.php`

**Step 1: Add failing tests**

```php
use Illuminate\Database\Eloquent\ModelNotFoundException;

public function test_update_changes_user_fields(): void
{
    $user = User::factory()->create(['name' => 'Old Name', 'role' => 'customer']);

    $this->service->update($user->id, [
        'name'     => 'New Name',
        'email'    => $user->email,
        'role'     => 'admin',
        'store_id' => null,
    ]);

    $this->assertDatabaseHas('users', [
        'id'   => $user->id,
        'name' => 'New Name',
        'role' => 'admin',
    ]);
}

public function test_update_does_not_change_password_when_omitted(): void
{
    $user         = User::factory()->create(['password' => bcrypt('original')]);
    $originalHash = $user->password;

    $this->service->update($user->id, [
        'name'     => $user->name,
        'email'    => $user->email,
        'role'     => $user->role,
        'store_id' => null,
        'password' => '',
    ]);

    $this->assertSame($originalHash, $user->fresh()->password);
}

public function test_update_throws_for_nonexistent_user(): void
{
    $this->expectException(ModelNotFoundException::class);

    $this->service->update(999, [
        'name'     => 'X',
        'email'    => 'x@x.com',
        'role'     => 'customer',
        'store_id' => null,
    ]);
}
```

**Step 2: Run to confirm failure**

```bash
php artisan test --filter UserServiceTest
```

Expected: 3 new tests fail.

**Step 3: Add `update` method to `UserService`**

```php
public function update(int $id, array $data): JsonResponse
{
    $user = User::findOrFail($id);

    $payload = [
        'name'     => $data['name'],
        'email'    => $data['email'],
        'role'     => $data['role'],
        'store_id' => $data['store_id'] ?? null,
    ];

    if (!empty($data['password'])) {
        $payload['password'] = $data['password'];
    }

    $user->update($payload);

    return response()->json($user->fresh());
}
```

**Step 4: Run tests**

```bash
php artisan test --filter UserServiceTest
```

Expected: all 9 tests pass.

---

## Task 6: UserService — `destroy` method + tests

**Files:**
- Modify: `app/Services/Users/UserService.php`
- Modify: `tests/Unit/Services/Users/UserServiceTest.php`

**Step 1: Add failing tests**

```php
public function test_destroy_soft_deletes_user(): void
{
    $user = User::factory()->create();

    $response = $this->service->destroy($user->id);

    $this->assertSame(200, $response->getStatusCode());
    $this->assertSoftDeleted('users', ['id' => $user->id]);
}

public function test_destroy_throws_for_nonexistent_user(): void
{
    $this->expectException(ModelNotFoundException::class);

    $this->service->destroy(999);
}
```

**Step 2: Run to confirm failure**

```bash
php artisan test --filter UserServiceTest
```

Expected: 2 new tests fail.

**Step 3: Add `destroy` method to `UserService`**

```php
public function destroy(int $id): JsonResponse
{
    $user = User::findOrFail($id);
    $user->delete();

    return response()->json(['message' => 'User deleted']);
}
```

**Step 4: Run tests**

```bash
php artisan test --filter UserServiceTest
```

Expected: all 11 tests pass.

---

## Task 7: UserService — `stores` method

**Files:**
- Modify: `app/Services/Users/UserService.php`

No separate tests needed — this is a pass-through query with no logic.

**Step 1: Add `stores` method**

```php
public function stores(): JsonResponse
{
    return response()->json(Store::all(['id', 'store_name']));
}
```

**Step 2: Run all tests to confirm nothing broke**

```bash
php artisan config:clear && php artisan test
```

Expected: all tests pass.

**Step 3: Commit**

```bash
git add app/Services/Users/UserService.php tests/Unit/Services/Users/UserServiceTest.php
git commit -m "add UserService with index, store, update, destroy, and stores methods"
```

---

## Task 8: ValidateUserRequest middleware + tests

**Files:**
- Create: `app/Http/Middlewares/Users/ValidateUserRequest.php`
- Create: `tests/Unit/Middlewares/Users/ValidateUserRequestTest.php`

**Step 1: Write failing tests**

```php
<?php

namespace Tests\Unit\Middlewares\Users;

use App\Http\Middlewares\Users\ValidateUserRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

class ValidateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeRequest(string $method, array $data, ?int $routeId = null): Request
    {
        $uri     = '/dashboard/users' . ($routeId ? "/{$routeId}" : '');
        $request = Request::create($uri, $method, $data);

        if ($routeId !== null) {
            $route = new Route([$method], '/dashboard/users/{id}', []);
            $route->bind($request);
            $route->setParameter('id', $routeId);
            $request->setRouteResolver(fn () => $route);
        }

        return $request;
    }

    private function run(Request $request): int
    {
        $middleware = new ValidateUserRequest();
        $next       = fn ($req) => response()->json(['ok' => true]);

        return $middleware->handle($request, $next)->getStatusCode();
    }

    public function test_passes_with_valid_create_data(): void
    {
        $this->assertSame(200, $this->run($this->makeRequest('POST', [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret123',
            'role'     => 'customer',
            'store_id' => null,
        ])));
    }

    public function test_fails_when_name_is_missing(): void
    {
        $this->assertSame(422, $this->run($this->makeRequest('POST', [
            'email'    => 'alice@example.com',
            'password' => 'secret123',
            'role'     => 'customer',
        ])));
    }

    public function test_fails_when_email_is_invalid(): void
    {
        $this->assertSame(422, $this->run($this->makeRequest('POST', [
            'name'     => 'Alice',
            'email'    => 'not-an-email',
            'password' => 'secret123',
            'role'     => 'customer',
        ])));
    }

    public function test_fails_when_email_is_duplicate_on_create(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $this->assertSame(422, $this->run($this->makeRequest('POST', [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret123',
            'role'     => 'customer',
        ])));
    }

    public function test_allows_same_email_on_update(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.com']);

        $this->assertSame(200, $this->run($this->makeRequest('PUT', [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'role'     => 'customer',
            'store_id' => null,
        ], $user->id)));
    }

    public function test_fails_when_password_is_missing_on_create(): void
    {
        $this->assertSame(422, $this->run($this->makeRequest('POST', [
            'name'  => 'Alice',
            'email' => 'alice@example.com',
            'role'  => 'customer',
        ])));
    }

    public function test_allows_missing_password_on_update(): void
    {
        $user = User::factory()->create();

        $this->assertSame(200, $this->run($this->makeRequest('PUT', [
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => 'customer',
            'store_id' => null,
        ], $user->id)));
    }

    public function test_fails_when_role_is_invalid(): void
    {
        $this->assertSame(422, $this->run($this->makeRequest('POST', [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret123',
            'role'     => 'superuser',
        ])));
    }

    public function test_fails_when_store_id_does_not_exist(): void
    {
        $this->assertSame(422, $this->run($this->makeRequest('POST', [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret123',
            'role'     => 'customer',
            'store_id' => 999,
        ])));
    }

    public function test_passes_when_store_id_is_null(): void
    {
        $this->assertSame(200, $this->run($this->makeRequest('POST', [
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'password' => 'secret123',
            'role'     => 'customer',
            'store_id' => null,
        ])));
    }
}
```

**Step 2: Run to confirm failure**

```bash
php artisan test --filter ValidateUserRequestTest
```

Expected: FAIL — class not found.

**Step 3: Create the middleware**

```php
<?php

namespace App\Http\Middlewares\Users;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $isUpdate = $request->isMethod('PUT');
        $userId   = $request->route('id');

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required',
                'email',
                $isUpdate
                    ? Rule::unique('users')->ignore($userId)
                    : Rule::unique('users'),
            ],
            'password' => $isUpdate
                ? ['nullable', 'string', 'min:8']
                : ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['admin', 'customer'])],
            'store_id' => ['nullable', 'exists:stores,id'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return $next($request);
    }
}
```

**Step 4: Run tests**

```bash
php artisan test --filter ValidateUserRequestTest
```

Expected: all 10 tests pass.

**Step 5: Run full suite**

```bash
php artisan config:clear && php artisan test
```

Expected: all tests pass.

**Step 6: Commit**

```bash
git add app/Http/Middlewares/Users/ValidateUserRequest.php tests/Unit/Middlewares/Users/ValidateUserRequestTest.php
git commit -m "add ValidateUserRequest middleware with create/update rules and tests"
```

---

## Task 9: UserController

**Files:**
- Create: `app/Http/Controllers/Users/UserController.php`

No separate unit tests — the controller is a thin delegator, fully covered by service tests.

**Step 1: Create the controller**

```php
<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->userService->index(
            $request->string('search')->toString(),
            (int) $request->input('page', 1),
        );
    }

    public function stores(): JsonResponse
    {
        return $this->userService->stores();
    }

    public function store(Request $request): JsonResponse
    {
        return $this->userService->store($request->all());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->userService->update($id, $request->all());
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->userService->destroy($id);
    }
}
```

**Step 2: Run tests**

```bash
php artisan config:clear && php artisan test
```

Expected: all tests pass.

**Step 3: Commit**

```bash
git add app/Http/Controllers/Users/UserController.php
git commit -m "add UserController delegating to UserService"
```

---

## Task 10: Register routes

**Files:**
- Modify: `routes/web.php`

**Step 1: Add imports and routes**

Add to the import block at the top:

```php
use App\Http\Controllers\Users\UserController;
use App\Http\Middlewares\Users\ValidateUserRequest as ValidateUserRequest2;
```

> Note: name it `ValidateUserRequest2` to avoid clash with the Login one — or use an alias. Alternatively, use the fully qualified class name inline.

Actually, avoid the alias confusion. Use inline fully qualified names OR rename the import properly. The cleaner approach:

```php
use App\Http\Controllers\Users\UserController;
use App\Http\Middlewares\Users\ValidateUserRequest as ValidateUserStoreRequest;
```

Add these routes **above** `Route::get('/{any}', ...)`:

```php
Route::get('/dashboard/stores', [UserController::class, 'stores'])
    ->middleware(RedirectIfNotAuthenticated::class);

Route::get('/dashboard/users', [UserController::class, 'index'])
    ->middleware(RedirectIfNotAuthenticated::class);

Route::post('/dashboard/users', [UserController::class, 'store'])
    ->middleware([RedirectIfNotAuthenticated::class, ValidateUserStoreRequest::class]);

Route::put('/dashboard/users/{id}', [UserController::class, 'update'])
    ->middleware([RedirectIfNotAuthenticated::class, ValidateUserStoreRequest::class]);

Route::delete('/dashboard/users/{id}', [UserController::class, 'destroy'])
    ->middleware(RedirectIfNotAuthenticated::class);
```

**Step 2: Run tests**

```bash
php artisan config:clear && php artisan test
```

Expected: all tests pass.

**Step 3: Commit**

```bash
git add routes/web.php
git commit -m "register dashboard users and stores API routes"
```

---

## Task 11: Extract DashboardLayout.vue

The header and logout logic currently live in `Dashboard.vue`. Extracting them to `DashboardLayout.vue` lets `Users.vue` reuse the same shell without duplication.

**Files:**
- Create: `resources/js/views/Dashboard/DashboardLayout.vue`
- Modify: `resources/js/views/Dashboard/Dashboard.vue`

**Step 1: Create `DashboardLayout.vue`**

Move the header, sidebar-toggle-anchor, and LeftNavigationBar out of `Dashboard.vue` into this layout:

```vue
<template>
    <div class="min-h-screen bg-neutral-100 font-sans">

        <!-- Top Navigation -->
        <header class="fixed top-0 left-0 right-0 z-30 bg-white border-b border-neutral-200 shadow-sm">
            <div class="h-16 flex items-center justify-between px-4 sm:px-6">

                <div class="flex items-center gap-3">
                    <div id="sidebar-toggle-anchor"></div>

                    <!-- Brand -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="text-base font-semibold text-neutral-900 tracking-tight">Stores</span>
                    </div>
                </div>

                <!-- Logout -->
                <button
                    @click="handleLogout"
                    class="flex items-center gap-2 text-sm text-neutral-600 hover:text-neutral-900 transition px-3 py-2 rounded-lg hover:bg-neutral-100"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                </button>
            </div>
        </header>

        <LeftNavigationBar />

        <div class="flex pt-16 min-h-screen">
            <main class="flex-1 ml-0 md:ml-56 px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import LeftNavigationBar from '../Common/LeftNavigationBar.vue';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

async function handleLogout() {
    await axios.post('/logout');
    window.location.href = '/login';
}
</script>
```

**Step 2: Refactor `Dashboard.vue` to use `DashboardLayout`**

Replace the entire file with:

```vue
<template>
    <DashboardLayout>

        <!-- Welcome -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm text-neutral-500">Welcome back. Here's an overview of your account.</p>
        </div>

        <!-- Cards grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- Account card -->
            <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-neutral-700">Account</h2>
                </div>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-neutral-400 text-xs uppercase tracking-wide">Name</dt>
                        <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-400 text-xs uppercase tracking-wide">Email</dt>
                        <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-400 text-xs uppercase tracking-wide">Role</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700">—</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Store card -->
            <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-neutral-700">Store</h2>
                </div>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-neutral-400 text-xs uppercase tracking-wide">Store Name</dt>
                        <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-400 text-xs uppercase tracking-wide">Address</dt>
                        <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-400 text-xs uppercase tracking-wide">Email</dt>
                        <dd class="text-neutral-800 font-medium mt-0.5">—</dd>
                    </div>
                </dl>
            </div>

            <!-- Security card -->
            <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 bg-neutral-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-neutral-700">Security</h2>
                </div>
                <p class="text-sm text-neutral-500 leading-relaxed">
                    Your session is protected. Sign out when you're done using a shared device.
                </p>
                <div class="mt-4 flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Session active
                </div>
            </div>

        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from './DashboardLayout.vue';
</script>
```

**Step 3: Verify the dashboard still renders correctly in the browser**

Start dev server: `composer dev`
Navigate to `/dashboard` and confirm the layout looks identical to before.

**Step 4: Commit**

```bash
git add resources/js/views/Dashboard/DashboardLayout.vue resources/js/views/Dashboard/Dashboard.vue
git commit -m "extract DashboardLayout component to share shell across dashboard pages"
```

---

## Task 12: Update LeftNavigationBar — use RouterLink with /dashboard/* paths

Nav items currently use `<a>` tags pointing to `/users`, `/stores`, etc. These must become Vue RouterLinks pointing to `/dashboard/users`, `/dashboard/stores`, etc. so navigation stays client-side within the SPA.

**Files:**
- Modify: `resources/js/views/Common/LeftNavigationBar.vue`

**Step 1: Replace `<a>` with `<RouterLink>` and update paths**

In the `<template>`, change:
```vue
<a
    v-for="item in navItems"
    :key="item.href"
    :href="item.href"
    ...
    :class="isActive(item.href) ? ... : ..."
>
    ...
    {{ item.label }}
</a>
```

To:
```vue
<RouterLink
    v-for="item in navItems"
    :key="item.to"
    :to="item.to"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition"
    :class="isActive(item.to)
        ? 'bg-blue-50 text-blue-700'
        : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900'"
>
    <svg
        class="w-[18px] h-[18px] shrink-0"
        :class="isActive(item.to) ? 'text-blue-600' : 'text-neutral-400'"
        fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
    >
        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
    </svg>
    {{ item.label }}
</RouterLink>
```

In `<script setup>`, add `RouterLink` import and update `navItems`:

```js
import { ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const route = useRoute();
const isOpen = ref(false);

function toggleSidebar() {
    isOpen.value = !isOpen.value;
}

const navItems = [
    { label: 'Stores',   to: '/dashboard/stores',   icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z' },
    { label: 'Products', to: '/dashboard/products', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10' },
    { label: 'Orders',   to: '/dashboard/orders',   icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
    { label: 'Users',    to: '/dashboard/users',    icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

function isActive(to) {
    return route.path === to;
}
```

**Step 2: Verify in browser**

Navigate to `/dashboard`. Click "Users" in the nav — the URL should change to `/dashboard/users` without a full page reload (no browser spinner). Clicking "Dashboard" nav item... actually there is no Dashboard nav item. That's fine — clicking the brand or any unimplemented route shows NotFound, which is expected.

**Step 3: Commit**

```bash
git add resources/js/views/Common/LeftNavigationBar.vue
git commit -m "update nav links to RouterLink with /dashboard/* paths"
```

---

## Task 13: Vue Router — add /dashboard/users route

**Files:**
- Modify: `resources/js/router/index.js`

**Step 1: Add the Users route**

```js
import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Login/Login.vue';
import Dashboard from '../views/Dashboard/Dashboard.vue';
import Users from '../views/Users/Users.vue';
import NotFound from '../views/NotFound/NotFound.vue';

const routes = [
    { path: '/', component: Home },
    { path: '/login', component: Login },
    { path: '/dashboard', component: Dashboard },
    { path: '/dashboard/users', component: Users },
    { path: '/:pathMatch(.*)*', component: NotFound },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
```

Note: `Users.vue` does not exist yet — the dev server will error until Task 14 is complete. That's fine.

---

## Task 14: Create Users.vue

**Files:**
- Create: `resources/js/views/Users/Users.vue`

**Step 1: Create the component**

```vue
<template>
    <DashboardLayout>

        <!-- Page header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Users</h1>
                <p class="mt-1 text-sm text-neutral-500">Manage all users in the system.</p>
            </div>
            <button
                @click="openCreate"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add User
            </button>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Search by name or email…"
                class="w-full sm:w-72 px-4 py-2 text-sm border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Table -->
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 bg-neutral-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wide">Store</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td colspan="5" class="px-4 py-10 text-center text-neutral-400">Loading…</td>
                    </tr>
                    <tr v-else-if="!users.length">
                        <td colspan="5" class="px-4 py-10 text-center text-neutral-400">No users found.</td>
                    </tr>
                    <template v-else>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50 transition"
                        >
                            <td class="px-4 py-3 font-medium text-neutral-800">{{ user.name }}</td>
                            <td class="px-4 py-3 text-neutral-600">{{ user.email }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"
                                    :class="user.role === 'admin' ? 'bg-blue-50 text-blue-700' : 'bg-neutral-100 text-neutral-600'"
                                >{{ user.role }}</span>
                            </td>
                            <td class="px-4 py-3 text-neutral-600">{{ storeName(user.store_id) }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button @click="openEdit(user)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                <button @click="confirmDelete(user)" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="meta.last_page > 1" class="flex items-center justify-between">
            <p class="text-sm text-neutral-500">{{ meta.total }} user{{ meta.total !== 1 ? 's' : '' }} total</p>
            <div class="flex items-center gap-2">
                <button
                    @click="goToPage(meta.current_page - 1)"
                    :disabled="meta.current_page === 1"
                    class="px-3 py-1.5 text-sm border border-neutral-200 rounded-lg text-neutral-600 hover:bg-neutral-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >Prev</button>
                <span class="text-sm text-neutral-600">{{ meta.current_page }} / {{ meta.last_page }}</span>
                <button
                    @click="goToPage(meta.current_page + 1)"
                    :disabled="meta.current_page === meta.last_page"
                    class="px-3 py-1.5 text-sm border border-neutral-200 rounded-lg text-neutral-600 hover:bg-neutral-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
                >Next</button>
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div
                v-if="modal.show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">
                        {{ modal.mode === 'create' ? 'Add User' : 'Edit User' }}
                    </h2>
                    <form @submit.prevent="submitModal" class="space-y-4">

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Name</label>
                            <input
                                v-model="modal.form.name"
                                type="text"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.name ? 'border-red-400' : 'border-neutral-300'"
                            />
                            <p v-if="modal.errors.name" class="mt-1 text-xs text-red-500">{{ modal.errors.name[0] }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
                            <input
                                v-model="modal.form.email"
                                type="email"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.email ? 'border-red-400' : 'border-neutral-300'"
                            />
                            <p v-if="modal.errors.email" class="mt-1 text-xs text-red-500">{{ modal.errors.email[0] }}</p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Password</label>
                            <input
                                v-model="modal.form.password"
                                type="password"
                                :placeholder="modal.mode === 'edit' ? 'Leave blank to keep current' : ''"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.password ? 'border-red-400' : 'border-neutral-300'"
                            />
                            <p v-if="modal.errors.password" class="mt-1 text-xs text-red-500">{{ modal.errors.password[0] }}</p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Role</label>
                            <select
                                v-model="modal.form.role"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.role ? 'border-red-400' : 'border-neutral-300'"
                            >
                                <option value="">Select role…</option>
                                <option value="admin">admin</option>
                                <option value="customer">customer</option>
                            </select>
                            <p v-if="modal.errors.role" class="mt-1 text-xs text-red-500">{{ modal.errors.role[0] }}</p>
                        </div>

                        <!-- Store -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Store</label>
                            <select
                                v-model="modal.form.store_id"
                                class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="modal.errors.store_id ? 'border-red-400' : 'border-neutral-300'"
                            >
                                <option :value="null">None</option>
                                <option v-for="store in stores" :key="store.id" :value="store.id">
                                    {{ store.id }} — {{ store.store_name }}
                                </option>
                            </select>
                            <p v-if="modal.errors.store_id" class="mt-1 text-xs text-red-500">{{ modal.errors.store_id[0] }}</p>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-neutral-600 border border-neutral-300 rounded-xl hover:bg-neutral-50 transition"
                            >Cancel</button>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-50 transition"
                            >{{ submitting ? 'Saving…' : 'Save' }}</button>
                        </div>

                    </form>
                </div>
            </div>
        </Teleport>

    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import DashboardLayout from '../Dashboard/DashboardLayout.vue';

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

// --- State ---
const users       = ref([]);
const stores      = ref([]);
const loading     = ref(false);
const submitting  = ref(false);
const search      = ref('');
const currentPage = ref(1);
const meta        = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const modal = reactive({
    show:   false,
    mode:   'create',
    editId: null,
    form:   emptyForm(),
    errors: {},
});

function emptyForm() {
    return { name: '', email: '', password: '', role: '', store_id: null };
}

// --- Fetch ---
async function fetchUsers() {
    loading.value = true;
    try {
        const { data } = await axios.get('/dashboard/users', {
            params: { search: search.value, page: currentPage.value },
        });
        users.value = data.data;
        meta.value  = data.meta;
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load users.' });
    } finally {
        loading.value = false;
    }
}

async function fetchStores() {
    try {
        const { data } = await axios.get('/dashboard/stores');
        stores.value = data;
    } catch {
        // non-critical — store select will simply be empty
    }
}

onMounted(() => {
    fetchUsers();
    fetchStores();
});

// Debounced search — resets to page 1 on new term
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage.value = 1;
        fetchUsers();
    }, 400);
});

// --- Helpers ---
function storeName(storeId) {
    if (!storeId) return '—';
    const store = stores.value.find(s => s.id === storeId);
    return store ? store.store_name : storeId;
}

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page) return;
    currentPage.value = page;
    fetchUsers();
}

// --- Modal ---
function openCreate() {
    modal.mode   = 'create';
    modal.editId = null;
    modal.form   = emptyForm();
    modal.errors = {};
    modal.show   = true;
}

function openEdit(user) {
    modal.mode   = 'edit';
    modal.editId = user.id;
    modal.form   = { name: user.name, email: user.email, password: '', role: user.role, store_id: user.store_id };
    modal.errors = {};
    modal.show   = true;
}

function closeModal() {
    modal.show = false;
}

async function submitModal() {
    submitting.value = true;
    modal.errors     = {};
    try {
        if (modal.mode === 'create') {
            await axios.post('/dashboard/users', modal.form);
        } else {
            await axios.put(`/dashboard/users/${modal.editId}`, modal.form);
        }
        closeModal();
        fetchUsers();
    } catch (error) {
        if (error.response?.status === 422) {
            modal.errors = error.response.data.errors ?? {};
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message ?? 'Something went wrong.' });
        }
    } finally {
        submitting.value = false;
    }
}

// --- Delete ---
async function confirmDelete(user) {
    const result = await Swal.fire({
        title:              'Delete user?',
        text:               `"${user.name}" will be removed.`,
        icon:               'warning',
        showCancelButton:   true,
        confirmButtonText:  'Delete',
        confirmButtonColor: '#ef4444',
        cancelButtonText:   'Cancel',
    });

    if (!result.isConfirmed) return;

    try {
        await axios.delete(`/dashboard/users/${user.id}`);
        fetchUsers();
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error', text: error.response?.data?.message ?? 'Failed to delete user.' });
    }
}
</script>
```

**Step 2: Run dev server and verify end-to-end**

```bash
composer dev
```

1. Navigate to `/dashboard` → verify dashboard still looks correct
2. Click "Users" in the nav → URL changes to `/dashboard/users`, Users page loads
3. Verify table loads (empty state if no users)
4. Click "Add User" → modal opens with all fields
5. Create a user → row appears in table
6. Click "Edit" → modal pre-fills with user data
7. Update user → row updates in table
8. Click "Delete" → SweetAlert2 confirm dialog appears → on confirm, row removed

**Step 3: Run full test suite**

```bash
php artisan config:clear && php artisan test
```

Expected: all tests pass.

**Step 4: Final commit**

```bash
git add resources/js/router/index.js resources/js/views/Users/Users.vue
git commit -m "add Users CRUD page with search, pagination, and modal create/edit/delete"
```
