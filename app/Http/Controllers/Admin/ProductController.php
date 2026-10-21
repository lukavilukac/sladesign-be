<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        return Inertia::render('Admin/Products/Products');
    }

    public function all()
    {
        $products = $this->productService->getProductsForAdmin();

        return response()->json(['json' => $products]);
    }

    public function show($id)
    {
        $product = $this->productService->getProductForAdminById($id);

        return Inertia::render('Admin/Products/ProductUpdate', ['product' => $product]);
    }

    public function newProduct()
    {
        return Inertia::render('Admin/Products/ProductCreate');
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u', 'unique:products,name'],
            'description' => ['required', 'string'],
            'slug' => 'nullable|alpha_dash:ascii|lowercase',
            'price' => ['required', 'numeric', 'regex:/^\d{1,6}(\.\d{1,2})?$/'],
            'modifier_group_ids.*' => 'nullable|exists:modifier_groups,id',
            'category_ids.*' => 'nullable|exists:categories,id',
            'product_image_id' => 'required|exists:product_images,id',
            'product_image_ids.*' => 'nullable|exists:product_images,id'
        ]);

        $product = $this->productService->createProduct($data);

        session()->flash('message', 'Product ' . Str::upper($product->name) . ' created!');

        return to_route('admin.products.index');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u', 'unique:products,name,'.$id],
            'en_name' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('product_translations', 'name')
                    ->where('product_id', $id)
                    ->where('locale', 'en')
                    ->ignore($id, 'product_id'),
                ],
            'description' => ['required', 'string'],
            'en_description' => 'nullable|string',
            'slug' => 'required|alpha_dash:ascii|lowercase',
            'en_slug' => [
                'required',
                'alpha_dash',
                'lowercase',
                Rule::unique('product_translations', 'slug')
                    ->where('locale', 'en')
                    ->ignore($id, 'product_id')
                    ->where(function ($query) use ($request, $id) {
                        $query->where('name', $request->name)
                            ->where('locale', 'en')
                            ->where('product_id', '<>', $id);
                    }),
            ],
            'price' => ['required', 'numeric', 'regex:/^\d{1,6}(\.\d{1,2})?$/'],
            'modifier_group_ids.*' => 'nullable|exists:modifier_groups,id',
            'category_ids.*' => 'nullable|exists:categories,id',
            'product_image_id' => 'required|exists:product_images,id',
            'product_image_ids.*' => 'nullable|exists:product_images,id'
        ]);

        $product = $this->productService->updateProduct($id, $data);

        session()->flash('message', 'Product ' . Str::upper($product->name) . ' updated!');

        return to_route('admin.products.index');
    }

    public function delete($id)//todo
    {

    }
}
