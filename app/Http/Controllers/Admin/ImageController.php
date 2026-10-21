<?php

namespace App\Http\Controllers\Admin;

use App\Models\Image\CategoryImage\CategoryImage;
use App\Models\Image\CategoryImage\ImageCategoryUploadService;
use App\Models\Image\ProductImage\ImageProductUploadService;
use App\Models\Image\ProductImage\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImageController
{

    private array $conf = [
        'product' => [
            'class' => ProductImage::class,
            'service' => ImageProductUploadService::class,
            'ratioWidth' => 4,
            'ratioHeight' => 5
        ],
        'category' => [
            'class' => CategoryImage::class,
            'service' => ImageCategoryUploadService::class,
            'ratioWidth' => 4,
            'ratioHeight' => 5
        ]
    ];

    public function index($entity)
    {
        $images = $this->conf[$entity]['class']::orderBy('id', 'desc')->get();

        return Inertia::render('Admin/Images/Images', ['images' => $images, 'ratioWidth' => $this->conf[$entity]['ratioWidth'], 'ratioHeight' => $this->conf[$entity]['ratioHeight'], 'entity' => $entity]);
    }

    public function all($entity)
    {
        $images = $this->conf[$entity]['class']::orderBy('id', 'desc')->get();

        return response()->json(['json' => $images]);
    }

    public function upload($entity, Request $request)
    {
        $file = $request->file('image');
        $alt = $request->get('alt');

        $uploadService = new $this->conf[$entity]['service'];

        $uploadService->upload($file, $alt, true);

        session()->flash('message', ucfirst($entity) . ' Image successfully Uploaded!');

        return to_route('admin.images.index', $entity);
    }

    public function delete($entity, $id)
    {
        //todo - check if image is connected to some product or category...
        $image = ProductImage::find($id);
        if (!$image) {
            $image = CategoryImage::find($id);
        }
        Storage::disk('public')->delete($entity . '/' . $image->title);
        if (!empty($image->thumbnail_path)) {
            Storage::disk('public')->delete($entity . '/thumbnails/' . $image->title);
        }
        $image->delete();

        session()->flash('message', 'Image is deleted successfully!');

        return to_route('admin.images.index', $entity);
    }
}
