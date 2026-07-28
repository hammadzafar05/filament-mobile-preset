<?php

namespace Hammadzafar05\FilamentMobilePreset;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentMobilePresetServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-mobile-preset';

    public static string $viewNamespace = 'filament-mobile-preset';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews(static::$viewNamespace);
    }
}
