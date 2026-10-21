<?php

namespace App\Models\Category;

use App\Helpers\StringHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function getAllCategories()
    {
        return Category::all();
    }

    public function getCategoriesForAdmin()
    {
        return Category::orderBy('updated_at', 'desc')->with('category_image')->get();
    }

    public function getCategoryById($id)
    {
        return Category::with('category_image')->findOrFail($id);
    }

    public function createCategory($data)
    {
        if (empty($data['slug'])) {
            $data['slug'] = StringHelper::kebab($data['name']);
        }

        return Category::create($data);
    }

    public function updateCategory($id, $data, $locale = 'en')
    {
        return DB::transaction(function () use ($id, $data, $locale) {
            $category = Category::findOrFail($id);
            $category->update($data);

            CategoryTranslation::updateOrCreate([
                'category_id' => $category->id, 'locale' => $locale
            ],
                [
                    'name' => $data['en_name'],
                    'description' => $data['en_description'],
                    'slug' => $data['en_slug'],
                    'locale' => $locale,
                    'category_id' => $category->id
                ]
            );

            return $category;
        });
    }

    public function getCategoryForAdminById($id, $locale = 'en')
    {
        $category = Category::with([
            'category_image',
            'category_translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }
        ])->find($id);

        return $this->translateForAdimn($category, $locale);
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
    }

    private function translateForAdimn(Category $category, $locale = 'en')
    {
        $category->{$locale . '_name'} = null;
        $category->{$locale .  '_description'} = null;
        $category->{$locale .  '_slug'} = null;

        $translation = data_get($category->category_translations, 0);
        if ($translation) {
            $category->{$locale . '_name'} = $translation->name;
            $category->{$locale .  '_description'} = $translation->description;
            $category->{$locale .  '_slug'} = $translation->slug;
        }

        return $category;
    }
}
