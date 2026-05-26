<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Décommentez pour générer plusieurs utilisateurs de test.

        $this->call([
            RolePermissionSeeder::class,
            FamilleSeeder::class,
            CategorieSeeder::class,
            BlocSeeder::class,
            SalleSeeder::class,
            ArticleSeeder::class,
            ConsommableSeeder::class,
            AffectationSeeder::class,
        ]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Administrateur', 'password' => 'password'],
        );

        $admin->assignRole('admin');
    }
}
