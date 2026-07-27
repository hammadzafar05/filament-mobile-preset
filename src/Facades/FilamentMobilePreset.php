<?php

namespace Hammadzafar05\FilamentMobilePreset\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Hammadzafar05\FilamentMobilePreset\FilamentMobilePreset
 */
class FilamentMobilePreset extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hammadzafar05\FilamentMobilePreset\FilamentMobilePreset::class;
    }
}
