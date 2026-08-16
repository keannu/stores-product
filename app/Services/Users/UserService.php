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

    public function stores(): JsonResponse
    {
        return response()->json(Store::all(['id', 'store_name']));
    }

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

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }
}
