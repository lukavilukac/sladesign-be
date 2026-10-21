<?php

namespace App\Http\Controllers\Api\Services\Category;

use App\Models\Category\Category;
use App\Models\Category\CategoryTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class EnApiCategoryService implements IApiCategoryServiceInterface
{
    public function getCategories(int|null $limit): Collection
    {
        return Category::with([
            'category_image',
            'category_translations' => function ($query) {
                $query->where('locale', 'en');
            }
        ])
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->get()->map(function ($category) {
                $translation = $category->category_translations->first();
                if ($translation) {
                    $category->name = $translation->name;
                    $category->slug = $translation->slug;
                    $category->description = $translation->description;
                }

                return $category;
            } );
    }

    public function findCategoryBySlug(string $slug): Category
    {
        $categoryTranslation = CategoryTranslation::with('category')
            ->where('slug', $slug)
            ->where('locale', 'en')
            ->first();

        $category = $categoryTranslation->category->load(['category_image']);

        $category->name = $categoryTranslation->name ?: $category->name;
        $category->slug = $categoryTranslation->slug ?: $category->slug;
        $category->description = $categoryTranslation->description ?: $category->description;

        return $category;
    }
}
