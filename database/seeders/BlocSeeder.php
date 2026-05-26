<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlocSeeder extends Seeder
{
    public function run(): void
    {
        // [code_bloc, nom_bloc]
        $blocs = [
            ['BLOC_BLOC_ADMINISTRA', 'Bloc administratif'],
            ['BLOC_BLOC_P_DAGOGIQU', 'Bloc pédagogique'],
            ['BLOC_EXTERNE', 'Externe'],
            ['BLOC_HANGAR', 'Hangar'],
            ['BLOC_HOT_L', 'Hotêl'],
            ['BLOC_R_FECTOIRE', 'Réfectoire'],
        ];

        foreach ($blocs as [$code, $nom]) {
            DB::table('blocs')->updateOrInsert(
                ['code_bloc' => $code],
                [
                    'code_bloc'   => $code,
                    'nom_bloc'    => $nom,
                    'description' => null,
                    'actif'       => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}
