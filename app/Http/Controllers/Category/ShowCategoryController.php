<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Category\ShowCategoryService;
use Illuminate\Http\Request;

class ShowCategoryController extends Controller
{
    public function __construct(
        private readonly ShowCategoryService $showCategoryService
    ) {}

    public function __invoke(Request $request, Category $category)
    {
        $validatedCategory = $this->showCategoryService->execute(
            user: $request->user(),
            category: $category
        );

        return response()->json([
            'status' => 'success',
            'data' => new CategoryResource($validatedCategory)
        ]);
    }
}
