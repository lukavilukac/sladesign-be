<?php

namespace App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use function App\Helpers\image_url;

class ImageUploadService
{
    protected string $entity;

    public function upload(UploadedFile $file, $alt, $storeThumbnail = false)
    {
        $path = $file->store(static::ENTITY, 'public');
        $name = basename($path);

        $response = (object) [
            'path' => image_url($path),
            'title' => $name,
            'size' => $this->formatSize(Storage::disk('public')->size($path)),
            'thumbnail_path' => null,
        ];

        if ($storeThumbnail) {
            $response->thumbnail_path = $this->storeThumbnail($name);
        }

        return $response;
    }

    protected function storeThumbnail(string $name, $width = 100, $height = 100)
    {
        $file = Storage::disk('public')->get(static::ENTITY . '/' . $name);
        $image = (new ImageManager(Driver::class))->decodeBinary($file);
        $thumbnail = $image->cover($width, $height);
        Storage::disk('public')->put(
            static::ENTITY . '/thumbnails/' . $name,
            (string) $thumbnail->encodeUsingFileExtension(pathinfo($name, PATHINFO_EXTENSION)),
        );

        return image_url(static::ENTITY. '/thumbnails/' . $name);
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
