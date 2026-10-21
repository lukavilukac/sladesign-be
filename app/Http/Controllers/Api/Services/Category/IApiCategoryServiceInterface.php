<?php

namespace App\Http\Controllers\Api\Services\Category;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Collection;

interface IApiCategoryServiceInterface
{
    public function getCategories(int|null $limit): Collection;

    public function findCategoryBySlug(string $slug): Category;
}
