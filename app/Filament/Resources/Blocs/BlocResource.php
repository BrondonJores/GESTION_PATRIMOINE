<?php

namespace App\Filament\Resources\Blocs;

use App\Filament\Resources\Blocs\Pages\CreateBloc;
use App\Filament\Resources\Blocs\Pages\EditBloc;
use App\Filament\Resources\Blocs\Pages\ListBlocs;
use App\Filament\Resources\Blocs\Schemas\BlocForm;
use App\Filament\Resources\Blocs\Tables\BlocsTable;
use App\Models\Bloc;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use BackedEnum;

class BlocResource extends Resource
{
    protected static ?string $model = Bloc::class;
        protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Blocs';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return BlocForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlocsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListBlocs::route('/'),
            'create' => CreateBloc::route('/create'),
            'edit'   => EditBloc::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view blocs') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create blocs') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('update blocs') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('delete blocs') ?? false;
    }
}