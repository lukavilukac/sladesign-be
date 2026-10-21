<?php

namespace App\Models\Product;

use App\Helpers\StringHelper;
use App\Models\Category\Category;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getAllProducts()
    {
        return Product::all();
    }

    public function getProductsForAdmin()
    {
        return Product::orderBy('updated_at', 'desc')->with('product_image')->get();
    }

    public function getProductsForApi($limit)
    {
        return Product::orderBy('updated_at', 'desc')->limit($limit)->with('product_image')->get();
    }

    public function findProductByTranslatedSlug($translatedSlug, $locale = 'en')
    {
        $productTranslation = ProductTranslation::with('product')
            ->where('slug', $translatedSlug)
            ->where('locale', $locale)
            ->first();

        $product = $productTranslation->product->load(['product_image', 'product_images']);

        $product->name = $productTranslation->name ?: $product->name;
        $product->slug = $productTranslation->slug ?: $product->slug;
        $product->description = $productTranslation->description ?: $product->description;

        return $product;
    }

    public function findBySlug($slug)
    {
        return Product::with('product_image', 'modifier_groups', 'product_images')
            ->where('slug', $slug)
            ->first();
    }

    public function getProductsByCategory(Category $category)
    {
        return $category->products()
            ->with('product_image', 'product_images')
            ->get();
    }

    public function getProductForAdminById($id, $locale = 'en')
    {
        $product = Product::with([
            'categories',
            'modifier_groups',
            'product_image',
            'product_images',
            'product_translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }
        ])->find($id);

        return $this->translateForAdimn($product, $locale);
    }

    private function translateForAdimn(Product $product, $locale = 'en')
    {
        $product->{$locale . '_name'} = null;
        $product->{$locale .  '_description'} = null;
        $product->{$locale .  '_slug'} = null;

        $translation = data_get($product->product_translations, 0);
        if ($translation) {
            $product->{$locale . '_name'} = $translation->name;
            $product->{$locale .  '_description'} = $translation->description;
            $product->{$locale .  '_slug'} = $translation->slug;
        }

        return $product;
    }

    public function createProduct($data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = StringHelper::kebab($data['name']);
        }

        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            $modifier_group_ids = data_get($data, 'modifier_group_ids');
            if (!is_null($modifier_group_ids)) {
                $product->modifier_groups()->sync($modifier_group_ids);
            }
            $category_ids = data_get($data, 'category_ids');
            if (!is_null($category_ids)) {
                $product->categories()->sync($category_ids);
            }
            $product_image_ids = data_get($data, 'product_image_ids');
            if (!is_null($product_image_ids)) {
                $product->product_images()->sync($product_image_ids);
            }

            return $product;
        });
    }

    public function updateProduct($id, $data, $locale = 'en')
    {
        /** @var Product $product */
        $product = Product::findOrFail($id);
        if (empty($data['slug'])) {
            $data['slug'] = StringHelper::kebab($data['name']);
        }

        return DB::transaction(function () use ($product, $data, $locale){
            $product->update($data);

            $modifier_group_ids = data_get($data, 'modifier_group_ids', []);
            $product->modifier_groups()->sync($modifier_group_ids);

            $category_ids = data_get($data, 'category_ids', []);
            $product->categories()->sync($category_ids);

            $product_image_ids = data_get($data, 'product_image_ids', []);
            $product->product_images()->sync($product_image_ids);

            //for now only one lang is supported
            ProductTranslation::updateOrCreate([
                    'product_id' => $product->id, 'locale' => $locale
                ],
                [
                    'name' => $data['en_name'],
                    'description' => $data['en_description'] ?? '',
                    'slug' => $data['en_slug'],
                    'locale' => $locale,
                    'product_id' => $product->id
                ]
            );

            return $product;
        });
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    }
}
