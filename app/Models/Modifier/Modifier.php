<?php

namespace App\Models\Modifier;

use App\Models\ModifierGroup\ModifierGroup;
use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $fillable = ['modifier_group_id', 'value', 'price', 'description'];

    protected $appends = ['modifier_group_name'];

    public function modifierGroup()
    {
        return $this->belongsTo(ModifierGroup::class);
    }

    public function getModifierGroupNameAttribute()
    {
        return $this->modifierGroup->name;
    }

    public function modifier_translations()
    {
        return $this->hasMany(ModifierTranslation::class);
    }

}
