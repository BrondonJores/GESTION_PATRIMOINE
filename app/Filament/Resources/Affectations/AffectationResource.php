<?php

namespace App\Filament\Resources\Affectations;

use App\Filament\Resources\Affectations\Pages\CreateAffectation;
use App\Filament\Resources\Affectations\Pages\EditAffectation;
use App\Filament\Resources\Affectations\Pages\ListAffectations;
use App\Filament\Resources\Affectations\Schemas\AffectationForm;
use App\Filament\Resources\Affectations\Tables\AffectationsTable;
use App\Models\Affectation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AffectationResource extends Resource
{
    protected static ?string $model = Affectation::class;
    protected static ?string $navigationLabel = 'Affectations';

    public static function form(Schema $schema): Schema
    {
        return AffectationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AffectationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAffectations::route('/'),
            'create' => CreateAffectation::route('/create'),
            'edit'   => EditAffectation::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view affectations') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create affectations') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return (Auth::user()?->can('update affectations') ?? false)
            && is_null($record->date_recuperation);
    }

    public static function canDelete(Model $record): bool
    {
        return (Auth::user()?->can('delete affectations') ?? false)
            && !is_null($record->date_recuperation);
    }
}