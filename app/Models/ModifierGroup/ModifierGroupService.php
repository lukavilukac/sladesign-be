<?php

namespace App\Models\ModifierGroup;

use App\Models\Category\CategoryTranslation;

class ModifierGroupService
{
    public function getAllModifierGroups()
    {
        return ModifierGroup::all();
    }

    public function getModifierGroupsForAdmin()
    {
        return ModifierGroup::orderBy('updated_at', 'desc')->get();
    }

    public function getModifierGroupById($id)
    {
        return ModifierGroup::findOrFail($id);
    }

    public function getModifierGroupForAdminById($id, $locale = 'en')
    {
        $modifierGroup = ModifierGroup::with([
            'modifier_group_translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }
        ])->find($id);

        return $this->translateForAdimn($modifierGroup, $locale);
    }

    public function createModifierGroup($data)
    {
        return ModifierGroup::create($data);
    }

    public function updateModifierGroup($id, $data, $locale='en')
    {
        $modifierGroup = ModifierGroup::findOrFail($id);
        $modifierGroup->update($data);
        ModifierGroupTranslation::updateOrCreate([
            'modifier_group_id' => $modifierGroup->id, 'locale' => $locale
        ],
            [
                'name' => $data['en_name'],
                'locale' => $locale,
                'modifier_group_id' => $modifierGroup->id
            ]
        );

        return $modifierGroup;
    }

    public function deleteModifierGroup($id)
    {
        $modifierGroup = ModifierGroup::findOrFail($id);
        $modifierGroup->delete();
    }

    private function translateForAdimn(ModifierGroup $modifierGroup, $locale = 'en')
    {
        $modifierGroup->{$locale . '_name'} = null;

        $translation = data_get($modifierGroup->modifier_group_translations, 0);
        if ($translation) {
            $modifierGroup->{$locale . '_name'} = $translation->name;
        }

        return $modifierGroup;
    }
}
