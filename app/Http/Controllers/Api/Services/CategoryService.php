<?php

namespace App\Http\Controllers\Api\Services;

use App\Models\Category\Category;

class CategoryService
{
    public function getCategoriesForApi($limit)
    {
        $categoryQuery = Category::with('category_image');
        if ($limit) {
            $categoryQuery->limit($limit);
        }
        return $categoryQuery->get();
    }

    public function getCategoryBySlug(string $slug)
    {
        return Category::where('slug', $slug)->firstOrFail();
    }
}
