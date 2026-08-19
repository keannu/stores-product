<?php

namespace Tests\Unit\Services\Stores;

use App\Models\Store;
use App\Services\Stores\StoreService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreServiceTest extends TestCase
{
    use RefreshDatabase;

    private StoreService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StoreService();
    }

    // --- index ---

    public function test_index_returns_paginated_stores(): void
    {
        Store::factory()->count(3)->create();

        $response = $this->service->index();
        $data     = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(3, $data['data']);
        $this->assertSame(3, $data['meta']['total']);
        $this->assertSame(1, $data['meta']['current_page']);
    }

    public function test_index_filters_by_store_name(): void
    {
        Store::factory()->create(['store_name' => 'Alpha Store']);
        Store::factory()->create(['store_name' => 'Beta Shop']);

        $response = $this->service->index('Alpha');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('Alpha Store', $data['data'][0]['store_name']);
    }

    public function test_index_filters_by_email(): void
    {
        Store::factory()->create(['email' => 'alpha@example.com']);
        Store::factory()->create(['email' => 'beta@example.com']);

        $response = $this->service->index('alpha');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('alpha@example.com', $data['data'][0]['email']);
    }

    public function test_index_filters_owner_by_name(): void
    {
        Store::factory()->create(['owner_name' => 'John Doe']);
        Store::factory()->create(['owner_name' => 'Jane Smith']);

        $response = $this->service->index('', 'John');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('John Doe', $data['data'][0]['owner_name']);
    }

    public function test_index_filters_owner_by_address(): void
    {
        Store::factory()->create(['address' => '123 Main Street']);
        Store::factory()->create(['address' => '456 Oak Avenue']);

        $response = $this->service->index('', 'Main');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame('123 Main Street', $data['data'][0]['address']);
    }

    public function test_index_does_not_include_soft_deleted_stores(): void
    {
        $store = Store::factory()->create();
        $store->delete();

        $response = $this->service->index();
        $data     = $response->getData(true);

        $this->assertSame(0, $data['meta']['total']);
    }

    public function test_index_returns_only_soft_deleted_when_status_inactive(): void
    {
        Store::factory()->create();
        $deleted = Store::factory()->create();
        $deleted->delete();

        $response = $this->service->index('', '', 1, 'inactive');
        $data     = $response->getData(true);

        $this->assertCount(1, $data['data']);
        $this->assertSame($deleted->id, $data['data'][0]['id']);
    }

    public function test_index_returns_all_when_status_all(): void
    {
        Store::factory()->create();
        $deleted = Store::factory()->create();
        $deleted->delete();

        $response = $this->service->index('', '', 1, 'all');
        $data     = $response->getData(true);

        $this->assertCount(2, $data['data']);
    }

    public function test_index_paginates_at_15_per_page(): void
    {
        Store::factory()->count(20)->create();

        $response = $this->service->index();
        $data     = $response->getData(true);

        $this->assertCount(15, $data['data']);
        $this->assertSame(15, $data['meta']['per_page']);
        $this->assertSame(2, $data['meta']['last_page']);
    }

    public function test_index_respects_page_parameter(): void
    {
        Store::factory()->count(20)->create();

        $response = $this->service->index('', '', 2);
        $data     = $response->getData(true);

        $this->assertCount(5, $data['data']);
        $this->assertSame(2, $data['meta']['current_page']);
    }

    // --- store ---

    public function test_store_creates_a_store(): void
    {
        $response = $this->service->store([
            'store_name'    => 'Test Store',
            'description'   => 'A test store',
            'address'       => '123 Test St',
            'owner_name'    => 'John Doe',
            'mobile_number' => '09171234567',
            'email'         => 'test@store.com',
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertDatabaseHas('stores', [
            'store_name' => 'Test Store',
            'email'      => 'test@store.com',
            'address'    => '123 Test St',
            'owner_name' => 'John Doe',
        ]);
    }

    public function test_store_defaults_redirect_links_to_slash(): void
    {
        $this->service->store([
            'store_name'    => 'Test Store',
            'address'       => '123 Test St',
            'owner_name'    => 'John Doe',
            'mobile_number' => '09171234567',
            'email'         => 'test@store.com',
        ]);

        $this->assertDatabaseHas('stores', [
            'email'                  => 'test@store.com',
            'admin_redirect_link'    => '/',
            'customer_redirect_link' => '/',
        ]);
    }

    public function test_store_accepts_custom_redirect_links(): void
    {
        $this->service->store([
            'store_name'             => 'Test Store',
            'address'                => '123 Test St',
            'owner_name'             => 'John Doe',
            'mobile_number'          => '09171234567',
            'email'                  => 'test@store.com',
            'admin_redirect_link'    => '/admin',
            'customer_redirect_link' => '/shop',
        ]);

        $this->assertDatabaseHas('stores', [
            'email'                  => 'test@store.com',
            'admin_redirect_link'    => '/admin',
            'customer_redirect_link' => '/shop',
        ]);
    }

    public function test_store_allows_null_description(): void
    {
        $this->service->store([
            'store_name'    => 'Test Store',
            'address'       => '123 Test St',
            'owner_name'    => 'John Doe',
            'mobile_number' => '09171234567',
            'email'         => 'test@store.com',
        ]);

        $store = Store::where('email', 'test@store.com')->first();
        $this->assertNull($store->description);
    }

    // --- update ---

    public function test_update_changes_store_fields(): void
    {
        $store = Store::factory()->create(['store_name' => 'Old Name']);

        $this->service->update($store->id, [
            'store_name'    => 'New Name',
            'description'   => 'Updated',
            'address'       => $store->address,
            'owner_name'    => $store->owner_name,
            'mobile_number' => $store->mobile_number,
            'email'         => $store->email,
        ]);

        $this->assertDatabaseHas('stores', [
            'id'          => $store->id,
            'store_name'  => 'New Name',
            'description' => 'Updated',
        ]);
    }

    public function test_update_returns_fresh_store(): void
    {
        $store = Store::factory()->create(['store_name' => 'Old Name']);

        $response = $this->service->update($store->id, [
            'store_name'    => 'New Name',
            'description'   => null,
            'address'       => $store->address,
            'owner_name'    => $store->owner_name,
            'mobile_number' => $store->mobile_number,
            'email'         => $store->email,
        ]);

        $data = $response->getData(true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('New Name', $data['store_name']);
    }

    public function test_update_defaults_redirect_links_to_slash(): void
    {
        $store = Store::factory()->create([
            'admin_redirect_link'    => '/admin',
            'customer_redirect_link' => '/shop',
        ]);

        $this->service->update($store->id, [
            'store_name'    => $store->store_name,
            'address'       => $store->address,
            'owner_name'    => $store->owner_name,
            'mobile_number' => $store->mobile_number,
            'email'         => $store->email,
        ]);

        $this->assertDatabaseHas('stores', [
            'id'                     => $store->id,
            'admin_redirect_link'    => '/',
            'customer_redirect_link' => '/',
        ]);
    }

    public function test_update_accepts_custom_redirect_links(): void
    {
        $store = Store::factory()->create();

        $this->service->update($store->id, [
            'store_name'             => $store->store_name,
            'address'                => $store->address,
            'owner_name'             => $store->owner_name,
            'mobile_number'          => $store->mobile_number,
            'email'                  => $store->email,
            'admin_redirect_link'    => '/admin',
            'customer_redirect_link' => '/shop',
        ]);

        $this->assertDatabaseHas('stores', [
            'id'                     => $store->id,
            'admin_redirect_link'    => '/admin',
            'customer_redirect_link' => '/shop',
        ]);
    }

    public function test_update_throws_for_nonexistent_store(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->update(999, [
            'store_name'    => 'X',
            'address'       => 'X',
            'owner_name'    => 'X',
            'mobile_number' => 'X',
            'email'         => 'x@x.com',
        ]);
    }

    // --- destroy ---

    public function test_destroy_soft_deletes_store(): void
    {
        $store = Store::factory()->create();

        $response = $this->service->destroy($store->id);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSoftDeleted('stores', ['id' => $store->id]);
    }

    public function test_destroy_returns_success_message(): void
    {
        $store = Store::factory()->create();

        $response = $this->service->destroy($store->id);
        $data     = $response->getData(true);

        $this->assertSame('Store deleted', $data['message']);
    }

    public function test_destroy_throws_for_nonexistent_store(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->destroy(999);
    }
}
