<?php
// app/Providers/Filament/AdminPanelProvider.php

namespace App\Providers\Filament;

use App\Filament\Pages\Apparence;
use App\Filament\Pages\Profile;
use App\Services\AppThemeService;
use Filament\Actions\Action;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // ── Sidebar : logo patrimoine existant ─────────────────

            ->brandName('Gestion du patrimoine')
            ->brandLogo(asset('images/favicon-patrimoine.svg'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/favicon-patrimoine.svg'))

            ->sidebarCollapsibleOnDesktop()
            ->colors(fn (AppThemeService $theme): array => $theme->getFilamentColors())
            ->darkMode(
                app(AppThemeService::class)->hasDarkMode(),
                app(AppThemeService::class)->hasForcedDarkMode(),
            )
            ->sidebarWidth(app(AppThemeService::class)->getSidebarWidth())
            ->collapsedSidebarWidth(app(AppThemeService::class)->getCollapsedSidebarWidth())
            ->profile(Profile::class, isSimple: false)
            ->userMenu(position: UserMenuPosition::Topbar)
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action
                    ->label('Mon profil')
                    ->icon(Heroicon::OutlinedUserCircle),
                Action::make('apparence')
                    ->label('Apparence')
                    ->icon(Heroicon::OutlinedSwatch)
                    ->url(fn (): string => Apparence::getUrl())
                    ->sort(10),
                'logout' => fn (Action $action): Action => $action
                    ->label('Se déconnecter'),
            ])

            //  Logo IFTTS sur la page login uniquement 
            ->renderHook(
                'panels::auth.login.form.before',
                fn () => Blade::render('
                    <div style="
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        margin-bottom: 24px;
                    ">
                        <img
                            src="' . asset('images/logo-iftts.png') . '"
                            alt="IFTTS Salé"
                            style="height: 110px; width: auto; object-fit: contain;"
                        />
                    </div>
                ')
            )

            // ── Hook 2 : Masquer le heading
            ->renderHook(
                'panels::head.end',
                fn () => Blade::render('
                    <style>
                        .fi-simple-layout .fi-simple-header {
                            display: none !important;
                        }
                    </style>
                ')
            )

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
