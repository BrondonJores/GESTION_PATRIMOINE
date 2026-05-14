<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\Article;
use App\Support\Alertes\StockAlertType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockAlertService
{
    public function synchroniserPourArticle(Article $article): void
    {
        $type = $this->typePourArticle($article);

        DB::transaction(function () use ($article, $type): void {
            $alertesOuvertes = Alerte::query()
                ->where('article_id', $article->id)
                ->whereIn('type_alerte', StockAlertType::values())
                ->where('statut', '!=', 'Résolu')
                ->lockForUpdate()
                ->get();

            if ($type === null) {
                $alertesOuvertes->each(fn (Alerte $alerte) => $this->resoudreAutomatiquement(
                    $alerte,
                    'Stock revenu au-dessus du seuil de surveillance.',
                ));

                return;
            }

            if ($alertesOuvertes->contains(fn (Alerte $alerte): bool => $alerte->type_alerte === $type)) {
                return;
            }

            $alertesOuvertes->each(fn (Alerte $alerte) => $this->resoudreAutomatiquement(
                $alerte,
                'Niveau remplacé par : ' . StockAlertType::label($type) . '.',
            ));

            Alerte::create([
                'article_id' => $article->id,
                'statut' => 'Non_traité',
                'canal' => 'Tous',
                'type_alerte' => $type,
                'retour' => $this->messagePourArticle($article, $type),
                'date_alerte' => Carbon::now(),
            ]);
        });
    }

    public function typePourArticle(Article $article): ?string
    {
        if ($article->quantite_min === null) {
            return null;
        }

        $quantite = (int) $article->quantite;
        $seuil = (int) $article->quantite_min;

        if ($quantite <= 0) {
            return StockAlertType::STOCK_EPUISE;
        }

        if ($quantite <= $seuil) {
            return StockAlertType::STOCK_MINIMAL;
        }

        if ($quantite <= $this->seuilProche($seuil)) {
            return StockAlertType::SEUIL_PROCHE;
        }

        return null;
    }

    private function seuilProche(int $seuil): int
    {
        return $seuil + max(1, (int) ceil($seuil * 0.2));
    }

    private function messagePourArticle(Article $article, string $type): string
    {
        return StockAlertType::label($type)
            . " pour l'article {$article->designation} : quantité {$article->quantite}, seuil minimal {$article->quantite_min}.";
    }

    private function resoudreAutomatiquement(Alerte $alerte, string $note): void
    {
        $alerte->forceFill([
            'statut' => 'Résolu',
            'note_resolution' => $note,
            'date_traitement' => Carbon::now(),
        ])->save();
    }
}
