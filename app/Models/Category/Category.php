<?php

namespace App\Models\Category;

use App\Models\Image\CategoryImage\CategoryImage;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'category_image_id'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    public function category_image()
    {
        return $this->belongsTo(CategoryImage::class);
    }

    public function category_translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }
}
