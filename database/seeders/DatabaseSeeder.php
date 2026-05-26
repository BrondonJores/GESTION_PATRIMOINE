<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Décommentez pour générer plusieurs utilisateurs de test.

        $this->call([
            RolePermissionSeeder::class,
        ]);

        $admin = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Administrateur', 'password' => 'password'],
        );

        $admin->assignRole('admin');
    }
}
