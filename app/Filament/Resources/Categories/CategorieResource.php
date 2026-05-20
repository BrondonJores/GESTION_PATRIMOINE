<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategorie;
use App\Filament\Resources\Categories\Pages\EditCategorie;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Schemas\CategorieForm;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Filament\Resources\Categories\Pages\ListCategoriesConsommables;
use App\Filament\Resources\Categories\Pages\ListCategoriesEquipements;
use App\Models\Categorie;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CategorieResource extends Resource
{
    protected static ?string $model = Categorie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CategorieForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
       return $table;
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('view categories') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('create categories') : false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('update categories') : false;
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('delete categories') : false;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            // Page par défaut : équipements
            'index'         => ListCategoriesEquipements::route('/'),

            // Page consommables — URL différente
            'consommables'  => ListCategoriesConsommables::route('/consommables'),

            'create'        => CreateCategorie::route('/create'),
            'edit'          => EditCategorie::route('/{record}/edit'),
        ];
    }
}
