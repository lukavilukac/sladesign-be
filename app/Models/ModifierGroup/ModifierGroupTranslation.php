<?php

namespace App\Models\ModifierGroup;

use Illuminate\Database\Eloquent\Model;

class ModifierGroupTranslation extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = ['name', 'locale', 'modifier_group_id'];
    /**
     * @var bool
     */
    public $timestamps = false;

    public function modifier_group()
    {
        return $this->belongsTo(ModifierGroup::class);
    }
}
