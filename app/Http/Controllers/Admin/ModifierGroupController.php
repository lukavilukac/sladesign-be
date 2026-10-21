<?php

namespace App\Http\Controllers\Admin;


use App\Models\ModifierGroup\ModifierGroupService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ModifierGroupController
{
    protected ModifierGroupService $modifierGroupService;

    public function __construct(ModifierGroupService $modifierGroupService)
    {
        $this->modifierGroupService = $modifierGroupService;
    }

    public function index()
    {
        return Inertia::render('Admin/ModifierGroups/ModifierGroups');
    }

    public function show($id)
    {
        $modifierGroup = $this->modifierGroupService->getModifierGroupForAdminById($id);

        return Inertia::render('Admin/ModifierGroups/ModifierGroupUpdate', ['modifierGroup' => $modifierGroup]);
    }

    public function newModifierGroup()
    {
        return Inertia::render('Admin/ModifierGroups/ModifierGroupCreate');
    }

    public function all()
    {
        $modifierGroups = $this->modifierGroupService->getModifierGroupsForAdmin();

        return response()->json(['json' => $modifierGroups]);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u', 'unique:modifier_groups,name'],
            'required' => 'required|boolean',
            'is_multi' => 'required|boolean'
        ]);

        $modifierGroup = $this->modifierGroupService->createModifierGroup($data);

        session()->flash('message', 'Modifier Group ' . Str::upper($modifierGroup->name) . ' created!');

        return to_route('admin.modifier-groups.index');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u', 'unique:modifier_groups,name,'.$id],
            'en_name' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('modifier_group_translations', 'name')
                    ->where('modifier_group_id', $id)
                    ->where('locale', 'en')
                    ->where(function ($query) use ($request) {
                        // Add the passed 'slug' value to the where condition
                        $query->where('name', $request->name);
                    }),
            ],
        ]);

        $this->modifierGroupService->updateModifierGroup($id, $data);

        return to_route('admin.modifier-groups.index');
    }

    public function delete($id)//todo
    {

    }
}
