<?php

namespace App\Filament\Pages;

use App\Services\AuditLogService;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class Profile extends EditProfile
{
    protected static ?string $title = 'Mon profil';

    protected Width|string|null $maxWidth = Width::SevenExtraLarge;

    public static function getLabel(): string
    {
        return 'Mon profil';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Mon profil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Photo de profil')
                    ->description('Personnalisez votre image visible dans le menu utilisateur.')
                    ->schema([
                        FileUpload::make('avatar_path')
                            ->label('Photo')
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048),
                    ]),
                Section::make('Informations personnelles')
                    ->description('Ces informations identifient votre compte dans le panel.')
                    ->schema([
                        $this->getNameFormComponent()
                            ->label('Nom complet'),
                        $this->getEmailFormComponent()
                            ->label('Adresse e-mail'),
                    ])
                    ->columns(2),
                Section::make('Sécurité')
                    ->description('Renseignez un nouveau mot de passe uniquement si vous souhaitez le modifier.')
                    ->schema([
                        $this->getPasswordFormComponent()
                            ->label('Nouveau mot de passe'),
                        $this->getPasswordConfirmationFormComponent()
                            ->label('Confirmation du mot de passe'),
                        $this->getCurrentPasswordFormComponent()
                            ->label('Mot de passe actuel'),
                    ])
                    ->columns(2),
                Section::make('Rôles et accès')
                    ->description('Lecture seule. Les droits sont gérés par un administrateur.')
                    ->schema([
                        Placeholder::make('roles')
                            ->label('Rôles')
                            ->content(fn (): string => $this->getUser()->roles->pluck('name')->sort()->implode(', ') ?: 'Aucun rôle attribué'),
                        Placeholder::make('permissions')
                            ->label('Permissions')
                            ->html()
                            ->content(fn (): HtmlString => $this->getPermissionsContent())
                            ->columnSpanFull(),
                        Placeholder::make('created_at')
                            ->label('Compte créé le')
                            ->content(fn (): string => $this->getUser()->created_at?->format('d/m/Y H:i') ?? 'Non renseigné'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        app(AuditLogService::class)->modification('Profil utilisateur', $record, request()->ip());

        return $record;
    }

    private function getPermissionsContent(): HtmlString
    {
        $permissions = $this->getUser()
            ->getAllPermissions()
            ->pluck('name')
            ->sort()
            ->values();

        if ($permissions->isEmpty()) {
            return new HtmlString('<span class="text-sm text-gray-500 dark:text-gray-400">Aucune permission attribuée</span>');
        }

        $badges = $permissions
            ->map(fn (string $permission): string => sprintf(
                '<span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">%s</span>',
                e($permission),
            ))
            ->implode('');

        return new HtmlString('<div class="flex flex-wrap gap-2">' . $badges . '</div>');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil mis à jour';
    }
}
