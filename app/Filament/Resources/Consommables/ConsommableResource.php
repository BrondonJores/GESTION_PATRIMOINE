<?php
// app/Filament/Resources/Consommables/ConsommableResource.php

namespace App\Filament\Resources\Consommables;

use App\Models\Bloc;
use App\Models\Consommable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;

use Filament\Tables\Filters\SelectFilter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ConsommableResource extends Resource
{
    protected static ?string $model = Consommable::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';
  protected static ?string $navigationLabel = 'Consommables';
    protected static ?int $navigationSort = 2;





    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Consommables\Schemas\ConsommableForm::configure($schema);
    }

     public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Consommables\Tables\ConsommablesTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view articles') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create articles') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()?->can('update articles') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()?->can('delete articles') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\Consommables\Pages\ListConsommables::route('/'),
            'create' => \App\Filament\Resources\Consommables\Pages\CreateConsommable::route('/create'),
            'edit'   => \App\Filament\Resources\Consommables\Pages\EditConsommable::route('/{record}/edit'),
        ];
    }
}
