<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Services\Category\IApiCategoryServiceInterface;
use Illuminate\Http\Request;

class CategoryController
{
    public function __construct(private IApiCategoryServiceInterface $categoryService) {}

    public function all(Request $request)
    {
        $limit = $request->get('limit', 5);

        $categories = $this->categoryService->getCategories($limit);

        return response()->json($categories);
    }
}
