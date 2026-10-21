<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Services\Category\IApiCategoryServiceInterface;
use App\Http\Controllers\Api\Services\CategoryService;
use App\Http\Controllers\Api\Services\Product\IApiProductServiceInterface;
use App\Models\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ProductController
{
    public function __construct(private IApiProductServiceInterface $productService, private IApiCategoryServiceInterface $categoryService){}

    public function all(Request $request)
    {
        $limit = $request->get('limit');
        $products = $this->productService->getProducts($limit);

        return response()->json($products);
    }

    public function getByCategorySlug($slug, CategoryService $categoryService)
    {
        $category = $this->categoryService->findCategoryBySlug($slug);
        $products = $this->productService->getProductsByCategory($category);

        return response()->json($products);
    }

    public function getByCategorySlugEn($slug, CategoryService $categoryService, ProductService $productService)
    {
        $translated = $categoryService->findCategoryByTranslatedSlug($slug);
    }

    public function findBySlug($slug)
    {
        $product = $this->productService->findBySlug($slug);

        return response()->json($product);
    }

    public function findBySlugEn($slug, ProductService $productService)
    {
        $product = $productService->findProductByTranslatedSlug($slug, 'en');

        return response()->json($product);
    }

    public function getProductsAndCategories()
    {
        $products = $this->productService
            ->getProducts(null)
            ->map(function($product) {

                $product->type = App::getLocale() === 'en' ? 'product' : 'proizvod';

                $product->thumbnail_path = $product->product_image->thumbnail_path;
                unset($product->product_image);
                unset($product->product_image_id);
                unset($product->description);
                unset($product->created_at);
                unset($product->updated_at);
                unset($product->price);
                unset($product->product_translations);

                return $product;
            });

        $categories = $this->categoryService->getCategories(null)
            ->map(function($category) {
               $category->type = App::getLocale() === 'en' ? 'category' : 'kategorija';

               $category->thumbnail_path = $category->category_image->thumbnail_path;
                unset($category->category_image);
                unset($category->category_image_id);
                unset($category->created_at);
                unset($category->updated_at);
                unset($category->description);
                unset($category->category_translations);

                return $category;
            });

        $productsAndCategories = $products->concat($categories);

        return response()->json($productsAndCategories);
    }
}
