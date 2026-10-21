<?php

namespace App\Http\Controllers\Api\Services\Product;

use App\Models\Category\Category;
use App\Models\Product\Product;
use Illuminate\Support\Collection;

interface IApiProductServiceInterface
{
    public function getProducts(int|null $limit): Collection;

    public function findBySlug(string $slug): Product;

    public function getProductsByCategory(Category $category): Collection;
}
