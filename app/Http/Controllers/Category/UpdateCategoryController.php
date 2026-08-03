<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\Category\UpdateCategoryService;
use Illuminate\Http\Request;

class UpdateCategoryController extends Controller
{
    public function __construct(
        private UpdateCategoryService $updateCategoryService
    ) {}

    public function __invoke(UpdateCategoryRequest $request, Category $category)
    {
        $updateCategory = $this->updateCategoryService->execute(
            user: $request->user(),
            category: $category,
            data: $request->validated()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Categoria atualizada com sucesso!',
            'data' => new CategoryResource($updateCategory),
        ]);
    }
}
