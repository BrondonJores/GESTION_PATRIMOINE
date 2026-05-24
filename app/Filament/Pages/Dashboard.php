<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as FilamentDashboard;

class Dashboard extends FilamentDashboard
{
    // Icône dans le menu sidebar
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Tableau de bord';


    protected static ?string $title = 'Tableau de bord';

    
    protected static ?int $navigationSort = -1;
}