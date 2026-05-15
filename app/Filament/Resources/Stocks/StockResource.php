<?php

namespace App\Filament\Resources\Stocks;

use App\Filament\Resources\Stocks\Pages\ListStocks;
use App\Filament\Resources\Stocks\Tables\StocksTable;
use App\Models\Article;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StockResource extends Resource
{
    // ✅ Modèle = Article, pas Stock
    // Une ligne dans l'interface = un article
    // Les colonnes Disponible/Affecté/Maintenance/Réformé sont calculées dynamiquement
    protected static ?string $model = Article::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCubeTransparent;
    protected static ?string $navigationLabel = 'Stocks';

    // ✅ Pas de navigationGroup → apparaît directement dans le sidebar
    protected static string|UnitEnum|null $navigationGroup = null;
    protected static ?int $navigationSort = 2;
    // Lecture seule — pas de formulaire de création ici
    // La création d'article se fait depuis ArticleResource
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return StocksTable::configure($table);
    }

    // Pas de création depuis ce module
    public static function canCreate(): bool
    {
        return false;
    }

    // Pas de modification de la fiche article depuis ce module
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    // Pas de suppression
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view articles') ?? false;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
        ];
    }
}