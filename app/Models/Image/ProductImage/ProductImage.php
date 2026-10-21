<?php

namespace App\Models\Image\ProductImage;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['path', 'thumbnail_path', 'size', 'title', 'alt'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_product_images', 'product_image_id', 'product_id');
    }
}
