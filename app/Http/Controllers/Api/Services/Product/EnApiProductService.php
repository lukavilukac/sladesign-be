<?php

namespace App\Http\Controllers\Api\Services\Product;

use App\Models\Category\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductTranslation;
use Illuminate\Support\Collection;

class EnApiProductService implements IApiProductServiceInterface
{
    public function getProducts(int|null $limit): Collection
    {
        return Product::orderBy('updated_at', 'desc')
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            })
            ->with([
                'product_image',
                'product_translations' => function ($query) {
                    $query->where('locale', 'en');
                }
            ])
            ->get()
            ->map(function ($product) {
                $translation = $product->product_translations->first();
                if ($translation) {
                    $product->name = $translation->name;
                    $product->description = $translation->description;
                    $product->slug = $translation->slug;
                }

                return $product;
            });
    }

    public function findBySlug(string $slug): Product
    {
        $productTranslation = ProductTranslation::with('product.modifier_groups.modifiers')
            ->where('slug', $slug)
            ->where('locale', 'en')
            ->first();

        $product = $productTranslation->product->load(['product_image', 'product_images']);

        $product->name = $productTranslation->name ?: $product->name;
        $product->slug = $productTranslation->slug ?: $product->slug;
        $product->description = $productTranslation->description ?: $product->description;
        $this->translateModifiersAndModifierGroupsForProduct($product);

        return $product;
    }

    public function getProductsByCategory(Category $category): Collection
    {
        return $category->products()
            ->with([
                'product_image',
                'product_images',
                'product_translations' => function ($query) {
                    $query->where('locale', 'en');
                }
            ])
            ->get()->map(function ($product) {
                $translation = $product->product_translations->first();
                if ($translation) {
                    $product->name = $translation->name;
                    $product->description = $translation->description;
                    $product->slug = $translation->slug;
                }

                return $product;
            });
    }

    private function translateModifiersAndModifierGroupsForProduct(&$product)
    {
        foreach ($product->modifier_groups as $modifier_group) {
            $modifier_group->name = $modifier_group->modifier_group_translations()->where('locale', 'en')->first()->name;
            foreach ($modifier_group->modifiers as $modifier) {
                $modifier->modifier_translations()->where('locale', 'en')->first()->name;
                $modifier->value = $modifier->modifier_translations()->where('locale', 'en')->first()->name;
                $modifier->description = $modifier->modifier_translations()->where('locale', 'en')->first()->description;
            }
        }
    }

}
