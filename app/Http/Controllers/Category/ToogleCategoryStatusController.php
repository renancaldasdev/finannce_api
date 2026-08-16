<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Category\ToogleCategoryStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToogleCategoryStatusController extends Controller
{
    public function __construct(
        private readonly ToogleCategoryStatusService $deactiveCategory
    ) {}

    public function __invoke(Request $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->deactiveCategory->execute(
            user: $request->user(),
            category: $category
        );

        $statusMessage = $updatedCategory->active ? 'Ativada' : 'Desativada';

        return response()->json([
            'success' => true,
            'message' => "Categoria {$statusMessage}!",
            'data' => new CategoryResource($updatedCategory),
        ], 200);
    }
}
