<?php

namespace Hammadzafar05\FilamentMobilePreset\Commands;

use Illuminate\Console\Command;

class FilamentMobilePresetCommand extends Command
{
    public $signature = 'filament-mobile-preset';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
