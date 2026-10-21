<?php

namespace App\Http\Controllers\Admin;

use App\Models\Modifier\ModifierService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ModifierController
{
    protected ModifierService $modifierService;

    public function __construct(ModifierService $modifierService)
    {
        $this->modifierService = $modifierService;
    }

    public function index()
    {
        return Inertia::render('Admin/Modifiers/Modifiers');
    }

    public function all()
    {
        $modifiers = $this->modifierService->getModifiersForAdmin();
        return response()->json(['json' => $modifiers]);
    }

    public function newModifier()
    {
        return Inertia::render('Admin/Modifiers/ModifierCreate');
    }

    public function show($id)
    {
        $modifier = $this->modifierService->getModifierForAdminById($id);

        return Inertia::render('Admin/Modifiers/ModifierUpdate', ['modifier' => $modifier]);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'value' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u'],
            'description' => ['required', 'string'],
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'price' => ['nullable', 'numeric', 'regex:/^\d{1,6}(\.\d{1,2})?$/'],
        ]);

        $modifier = $this->modifierService->createModifier($data);

        session()->flash('message', 'Modifier ' . Str::upper($modifier->name) . ' created!');

        return to_route('admin.modifiers.index');
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'value' => ['required', 'regex:/^[A-Za-z0-9\sčćžšđČĆŽŠĐ]+$/u'],
            'description' => ['required', 'string'],
            'en_description' => ['required', 'string'],
            'modifier_group_id' => 'required|exists:modifier_groups,id',
            'price' => ['required', 'numeric', 'regex:/^\d{1,6}(\.\d{1,2})?$/'],
            'en_value' => [
                'required',
                'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('modifier_translations', 'name')
                    ->where('modifier_id', $id)
                    ->where('locale', 'en')
                    ->ignore($request->en_value, 'name') // Ignore the current value of en_value
            ],
        ]);

        $modifier = $this->modifierService->updateModifier($id, $data);

        session()->flash('message', 'Modifier ' . Str::upper($modifier->name) . ' updated!');

        return to_route('admin.modifiers.index');
    }

    public function delete($id)//todo
    {

    }
}
