<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use App\Models\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('marquer_comme_lue')
                ->label('Marquer comme lue')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => ! $this->getRecord()->lu)
                ->action(fn (): bool => $this->setReadStatus(true)),
            Action::make('marquer_comme_non_lue')
                ->label('Marquer comme non lue')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('warning')
                ->visible(fn (): bool => $this->getRecord()->lu)
                ->action(fn (): bool => $this->setReadStatus(false)),
        ];
    }

    private function setReadStatus(bool $isRead): bool
    {
        /** @var Notification $notification */
        $notification = $this->getRecord();

        $saved = $notification->forceFill(['lu' => $isRead])->save();

        $this->refreshFormData(['lu']);

        return $saved;
    }
}
