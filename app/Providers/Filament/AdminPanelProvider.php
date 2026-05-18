<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Apparence;
use App\Filament\Pages\Profile;
use App\Filament\Widgets\ArticlesStatutChartWidget;
use App\Filament\Widgets\EvolutionAffectationsWidget;
use App\Filament\Widgets\RepartitionBlocChartWidget;
use App\Filament\Widgets\RepartitionCategorieChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Services\AppThemeService;
use Filament\Actions\Action;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Gestion du patrimoine')
            ->brandLogo(asset('images/logo-patrimoine.svg'))
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
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('filament.topbar-alertes'),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('filament.topbar-notifications'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                StatsOverviewWidget::class,
                ArticlesStatutChartWidget::class,
                RepartitionCategorieChartWidget::class,
                RepartitionBlocChartWidget::class,
                EvolutionAffectationsWidget::class,
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