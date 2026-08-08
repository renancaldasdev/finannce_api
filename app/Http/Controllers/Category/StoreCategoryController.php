<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\Category\StoreCategoryService;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    public function __construct(
        private StoreCategoryService $storeCategoryService
    ) {}

    public function __invoke(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        $category = $this->storeCategoryService->execute(
            $request->user(),
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Categoria criada com sucesso!',
            'data' => new CategoryResource($category),
        ], 201);
    }
}
