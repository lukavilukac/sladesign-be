<?php

namespace App\Http\Controllers\Api\Services\Category;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Collection;
use function App\Helpers\public_asset;

class ApiCategoryService implements IApiCategoryServiceInterface
{
    public function getCategories(int|null $limit): Collection
    {
        $categoryQuery = Category::with('category_image');
        if ($limit) {
            $categoryQuery->limit($limit);
        }

        return $categoryQuery->get();
    }

    public function findCategoryBySlug(string $slug): Category
    {
        return Category::where('slug', $slug)->first();
    }
}
