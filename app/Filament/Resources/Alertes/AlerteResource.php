<?php

namespace App\Filament\Resources\Alertes;

use App\Filament\Resources\Alertes\Pages\ListAlertes;
use App\Filament\Resources\Alertes\Pages\ViewAlerte;
use App\Filament\Resources\Alertes\Schemas\AlerteForm;
use App\Filament\Resources\Alertes\Schemas\AlerteInfolist;
use App\Filament\Resources\Alertes\Tables\AlertesTable;
use App\Models\Alerte;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AlerteResource extends Resource
{
    protected static ?string $model = Alerte::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Alertes';

    protected static ?string $modelLabel = 'alerte';

    protected static ?string $pluralModelLabel = 'alertes';

    protected static string|UnitEnum|null $navigationGroup = 'Support & Admin';

    protected static ?string $recordTitleAttribute = 'statut';

    public static function form(Schema $schema): Schema
    {
        return AlerteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AlerteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlertesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlertes::route('/'),
            'view' => ViewAlerte::route('/{record}'),
        ];
    }
}
