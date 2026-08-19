<?php

namespace Tests\Unit\Services\Users;

use App\Models\Store;
use App\Models\User;
use App\Services\Users\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    // --- index ---

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

    public function test_index_returns_only_soft_deleted_when_status_inactive(): void
    {
        User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        $response = $this->service->index('', 1, null, '', '', 'inactive');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame($deleted->id, $data['data'][0]['id']);
    }

    public function test_index_returns_all_when_status_all(): void
    {
        User::factory()->create();
        $deleted = User::factory()->create();
        $deleted->delete();

        $response = $this->service->index('', 1, null, '', '', 'all');
        $data     = $response->getData(true);

        $this->assertCount(2, $data['data']);
    }

    public function test_index_filters_by_store_id(): void
    {
        $store = Store::factory()->create();
        User::factory()->create(['store_id' => $store->id]);
        User::factory()->create(['store_id' => null]);

        $response = $this->service->index('', 1, $store->id);
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame($store->id, $data['data'][0]['store_id']);
    }

    public function test_index_filters_by_role(): void
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'customer']);

        $response = $this->service->index('', 1, null, 'admin');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('admin', $data['data'][0]['role']);
    }

    public function test_index_hides_super_admins_for_non_super_admin_auth_role(): void
    {
        User::factory()->create(['role' => 'super_admin']);
        User::factory()->create(['role' => 'admin']);

        $response = $this->service->index('', 1, null, '', 'admin');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('admin', $data['data'][0]['role']);
    }

    public function test_index_scopes_to_auth_store_for_non_super_admin(): void
    {
        $store1 = Store::factory()->create();
        $store2 = Store::factory()->create();
        User::factory()->create(['role' => 'admin', 'store_id' => $store1->id]);
        User::factory()->create(['role' => 'admin', 'store_id' => $store2->id]);

        $response = $this->service->index('', 1, null, '', 'admin', 'active', $store1->id);
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame($store1->id, $data['data'][0]['store_id']);
    }

    public function test_index_shows_super_admins_when_auth_role_is_super_admin(): void
    {
        User::factory()->create(['role' => 'super_admin']);
        User::factory()->create(['role' => 'admin']);

        $response = $this->service->index('', 1, null, '', 'super_admin');
        $data     = $response->getData(true);

        $this->assertCount(2, $data['data']);
    }

    public function test_index_paginates_at_15_per_page(): void
    {
        User::factory()->count(20)->create();

        $response = $this->service->index();
        $data     = $response->getData(true);

        $this->assertCount(15, $data['data']);
        $this->assertSame(15, $data['meta']['per_page']);
        $this->assertSame(2, $data['meta']['last_page']);
    }

    public function test_index_respects_page_parameter(): void
    {
        User::factory()->count(20)->create();

        $response = $this->service->index('', 2);
        $data     = $response->getData(true);

        $this->assertCount(5, $data['data']);
        $this->assertSame(2, $data['meta']['current_page']);
    }

    // --- stores ---

    public function test_stores_returns_all_stores(): void
    {
        Store::factory()->count(3)->create();

        $response = $this->service->stores();
        $data     = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(3, $data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('store_name', $data[0]);
    }

    // --- store ---

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

    public function test_store_generates_random_password_when_omitted(): void
    {
        $this->service->store([
            'name'     => 'Alice',
            'email'    => 'alice@example.com',
            'role'     => 'customer',
            'store_id' => null,
        ]);

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($user->password);
    }

    // --- update ---

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
        $user = User::factory()->create(['password' => 'original', 'role' => 'customer']);
        $originalHash = $user->fresh()->password;

        $this->service->update($user->id, [
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => 'customer',
            'store_id' => null,
            'password' => '',
        ]);

        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_update_returns_fresh_user(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->service->update($user->id, [
            'name'     => 'New Name',
            'email'    => $user->email,
            'role'     => $user->role ?? 'customer',
            'store_id' => null,
        ]);

        $data = $response->getData(true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('New Name', $data['name']);
    }

    public function test_update_defaults_store_id_to_null(): void
    {
        $store = Store::factory()->create();
        $user  = User::factory()->create(['store_id' => $store->id]);

        $this->service->update($user->id, [
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role ?? 'customer',
        ]);

        $this->assertDatabaseHas('users', [
            'id'       => $user->id,
            'store_id' => null,
        ]);
    }

    public function test_update_changes_password_when_provided(): void
    {
        $user = User::factory()->create(['password' => 'original']);

        $this->service->update($user->id, [
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $user->role ?? 'customer',
            'store_id' => null,
            'password' => 'newpassword',
        ]);

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
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

    // --- changePassword ---

    public function test_change_password_updates_password(): void
    {
        $user = User::factory()->create(['password' => 'original']);

        $response = $this->service->changePassword($user->id, 'newpassword');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_change_password_returns_success_message(): void
    {
        $user = User::factory()->create();

        $response = $this->service->changePassword($user->id, 'newpassword');
        $data     = $response->getData(true);

        $this->assertSame('Password updated', $data['message']);
    }

    public function test_change_password_throws_for_nonexistent_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->changePassword(999, 'newpassword');
    }

    // --- destroy ---

    public function test_destroy_soft_deletes_user(): void
    {
        $user = User::factory()->create();

        $response = $this->service->destroy($user->id);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_destroy_returns_success_message(): void
    {
        $user = User::factory()->create();

        $response = $this->service->destroy($user->id);
        $data     = $response->getData(true);

        $this->assertSame('User deleted', $data['message']);
    }

    public function test_destroy_throws_for_nonexistent_user(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->destroy(999);
    }
}
