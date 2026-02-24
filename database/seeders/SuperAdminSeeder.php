<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {

        $roles = ['super_admin', 'admin', 'citoyen', 'autorite'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'sanctum',
            ]);
        }

        $superAdmin = Utilisateur::firstOrCreate(
            ['email_utilisateur' => 'superadmin@example.com'],
            [
                'nom_utilisateur' => 'Super',
                'prenom_utilisateur' => 'Admin',
                'telephone_utilisateur' => '0000000000',
                'mot_de_passe_utilisateur' => Hash::make('password'),
                'is_active' => true,
                'verified_at' => now(),
            ]
        );

        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        $this->command->info('✔️ Super Admin créé avec succès');
    }
}
