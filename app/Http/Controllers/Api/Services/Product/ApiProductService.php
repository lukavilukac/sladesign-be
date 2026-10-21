<?php

namespace App\Http\Controllers\Api\Services\Product;

use App\Models\Category\Category;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Collection;

class ApiProductService implements IApiProductServiceInterface
{
    public function getProducts(int|null $limit): Collection
    {
        return Product::orderBy('updated_at', 'desc')
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->with('product_image')->get();
    }

    public function findBySlug(string $slug): Product
    {
        return Product::with('product_image', 'modifier_groups.modifiers', 'product_images')
            ->where('slug', $slug)
            ->first();
    }

    public function getProductsByCategory(Category $category): \Illuminate\Support\Collection
    {
        return $category->products()
            ->with('product_image', 'product_images')
            ->get();
    }
}
