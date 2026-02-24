<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Lister tous les rôles
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles, 200);
    }

    // Créer un rôle
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'sometimes|string'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'sanctum'
        ]);

        return response()->json(['message' => 'Rôle créé', 'role' => $role], 201);
    }

    // Mettre à jour un rôle
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'guard_name' => 'sometimes|string'
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? $role->guard_name
        ]);

        return response()->json(['message' => 'Rôle mis à jour', 'role' => $role], 200);
    }

    // Supprimer un rôle
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'Rôle supprimé'], 200);
    }

    // Assigner des permissions à un rôle (remplace les existantes)
    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            'message' => 'Permissions assignées avec succès',
            'role' => $role->load('permissions')
        ], 200);
    }
}
