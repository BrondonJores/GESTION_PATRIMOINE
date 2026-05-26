<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FamilleSeeder::class,
            CategorieSeeder::class,
            BlocSeeder::class,
            SalleSeeder::class,
            ArticleSeeder::class,
            ConsommableSeeder::class,
            AffectationSeeder::class,
        ]);
    }
}
