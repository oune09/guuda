<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // Lister toutes les permissions
    public function index()
    {
        $permissions = Permission::all();
        return response()->json($permissions, 200);
    }

    // Créer une permission
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'sometimes|string'
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web'
        ]);

        return response()->json(['message' => 'Permission créée', 'permission' => $permission], 201);
    }

    // Mettre à jour une permission
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'guard_name' => 'sometimes|string'
        ]);

        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? $permission->guard_name
        ]);

        return response()->json(['message' => 'Permission mise à jour', 'permission' => $permission], 200);
    }

    // Supprimer une permission
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return response()->json(['message' => 'Permission supprimée'], 200);
    }
}
