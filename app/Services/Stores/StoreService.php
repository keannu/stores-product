<?php

namespace App\Services\Stores;

use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreService
{
    public function index(string $search = '', string $searchOwner = '', int $page = 1, string $status = 'active'): JsonResponse
    {
        $query = Store::query();

        if ($status === 'inactive') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($searchOwner !== '') {
            $query->where(function ($q) use ($searchOwner) {
                $q->where('owner_name', 'like', "%{$searchOwner}%")
                  ->orWhere('address', 'like', "%{$searchOwner}%");
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

    public function store(array $data): JsonResponse
    {
        $store = Store::create([
            'store_name'             => $data['store_name'],
            'description'            => $data['description'] ?? null,
            'address'                => $data['address'],
            'owner_name'             => $data['owner_name'],
            'mobile_number'          => $data['mobile_number'],
            'email'                  => $data['email'],
            'admin_redirect_link'    => $data['admin_redirect_link'] ?? '/',
            'customer_redirect_link' => $data['customer_redirect_link'] ?? '/',
        ]);

        return response()->json($store, 201);
    }

    public function update(int $id, array $data): JsonResponse
    {
        $store = Store::findOrFail($id);

        $store->update([
            'store_name'             => $data['store_name'],
            'description'            => $data['description'] ?? null,
            'address'                => $data['address'],
            'owner_name'             => $data['owner_name'],
            'mobile_number'          => $data['mobile_number'],
            'email'                  => $data['email'],
            'admin_redirect_link'    => $data['admin_redirect_link'] ?? '/',
            'customer_redirect_link' => $data['customer_redirect_link'] ?? '/',
        ]);

        return response()->json($store->fresh());
    }

    public function destroy(int $id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $store->delete();

        return response()->json(['message' => 'Store deleted']);
    }
}
