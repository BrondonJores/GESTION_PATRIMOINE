<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Article;
use App\Models\Bloc;
use App\Models\Categorie;
use App\Models\Famille;
use App\Models\Salle;
use App\Models\User;
use App\Services\AffectationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MouvementLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cycle_complet_affectation_reaffectation_recuperation(): void
    {
        // 1. Setup
        $famille = Famille::create(['code_famille' => 'F1', 'nom_famille' => 'Mobilier']);
        $categorie = Categorie::create(['nom_categorie' => 'Chaises', 'famille_id' => $famille->id]);
        $article = Article::create([
            'numero_reference' => 'CH-001',
            'designation' => 'Chaise ergonomique',
            'categorie_id' => $categorie->id,
            'statut' => 'Disponible'
        ]);
        
        $bloc = Bloc::create(['code_bloc' => 'B-A', 'nom_bloc' => 'Bâtiment A', 'actif' => true]);
        $salle1 = Salle::create(['code_salle' => 'S-101', 'nom_salle' => 'Salle 101', 'bloc_id' => $bloc->id]);
        $salle2 = Salle::create(['code_salle' => 'S-102', 'nom_salle' => 'Salle 102', 'bloc_id' => $bloc->id]);

        $user = User::factory()->create();
        $this->actingAs($user);
        $service = new AffectationService();

        // 2. AFFECTATION
        $affectation1 = $service->affecterArticle([
            'article_id' => $article->id,
            'bloc_id' => $bloc->id,
            'salle_id' => $salle1->id,
        ]);
        
        $article->refresh();
        $this->assertEquals('Affecté', $article->statut);
        $this->assertEquals($salle1->id, $affectation1->salle_id);

        // 3. RÉAFFECTATION
        $affectation2 = $service->reaffecter($affectation1, [
            'bloc_id' => $bloc->id,
            'salle_id' => $salle2->id,
            'observations' => 'Déplacement suite travaux'
        ]);

        $this->assertDatabaseHas('reaffectations', [
            'affectation_id' => $affectation1->id,
            'salle_id' => $salle2->id,
        ]);
        
        // L'ancienne affectation doit être "clôturée" (date_recuperation non nulle)
        $affectation1->refresh();
        $this->assertNotNull($affectation1->date_recuperation);
        $this->assertEquals($salle2->id, $affectation2->salle_id);

        // 4. RÉCUPÉRATION
        $recuperation = $service->recuperer($affectation2, [
            'observations' => 'Retour au stock'
        ]);

        $this->assertDatabaseHas('recuperations', [
            'affectation_id' => $affectation2->id,
        ]);

        // L'article doit redevenir disponible
        $article->refresh();
        $this->assertEquals('Disponible', $article->statut);
    }
}
