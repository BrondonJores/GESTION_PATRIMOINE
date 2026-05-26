<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamilleSeeder extends Seeder
{
    public function run(): void
    {
        // [code_famille, nom_famille]
        $familles = [
            ['FAM_MATERIEL_ET_OUTILLAG', 'Materiel et outillage'],
            ['FAM_MATERIEL_INFORMATIQU', 'Materiel informatique'],
            ['FAM_MATERIEL_MOBILIER', 'Materiel mobilier'],
            ['FAM_MATERIEL_MOBILIER_DE', 'Materiel mobilier de bureau'],
            ['FAM_MATERIEL_P_DAGOGIQUE', 'Materiel pédagogique'],
            ['FAM_MATERIEL_TECHNIQUE', 'Materiel technique'],
        ];

        foreach ($familles as [$code, $nom]) {
            DB::table('familles')->updateOrInsert(
                ['code_famille' => $code],
                [
                    'code_famille' => $code,
                    'nom_famille'  => $nom,
                    'description'  => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }
    }
}
