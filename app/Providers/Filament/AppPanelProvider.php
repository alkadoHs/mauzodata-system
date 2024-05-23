<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Models\Team;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('300px')
            ->colors([
                'primary' => [
                    50 => "#faf6f6",
                    100 => "#f6eaea",
                    200 => "#efd9d9",
                    300 => "#e2c0bf",
                    400 => "#d6a6a6",
                    500 => "#bd7776",
                    600 => "#a75d5b",
                    700 => "#8b4b4a",
                    800 => "#744140",
                    900 => "#623b3a",
                    950 => "#341c1b",
                ],
                'success' => Color::Blue,
                'badge' => Color::Sky,
            ])
            ->font('Inter Tight')
            ->tenant(Team::class)
            ->tenantRegistration(RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class)
            ->tenantMenuItems([
                'register' => MenuItem::make()
                                ->label('Register new branch')
                                ->visible(fn (): bool => auth()->user()->role === 'admin'),
                'profile' => MenuItem::make()
                                ->label('Branch profile')
                                ->icon('heroicon-o-building-storefront')
                                ->visible(fn (): bool => auth()->user()->role === 'admin'),
                                
            ])
            ->brandName('Mauzodata')
            ->brandLogo('/mauzodata.svg')
            ->brandLogoHeight('20')
            // ->registration()
            ->login(Login::class)
            ->emailVerification()
            ->passwordReset()
            ->profile()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([])
            // ->navigationItems([
            //     NavigationItem::make('Sellers Report')
            //         ->group('Reports')
            // ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->databaseNotifications()
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->plugins([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
            // ->viteTheme('resources/css/filament/app/theme.css');
    }
}
