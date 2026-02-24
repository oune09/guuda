<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        
        $permissions = [
            
            'user.view.profile', 'user.update.profile', 'user.view.all', 
            'user.create.admin', 'user.create.autorite',

            
            'incident.create', 'incident.view.own', 'incident.view.detail',
            'incident.update.status', 'incident.delete', 'incident.view.unite', 
            'incident.view.all',

            
            'alerte.create', 'alerte.view.all', 'alerte.view.own', 
            'alerte.view.detail', 'alerte.update', 'alerte.delete',

            
            'organisation.create', 'organisation.view', 'organisation.update', 'organisation.delete',

            
            'unite.create', 'unite.view', 'unite.view.detail', 'unite.update', 'unite.delete', 'unite.update.location',

           
            'role.view', 'role.create', 'role.update', 'role.delete', 'role.assign.permission',
            'permission.view', 'permission.create', 'permission.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

       
        
        
        $citoyenPermissions = [
            'user.view.profile', 'user.update.profile',
            'incident.create', 'incident.view.own', 'incident.view.detail',
            'alerte.view.own', 'alerte.view.detail','organisation.view',
        ];

        
        $autoritePermissions = [
            'user.view.profile', 'user.update.profile',
            'incident.view.unite', 'incident.view.detail', 'incident.update.status',
            'alerte.view.all', 'alerte.view.detail'
        ];

        
        $adminPermissions = array_merge($autoritePermissions, [
            'user.create.admin', 'user.create.autorite','user.update.profile',
            'unite.view', 'unite.view.detail', 'unite.create', 'unite.update','unite.update.location', 
            'incident.view.unite', 'incident.view.detail', 'incident.update.status',
            'alerte.view.all', 'alerte.view.detail'
        ]);

       
        $roles = [
            'citoyen'     => $citoyenPermissions,
            'autorite'    => $autoritePermissions,
            'admin'       => $adminPermissions,
            'super_admin' => $permissions, 
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
