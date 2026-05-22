<?php

namespace App\Filament\Resources\Affectations\Pages;

use App\Filament\Resources\Affectations\AffectationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAffectations extends ListRecords
{
    protected static string $resource = AffectationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle Affectation'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'tous' => Tab::make('Tous')
                ->icon('heroicon-o-squares-2x2'),

            'equipements' => Tab::make('Équipements')
                ->icon('heroicon-o-computer-desktop')
                ->modifyQueryUsing(fn(Builder $query) =>
                    $query->where('type', 'article')
                ),

            'consommables' => Tab::make('Consommables')
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn(Builder $query) =>
                    $query->where('type', 'consommable')
                ),

            
        ];
    }
}