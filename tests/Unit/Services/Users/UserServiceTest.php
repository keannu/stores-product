<?php

namespace Tests\Unit\Services\Users;

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

    // --- destroy ---

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
}
