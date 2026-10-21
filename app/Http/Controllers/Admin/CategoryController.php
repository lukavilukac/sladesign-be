<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        return Inertia::render('Admin/Categories/Categories');
    }

    public function newCategory()
    {
        return Inertia::render('Admin/Categories/CategoryCreate');
    }

    public function all()
    {
        $categories = $this->categoryService->getCategoriesForAdmin();

        return response()->json(['json' => $categories]);
    }

    public function show($id)
    {
        $category = $this->categoryService->getCategoryForAdminById($id);

        return Inertia::render('Admin/Categories/CategoryUpdate', ['category' => $category]);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u', 'unique:categories,name'],
            'description' => ['required', 'string'],
            'slug' => 'nullable|alpha_dash:ascii|lowercase',
            'category_image_id' => 'required|exists:category_images,id',
        ]);

        $category = $this->categoryService->createCategory($data);

        session()->flash('message', 'Category ' . Str::upper($category->name) . ' created!');

        return to_route('admin.categories.index');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u', 'unique:categories,name,'.$id],
            'en_name' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('category_translations', 'name')
                    ->where('locale', 'en')
                    ->ignore($id, 'category_id')
                    ->where(function ($query) use ($request, $id) {
                        $query->where('name', $request->name)
                            ->where('locale', 'en');
                    }),
            ],
            'description' => ['required', 'string'],
            'en_description' => ['required', 'string'],
            'slug' => 'required|alpha_dash:ascii|lowercase',
            'en_slug' => 'required|alpha_dash:ascii|lowercase',
            'category_image_id' => 'required|exists:category_images,id',
        ]);

        $category = $this->categoryService->updateCategory($id, $data);

        session()->flash('message', 'Category ' . Str::upper($category->name) . ' updated!');

        return to_route('admin.categories.index');
    }

    public function delete($id)//todo
    {

    }
}
