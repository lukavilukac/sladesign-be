<?php

namespace App\Models\Product;

use App\Models\Category\Category;
use App\Models\Image\ProductImage\ProductImage;
use App\Models\ModifierGroup\ModifierGroup;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price', 'product_image_id'];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function modifier_groups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'modifier_group_product');
    }

    public function product_image()
    {
        return $this->belongsTo(ProductImage::class);
    }

    public function product_images()
    {
        return $this->belongsToMany(ProductImage::class, 'product_product_image', 'product_id', 'product_image_id');
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }
}
