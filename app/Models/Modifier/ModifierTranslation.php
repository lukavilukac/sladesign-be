<?php

namespace App\Models\Modifier;

use Illuminate\Database\Eloquent\Model;

class ModifierTranslation extends Model
{
    protected $fillable = ['name', 'locale', 'description', 'modifier_id'];

    public $timestamps = false;

    public function modifier()
    {
        return $this->belongsTo(Modifier::class);
    }
}
