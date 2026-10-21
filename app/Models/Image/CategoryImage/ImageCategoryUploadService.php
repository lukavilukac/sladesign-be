<?php

namespace App\Models\Image\CategoryImage;

use App\Models\Image\ImageUploadService;
use Illuminate\Http\UploadedFile;

class ImageCategoryUploadService extends ImageUploadService
{
    const ENTITY = 'category';

    public function upload(UploadedFile $file, $alt, $storeThumbnail = false)
    {
        $uploadedImage = parent::upload($file, $alt, $storeThumbnail);

        return CategoryImage::create([
            'path' => $uploadedImage->path,
            'thumbnail_path' => $uploadedImage?->thumbnail_path,
            'size' => $uploadedImage->size,
            'title' => $uploadedImage->title,
            'alt' => $alt
        ]);
    }
}
