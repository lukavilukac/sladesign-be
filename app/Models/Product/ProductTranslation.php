<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = ['name', 'description', 'slug', 'locale', 'product_id'];
    /**
     * @var bool
     */
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
