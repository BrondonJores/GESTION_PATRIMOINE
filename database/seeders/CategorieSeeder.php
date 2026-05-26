<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        // [code_categorie, nom_categorie, code_famille]
        $categories = [
            ['CAT_ARROSOIR', 'Arrosoir', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_ASPERSEUR_ESCAMOTABL', 'Asperseur escamotable', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_ATOMISEUR', 'Atomiseur', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_AUGE_DE_MA_ON', 'Auge de maçon', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_BALAI_GAZON', 'Balai à gazon', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_BALANCE', 'Balance', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_BINETTE', 'Binette', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_BROUETTE', 'Brouette', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_BURETTE_HUILE', 'Burette à huile', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_BURIN', 'Burin', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_B_CHE', 'Bêche', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CANNE_PLOMBER', 'Canne à plomber', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CHALUMEAU', 'Chalumeau', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CHA_NE_D_ARPENTEUR', 'Chaîne d’arpenteur', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CISAILLE', 'Cisaille', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CISEAUX', 'Ciseaux', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CLEF_GRIFFE', 'Clef à griffe', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CLEF_MOELETTE', 'Clef à moelette', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_COFFRE_OUTIL_M_TALLI', 'Coffre à outil métallique', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_CONSIGNE_BUTANE', 'Consigne (butane)', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_ELAGUEUR', 'Elagueur', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_FOURCHE', 'Fourche', 'FAM_MATERIEL_ET_OUTILLAG'],
            ['CAT_COPIEUR_IMPRIMANTE', 'Copieur /imprimante', 'FAM_MATERIEL_INFORMATIQU'],
            ['CAT_CHARIOT_DESSERTE_SUR', 'CHARIOT DESSERTE  SUR ROULETTE', 'FAM_MATERIEL_MOBILIER'],
            ['CAT_ARMOIRE', 'Armoire', 'FAM_MATERIEL_MOBILIER_DE'],
            ['CAT_BUREAU', 'Bureau', 'FAM_MATERIEL_MOBILIER_DE'],
            ['CAT_CHAISE', 'Chaise', 'FAM_MATERIEL_MOBILIER_DE'],
            ['CAT_CLAPET', 'Clapet', 'FAM_MATERIEL_MOBILIER_DE'],
            ['CAT_CLASSEUR', 'Classeur', 'FAM_MATERIEL_MOBILIER_DE'],
            ['CAT_FAUTEUIL_DE_BUREAU', 'Fauteuil de bureau', 'FAM_MATERIEL_MOBILIER_DE'],
            ['CAT_EQUERRE', 'Equerre', 'FAM_MATERIEL_P_DAGOGIQUE'],
            ['CAT_MATERIEL_P_DAGOGIQUE', 'Materiel pédagogique', 'FAM_MATERIEL_P_DAGOGIQUE'],
            ['CAT_CHAUFFE_EAU_AVEC_CAP', 'CHAUFFE EAU AVEC CAPTEUR SOLAIRE', 'FAM_MATERIEL_TECHNIQUE'],
            ['CAT_CAMERA_NUM_RIQUE', 'Camera numérique', 'FAM_MATERIEL_TECHNIQUE'],
            ['CAT_CLIMATISEUR', 'Climatiseur', 'FAM_MATERIEL_TECHNIQUE'],
            ['CAT_EXTINCTEUR', 'Extincteur', 'FAM_MATERIEL_TECHNIQUE'],
            ['CAT_MAT_RIEL_TECHNIQUE', 'Matériel technique', 'FAM_MATERIEL_TECHNIQUE'],
            ['CAT_PHOTOCOPIEUR', 'Photocopieur', 'FAM_MATERIEL_TECHNIQUE'],
        ];

        foreach ($categories as [$code, $nom, $famCode]) {
            $famille = DB::table('familles')->where('code_famille', $famCode)->first();
            if (! $famille) continue;

            DB::table('categories')->updateOrInsert(
                ['code_categorie' => $code],
                [
                    'code_categorie' => $code,
                    'nom_categorie'  => $nom,
                    'description'    => null,
                    'famille_id'     => $famille->id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );
        }
    }
}
