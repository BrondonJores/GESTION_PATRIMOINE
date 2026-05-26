<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * La colonne "Lieu d'affectation" du fichier Excel correspond aux salles
 * au sens de la migration : chaque lieu est rattache a son bloc parent.
 *
 * Mapping Excel -> Migration :
 *   "Bloc"               -> blocs.nom_bloc
 *   "Lieu d'affectation" -> salles.nom_salle  (FK: salles.bloc_id)
 */
class SalleSeeder extends Seeder
{
    public function run(): void
    {
        // [code_salle, nom_salle (= lieu d'affectation Excel), code_bloc]
        $salles = [
            ['SALLE_BUREAU_N_10_BLOC_A', 'Bureau N 10', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_11_BLOC_A', 'Bureau N 11', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_12_BLOC_A', 'Bureau N 12', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_13_BLOC_A', 'Bureau N 13', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_14_BLOC_A', 'Bureau N 14', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_15_BLOC_A', 'Bureau N 15', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_16_BLOC_A', 'Bureau N 16', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_17_BLOC_A', 'Bureau N 17', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_18_BLOC_A', 'Bureau N 18', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_19_BLOC_A', 'Bureau N 19', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_4_BLOC_A', 'Bureau N 4', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_5_BLOC_A', 'Bureau N 5', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_6_BLOC_A', 'Bureau N 6', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_BUREAU_N_9_BLOC_A', 'Bureau N 9', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_SALLE_DE_TIRAGE_BLOC_A', 'SALLE DE TIRAGE', 'BLOC_BLOC_ADMINISTRA'],
            ['SALLE_AMPHI_BLOC_P', 'AMPHI', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_BIBLIOTH_QUE_BLOC_P', 'Bibliothéque', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_BLOC_P', 'SALLE', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_DE_COURS_N_2_BLOC_P', 'Salle de cours N  2', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_DE_S_MINAIRE_BLOC_P', 'Salle de séminaire 2', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_DE_S_MINAIRE_BLOC_P', 'Salle de séminaire 3', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_DE_S_MINAIRE_BLOC_P', 'Salle de séminaire 4', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_DE_S_MINAIRE_BLOC_P', 'Salle de séminaire 5', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_SALLE_DE_S_MINAIRE_BLOC_P', 'Salle de séminaire 6', 'BLOC_BLOC_P_DAGOGIQU'],
            ['SALLE_CASA_AIN_BORJA_EXTERN', 'CASA AIN BORJA', 'BLOC_EXTERNE'],
            ['SALLE_IMOUZZER_EXTERN', 'IMOUZZER', 'BLOC_EXTERNE'],
            ['SALLE_HANGAR_HANGAR', 'Hangar', 'BLOC_HANGAR'],
            ['SALLE_MAGASIN_HANGAR', 'Magasin', 'BLOC_HANGAR'],
            ['SALLE_PLOMBIER_HANGAR', 'PLOMBIER', 'BLOC_HANGAR'],
            ['SALLE_R_FORME_HANGAR', 'Réforme', 'BLOC_HANGAR'],
            ['SALLE_STOCK_HANGAR', 'STOCK', 'BLOC_HANGAR'],
            ['SALLE_HOTEL_HOT_L', 'HOTEL', 'BLOC_HOT_L'],
            ['SALLE_TERRASSE_HOTEL_HOT_L', 'Terrasse hotel', 'BLOC_HOT_L'],
            ['SALLE_CUISINE_R_FECT', 'CUISINE', 'BLOC_R_FECTOIRE'],
        ];

        foreach ($salles as [$code, $nom, $blocCode]) {
            $bloc = DB::table('blocs')->where('code_bloc', $blocCode)->first();
            if (! $bloc) continue;

            DB::table('salles')->updateOrInsert(
                ['code_salle' => $code],
                [
                    'code_salle' => $code,
                    'nom_salle'  => $nom,
                    'bloc_id'    => $bloc->id,
                    'actif'      => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
