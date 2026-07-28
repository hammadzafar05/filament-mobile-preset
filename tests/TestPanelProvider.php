<?php

namespace Hammadzafar05\FilamentMobilePreset\Tests;

use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Panel;
use Filament\PanelProvider;
use Hammadzafar05\FilamentMobilePreset\FilamentMobilePresetPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test')
            ->middleware([DisableBladeIconComponents::class])
            ->plugin(FilamentMobilePresetPlugin::make());
    }
}
