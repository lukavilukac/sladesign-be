<?php

namespace App\Models\Category;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = ['name', 'description', 'slug', 'locale', 'category_id'];
    /**
     * @var bool
     */
    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
