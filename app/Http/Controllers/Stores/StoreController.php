<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Services\Stores\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(private StoreService $storeService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->storeService->index(
            $request->string('search')->toString(),
            (int) $request->input('page', 1),
            $request->string('status', 'active')->toString(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storeService->store($request->all());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->storeService->update($id, $request->all());
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->storeService->destroy($id);
    }
}
