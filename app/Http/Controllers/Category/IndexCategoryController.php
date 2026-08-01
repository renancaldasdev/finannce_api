<?php

declare(strict_types=1);

namespace App\Http\Controllers\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\Category\IndexCategoryService;

class IndexCategoryController extends Controller
{
    public function __construct(
        private IndexCategoryService $indexCategoryService
    ) {}
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $categories = $this->indexCategoryService->execute($user);

        return response()->json([
            'status' => 'success',
            'data' => CategoryResource::collection($categories)
        ]);
    }
}
