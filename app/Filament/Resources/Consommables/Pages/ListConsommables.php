<?php

namespace App\Filament\Resources\Consommables\Pages;

use App\Filament\Resources\Consommables\ConsommableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListConsommables extends ListRecords
{
    protected static string $resource = ConsommableResource::class;

       protected function getHeaderActions(): array
{
    return [
        CreateAction::make()
            ->visible(fn () => Auth::user()?->can('create articles') ?? false),
    ];
}
}
