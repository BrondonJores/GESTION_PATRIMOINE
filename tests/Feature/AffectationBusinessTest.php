<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Bloc;
use App\Models\Categorie;
use App\Models\Famille;
use App\Models\User;
use App\Services\AffectationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffectationBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_affecter_article_modifie_le_statut_et_cree_historique(): void
    {
        // 1. Préparation de l'état initial
        $famille = Famille::create(['code_famille' => 'F1', 'nom_famille' => 'Mobilier']);
        $categorie = Categorie::create(['nom_categorie' => 'Bureaux', 'famille_id' => $famille->id]);
        
        $article = Article::create([
            'numero_reference' => 'BUR-2026-001',
            'designation' => 'Bureau de direction',
            'categorie_id' => $categorie->id,
            'statut' => 'Disponible'
        ]);
        
        $bloc = Bloc::create(['code_bloc' => 'B-A', 'nom_bloc' => 'Bâtiment A', 'actif' => true]);
        
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. Exécution du service métier (dans sa transaction ACID)
        $service = new AffectationService();
        $affectation = $service->affecterArticle([
            'article_id' => $article->id,
            'bloc_id' => $bloc->id,
            'date_affectation' => now()->toDateString(),
        ]);

        // 3. Assertions sur la persistance des données
        $this->assertDatabaseHas('affectations', [
            'id' => $affectation->id,
            'article_id' => $article->id,
            'type' => 'article'
        ]);

        // Le statut de l'article doit avoir changé
        $article->refresh();
        $this->assertEquals('Affecté', $article->statut);
    }

    public function test_impossible_affecter_article_deja_affecte_rollback_assure(): void
    {
        $famille = Famille::create(['code_famille' => 'F2', 'nom_famille' => 'Informatique']);
        $categorie = Categorie::create(['nom_categorie' => 'PC', 'famille_id' => $famille->id]);
        
        $article = Article::create([
            'numero_reference' => 'PC-2026-002',
            'designation' => 'PC Portable',
            'categorie_id' => $categorie->id,
            'statut' => 'Affecté' // L'article n'est PAS disponible
        ]);
        
        $bloc = Bloc::create(['code_bloc' => 'B-B', 'nom_bloc' => 'Bâtiment B', 'actif' => true]);

        $service = new AffectationService();
        
        // On s'attend à ce que le Service lève une Exception et fasse un Rollback
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cet article ne peut pas être affecté.");

        $service->affecterArticle([
            'article_id' => $article->id,
            'bloc_id' => $bloc->id,
        ]);

        // Vérification de la non-création de donnée orpheline
        $this->assertDatabaseMissing('affectations', [
            'article_id' => $article->id,
        ]);
    }
}
