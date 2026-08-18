<?php

namespace App\Services\Users;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UserService
{
    public function index(string $search = '', int $page = 1, ?int $storeId = null, string $role = '', string $authRole = '', string $status = 'active', ?int $authStoreId = null): JsonResponse
    {
        $query = User::query();

        if ($status === 'inactive') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        if ($authRole !== 'super_admin') {
            $query->where('role', '!=', 'super_admin');
            if ($authStoreId !== null) {
                $query->where('store_id', $authStoreId);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($storeId !== null) {
            $query->where('store_id', $storeId);
        }

        if ($role !== '') {
            $query->where('role', $role);
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

    public function stores(): JsonResponse
    {
        return response()->json(Store::all(['id', 'store_name']));
    }

    public function store(array $data): JsonResponse
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'] ?? Str::random(16),
            'role'     => $data['role'],
            'store_id' => $data['store_id'] ?? null,
        ]);

        return response()->json($user, 201);
    }

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

    public function changePassword(int $id, string $password): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['password' => $password]);

        return response()->json(['message' => 'Password updated']);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}
