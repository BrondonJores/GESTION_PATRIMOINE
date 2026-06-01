<?php

namespace App\Filament\Resources\Familles;

use App\Filament\Resources\Familles\Pages\CreateFamille;
use App\Filament\Resources\Familles\Pages\EditFamille;
use App\Filament\Resources\Familles\Pages\ListFamilles;
use App\Filament\Resources\Familles\Schemas\FamilleForm;
use App\Filament\Resources\Familles\Tables\FamillesTable;
use App\Models\Famille;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FamilleResource extends Resource
{
    protected static ?string $model = Famille::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return FamilleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FamillesTable::configure($table);
    }


    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('view familles') : false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        return $user ? $user->can('create familles') : false;
    }

    public static function canEdit(Model $record): bool
    {/** @var User|null $user */
        
        $user = Auth::user();
        return $user ? $user->can('update familles') : false;
    }

    public static function canDelete(Model $record): bool
    {/** @var User|null $user */
        
        $user = Auth::user();
        return $user ? $user->can('delete familles') : false;
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
            'index' => ListFamilles::route('/'),
            'create' => CreateFamille::route('/create'),
            'edit' => EditFamille::route('/{record}/edit'),
        ];
    }
}
