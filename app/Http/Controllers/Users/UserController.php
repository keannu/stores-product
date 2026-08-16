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
            $request->filled('store_id') ? (int) $request->input('store_id') : null,
            $request->string('role')->toString(),
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

    public function changePassword(Request $request, int $id): JsonResponse
    {
        return $this->userService->changePassword($id, $request->input('password'));
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->userService->destroy($id);
    }
}
