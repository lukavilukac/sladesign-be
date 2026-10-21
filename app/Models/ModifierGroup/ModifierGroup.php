<?php

namespace App\Models\ModifierGroup;

use App\Models\Modifier\Modifier;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = ['product_id', 'name', 'required', 'is_multi'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiers()
    {
        return $this->hasMany(Modifier::class);
    }

    public function modifier_group_translations()
    {
        return $this->hasMany(ModifierGroupTranslation::class);
    }
}
