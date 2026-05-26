<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Les CONSOMMABLES ne figurent PAS dans Canevas_INVENTAIRE.xlsx car ils sont
 * geres en stock global (quantite) et non par numero individuel.
 *
 * Ce seeder fournit un jeu initial coherent avec votre contexte.
 * Completez ou supprimez les lignes selon votre inventaire reel.
 *
 * Regle statut (appliquee aussi par ConsommableObserver) :
 *   Disponible => quantite_stock > quantite_min
 *   Sous seuil => 0 < quantite_stock <= quantite_min
 *   Epuise     => quantite_stock = 0
 */
class ConsommableSeeder extends Seeder
{
    public function run(): void
    {
        // [designation, code_categorie, quantite_stock, quantite_min, statut, observations]
        $consommables = [
            ['Papier A4 80g (rame)', 'CAT_CLASSEUR', 50, 10, 'Disponible', null],
            ['Stylos bille bleu (boite 50)', 'CAT_CLASSEUR', 20, 5, 'Disponible', null],
            ['Cartouche toner imprimante HP', 'CAT_COPIEUR_IMPRIMANTE', 6, 2, 'Disponible', null],
            ['Produit nettoyant desinfectant 5L', 'CAT_ARROSOIR', 12, 3, 'Disponible', null],
            ['Poudre extincteur ABC 6kg recharge', 'CAT_EXTINCTEUR', 8, 2, 'Disponible', 'Recharge annuelle obligatoire'],
            ['Huile moteur 20W50 bidon 5L', 'CAT_BURETTE_HUILE', 10, 2, 'Disponible', null],
            ['Gaz butane bouteille 13kg', 'CAT_CONSIGNE_BUTANE_', 4, 1, 'Disponible', null],
        ];

        $catMap = DB::table('categories')->pluck('id', 'code_categorie')->toArray();

        foreach ($consommables as [$desig, $catCode, $qty, $qtyMin, $statut, $obs]) {
            $catId = $catMap[$catCode] ?? null;
            if (! $catId) continue;

            DB::table('consommables')->updateOrInsert(
                ['designation' => $desig, 'categorie_id' => $catId],
                [
                    'designation'    => $desig,
                    'categorie_id'   => $catId,
                    'quantite_stock' => $qty,
                    'quantite_min'   => $qtyMin,
                    'statut'         => $statut,
                    'observations'   => $obs,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );
        }
    }
}
