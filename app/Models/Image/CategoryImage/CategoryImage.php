<?php

namespace App\Models\Image\CategoryImage;

use Illuminate\Database\Eloquent\Model;

class CategoryImage extends Model
{
    protected $fillable = ['path', 'thumbnail_path', 'size', 'title', 'alt'];
}
