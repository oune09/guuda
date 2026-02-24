<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;

class UserRoleMigrationSeeder extends Seeder
{
    public function run(): void
    {
        Utilisateur::all()->each(function ($user) {
            if ($user->role_utilisateur) {
                $user->assignRole($user->role_utilisateur);
            }
        });
    }
}
