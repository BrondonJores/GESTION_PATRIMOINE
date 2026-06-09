<?php

namespace Tests\Feature;

use App\Models\Consommable;
use App\Models\Categorie;
use App\Models\Famille;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsommableAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerte_minimal_atteint_generer_automatiquement(): void
    {
        // 1. Mise en place du catalogue
        $famille = Famille::create(['code_famille' => 'F1', 'nom_famille' => 'Consommables IT']);
        $categorie = Categorie::create(['nom_categorie' => 'Câbles', 'famille_id' => $famille->id]);
        
        // 2. Création d'un consommable avec un stock sain
        $consommable = Consommable::create([
            'designation' => 'Câble RJ45',
            'categorie_id' => $categorie->id,
            'quantite_stock' => 50,
            'quantite_min' => 10,
        ]);

        // Aucune alerte ne doit exister à ce stade
        $this->assertDatabaseMissing('alertes', [
            'consommable_id' => $consommable->id,
        ]);

        // 3. Déclenchement de l'événement Observer via modification de stock
        // On passe de 50 à 8 (en dessous du seuil de 10)
        $consommable->update(['quantite_stock' => 8]);

        // 4. Assertion que le ConsommableObserver a bien détecté la baisse et créé l'alerte
        $this->assertDatabaseHas('alertes', [
            'consommable_id' => $consommable->id,
            'type_alerte' => 'stock_minimal_atteint',
            'statut' => 'Non_traité',
        ]);
    }

    public function test_alerte_stock_epuise_generee_quand_quantite_tombe_a_zero(): void
    {
        $famille = Famille::create(['code_famille' => 'F2', 'nom_famille' => 'Fournitures']);
        $categorie = Categorie::create(['nom_categorie' => 'Papier', 'famille_id' => $famille->id]);
        
        $consommable = Consommable::create([
            'designation' => 'Ramette A4',
            'categorie_id' => $categorie->id,
            'quantite_stock' => 20,
            'quantite_min' => 5,
        ]);

        // Le stock est épuisé
        $consommable->update(['quantite_stock' => 0]);

        // L'Observer doit générer une alerte spécifique à l'épuisement
        $this->assertDatabaseHas('alertes', [
            'consommable_id' => $consommable->id,
            'type_alerte' => 'stock_epuise',
            'statut' => 'Non_traité',
        ]);
    }
}
