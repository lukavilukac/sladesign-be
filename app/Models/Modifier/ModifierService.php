<?php

namespace App\Models\Modifier;

use App\Models\ModifierGroup\ModifierGroup;
use App\Models\ModifierGroup\ModifierGroupTranslation;

class ModifierService
{
    public function getAllModifiers()
    {
        return Modifier::all();
    }

    public function getModifiersForAdmin()
    {
        return Modifier::orderBy('updated_at', 'desc')->get();
    }

    public function getModifierById($id)
    {
        return Modifier::findOrFail($id);
    }

    public function createModifier($data)
    {
        if (empty($data['price'])) {
            $data['price'] = 0.00;
        }

        return Modifier::create($data);
    }

    public function updateModifier($id, $data, $locale = 'en')
    {
        if (empty($data['price'])) {
            $data['price'] = 0.00;
        }
        $modifier = Modifier::findOrFail($id);
        $modifier->update($data);

        ModifierTranslation::updateOrCreate([
            'modifier_id' => $modifier->id, 'locale' => $locale
        ],
            [
                'name' => $data['en_value'],
                'description' => $data['en_description'],
                'locale' => $locale,
                'modifier_id' => $modifier->id
            ]
        );

        return $modifier;
    }

    public function getModifierForAdminById($id, $locale = 'en')
    {
        $modifier = Modifier::with([
            'modifier_translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }
        ])->find($id);

        return $this->translateForAdimn($modifier, $locale);
    }

    public function deleteModifier($id)
    {
        $modifier = Modifier::findOrFail($id);
        $modifier->delete();
    }

    private function translateForAdimn(Modifier $modifier, $locale = 'en')
    {
        $modifier->{$locale . '_value'} = null;

        $translation = data_get($modifier->modifier_translations, 0);
        if ($translation) {
            $modifier->{$locale . '_value'} = $translation->name;
            $modifier->{$locale . '_description'} = $translation->description;
        }

        return $modifier;
    }
}
