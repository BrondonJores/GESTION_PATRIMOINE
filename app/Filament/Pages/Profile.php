<?php

namespace App\Filament\Pages;

use App\Services\AuditLogService;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

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
                        Placeholder::make('created_at')
                            ->label('Compte créé le')
                            ->content(fn (): string => $this->getUser()->created_at?->format('d/m/Y H:i') ?? 'Non renseigné'),
                        Fieldset::make('Permissions')
                            ->schema($this->getPermissionCheckboxLists())
                            ->columns([
                                'default' => 1,
                                'xl' => 2,
                            ])
                            ->columnSpanFull(),
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

    private function getPermissionCheckboxLists(): array
    {
        $permissions = $this->getUser()
            ->getAllPermissions()
            ->pluck('name')
            ->sort()
            ->values();

        if ($permissions->isEmpty()) {
            return [
                Placeholder::make('permissions_vides')
                    ->label('Permissions')
                    ->content('Aucune permission attribuée')
                    ->columnSpanFull(),
            ];
        }

        return $permissions
            ->groupBy(fn (string $permission): string => $this->getPermissionModule($permission))
            ->sortKeys()
            ->map(function ($permissions, string $module): CheckboxList {
                $options = $permissions
                    ->mapWithKeys(fn (string $permission): array => [
                        $permission => $this->getPermissionActionLabel($permission),
                    ])
                    ->all();

                return CheckboxList::make('permissions_' . str($module)->slug('_'))
                    ->label($this->getPermissionModuleLabel($module) . ' (' . $permissions->count() . ')')
                    ->options($options)
                    ->default($permissions->all())
                    ->disabled()
                    ->dehydrated(false)
                    ->bulkToggleable(false)
                    ->columns(1);
            })
            ->values()
            ->all();
    }

    private function getPermissionModule(string $permission): string
    {
        $mots = explode(' ', $permission);

        return match ($permission) {
            'assign roles' => 'users',
            'reset password users' => 'users',
            'reaffecter articles', 'recuperer articles' => 'affectations',
            default => end($mots) ?: $permission,
        };
    }

    private function getPermissionModuleLabel(string $module): string
    {
        return [
            'affectations' => 'Affectations',
            'alertes' => 'Alertes',
            'articles' => 'Articles',
            'blocs' => 'Blocs',
            'logs' => 'Journaux',
            'notifications' => 'Notifications',
            'rapports' => 'Rapports',
            'salles' => 'Salles',
            'users' => 'Utilisateurs',
        ][$module] ?? ucfirst($module);
    }

    private function getPermissionActionLabel(string $permission): string
    {
        return [
            'view' => 'Consulter',
            'view_any' => 'Consulter la liste',
            'create' => 'Créer',
            'update' => 'Modifier',
            'delete' => 'Supprimer',
            'export' => 'Exporter',
            'traiter' => 'Traiter',
            'assign' => 'Attribuer les rôles',
            'reset' => 'Réinitialiser le mot de passe',
            'activate' => 'Activer',
            'deactivate' => 'Désactiver',
            'reaffecter' => 'Réaffecter un article',
            'recuperer' => 'Récupérer un article',
        ][str($permission)->before(' ')->toString()] ?? ucfirst($permission);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Profil mis à jour';
    }
}
