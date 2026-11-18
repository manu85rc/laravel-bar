<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private const COLORS = [
        '#2d8f5e',
        '#3a7bbd',
        '#c8922a',
        '#c0392b',
        '#7c5cc8',
        '#0891b2',
        '#c2410c',
        '#9333ea',
        '#0d9488',
        '#be185d',
    ];

    public function index()
    {
        $roles = Role::withCount('permissions')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        $usedColors = Role::pluck('color')->filter()->toArray();
        $defaultColor = collect(self::COLORS)->first(fn ($c) => ! in_array($c, $usedColors)) ?? self::COLORS[0];

        return view('admin.roles.create', compact('permissions', 'defaultColor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:roles,slug',
            'color' => 'required|string|max:7',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'color' => $request->color,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $role->load('permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:roles,slug,'.$role->id,
            'color' => 'required|string|max:7',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'color' => $request->color,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'super_admin') {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No puedes eliminar el rol de Super Admin.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}
