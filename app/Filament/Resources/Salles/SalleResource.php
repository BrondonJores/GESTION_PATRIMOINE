<?php

namespace App\Filament\Resources\Salles;

use App\Filament\Resources\Salles\Pages\CreateSalle;
use App\Filament\Resources\Salles\Pages\EditSalle;
use App\Filament\Resources\Salles\Pages\ListSalles;
use App\Filament\Resources\Salles\Schemas\SalleForm;
use App\Filament\Resources\Salles\Tables\SallesTable;
use App\Models\Salle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SalleResource extends Resource
{
    protected static ?string $model = Salle::class;
    protected static ?string $navigationLabel = 'Salles';

    public static function form(Schema $schema): Schema
    {
        return SalleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SallesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSalles::route('/'),
            'create' => CreateSalle::route('/create'),
            'edit'   => EditSalle::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view salles') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create salles') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('update salles') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('delete salles') ?? false;
    }
}