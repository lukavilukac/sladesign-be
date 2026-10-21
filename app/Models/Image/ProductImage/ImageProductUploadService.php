<?php

namespace App\Models\Image\ProductImage;

use App\Models\Image\ImageUploadService;
use Illuminate\Http\UploadedFile;

class ImageProductUploadService extends ImageUploadService
{
    const ENTITY = 'product';

    public function upload(UploadedFile $file, $alt, $storeThumbnail = false)
    {
        $uploadedImage = parent::upload($file, $alt, $storeThumbnail);

        return ProductImage::create([
            'path' => $uploadedImage->path,
            'thumbnail_path' => $uploadedImage?->thumbnail_path,
            'size' => $uploadedImage->size,
            'title' => $uploadedImage->title,
            'alt' => $alt
        ]);
    }
}
