<?php

namespace Hammadzafar05\FilamentMobilePreset;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Contracts\Plugin;
use Filament\Pages\BasePage;
use Filament\Panel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Hammadzafar05\MobileBottomNav\MobileBottomNav;

class FilamentMobilePresetPlugin implements Plugin
{
    protected bool | MobileBottomNav $bottomNav = true;

    protected bool $hasThumbAlignment = true;

    protected bool $hasSlideOverModals = true;

    protected bool $hasStackedTables = true;

    protected bool $hasCreateAnother = false;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-mobile-preset';
    }

    /**
     * Register the bottom navigation bar. Pass a configured `MobileBottomNav`
     * instance to customise it, or `false` to bring your own navigation.
     */
    public function bottomNav(bool | MobileBottomNav $nav = true): static
    {
        $this->bottomNav = $nav;

        return $this;
    }

    /**
     * Pull page header, form and modal footer actions towards the right edge,
     * within thumb reach.
     */
    public function thumbAlignment(bool $condition = true): static
    {
        $this->hasThumbAlignment = $condition;

        return $this;
    }

    /**
     * Open action modals as slide-overs. Actions requiring confirmation keep
     * their centred dialog.
     */
    public function slideOverModals(bool $condition = true): static
    {
        $this->hasSlideOverModals = $condition;

        return $this;
    }

    /**
     * Render table rows as stacked, labelled cards on narrow screens instead
     * of a horizontally scrolling table.
     */
    public function stackedTables(bool $condition = true): static
    {
        $this->hasStackedTables = $condition;

        return $this;
    }

    /**
     * Restore the "Create & create another" action, which the preset removes
     * by default.
     */
    public function createAnother(bool $condition = true): static
    {
        $this->hasCreateAnother = $condition;

        return $this;
    }

    public function register(Panel $panel): void
    {
        if ($this->bottomNav !== false && ! $panel->hasPlugin('mobile-bottom-nav')) {
            $panel->plugin(
                $this->bottomNav instanceof MobileBottomNav
                    ? $this->bottomNav
                    : MobileBottomNav::make(),
            );
        }

        $panel->renderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('filament-mobile-preset::styles', [
                'hasThumbAlignment' => $this->hasThumbAlignment,
            ])->render(),
        );
    }

    public function boot(Panel $panel): void
    {
        if (! $this->hasCreateAnother) {
            CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));

            // ponytail: `$canCreateAnother` is a process-global static and this switch is one-way.
            //           Under Octane it leaks into panels that never registered this plugin.
            //           Per-page escape hatch: redeclare `protected static bool $canCreateAnother = true;`.
            //           Upgrade path: a Livewire/page-level hook, if anyone actually hits this.
            CreateRecord::disableCreateAnother();
        }

        if ($this->hasThumbAlignment) {
            Action::configureUsing(fn (Action $action) => $action->modalFooterActionsAlignment(Alignment::End));

            // ponytail: process-global static, same Octane ceiling as above.
            BasePage::formActionsAlignment(Alignment::End);
        }

        if ($this->hasStackedTables) {
            // Survives the resource's own table() call: unlike recordActions(), which resets
            // its array, nothing in a resource touches this property. A resource can still
            // opt out with ->stackedOnMobile(false), which runs later and wins.
            Table::configureUsing(fn (Table $table) => $table->stackedOnMobile());
        }

        if ($this->hasSlideOverModals) {
            // Evaluated at render time, not here: `Action`-level configurations run before
            // `DeleteAction::setUp()` calls `requiresConfirmation()`, so an eager check reads false.
            Action::configureUsing(fn (Action $action) => $action->slideOver(
                fn (Action $action): bool => ! $action->isConfirmationRequired(),
            ));
        }
    }
}
