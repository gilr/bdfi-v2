<?php

namespace App\Providers\Filament;
use App\Http\Middleware\MaintenanceModeAdmin;

use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Filament\Enums\ThemeMode;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('filament')
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
            ])
            ->defaultThemeMode(ThemeMode::Dark)
            ->maxContentWidth('screen-2xl')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                MaintenanceModeAdmin::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                     ->label('Auteurs')
                     ->icon('heroicon-s-user-group'),
                NavigationGroup::make()
                    ->label('Publications')
                    ->icon('heroicon-s-book-open'),
                NavigationGroup::make()
                    ->label('Oeuvres')
                    ->icon('heroicon-s-document-text'),
                NavigationGroup::make()
                    ->label('Prix')
                    ->icon('heroicon-s-hand-thumb-up'),
                NavigationGroup::make()
                    ->label('Site')
                    ->icon('heroicon-s-home'),
                NavigationGroup::make()
                    ->label('Tables internes')
                    ->icon('heroicon-s-cog')
                    ->collapsed(),
            ])
            ->topNavigation() // Menu en haut et non en sidebar
//            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                NavigationItem::make('Site')
                ->url(env('APP_URL'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-home')
                ->group('Liens BDFI')
                ->sort(1),
                NavigationItem::make('Admin')
                ->url(env('APP_URL').'/admin', shouldOpenInNewTab: false)
                ->icon('heroicon-o-clipboard')
                ->group('Liens BDFI')
                ->sort(2),
            // ...
          ]);
    }
}
