<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Category\DestroyCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestroyCategoryController extends Controller
{
    public function __construct(
        private readonly DestroyCategoryService $destroyCategoryService
    ) {}

    public function __invoke(Request $request, Category $category): JsonResponse
    {
        $this->destroyCategoryService->execute($request->user(), $category);

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria excluída com sucesso!',
        ]);
    }
}
