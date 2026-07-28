<?php

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Pages\BasePage;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Livewire\Component;

beforeEach(function () {
    // Plugin::boot() runs from the panel boot path, not the service provider.
    filament()->bootCurrentPanel();
});

it('removes create & create another from the create modal', function () {
    expect(CreateAction::make('create')->canCreateAnother())->toBeFalse();
});

it('right-aligns modal footer actions', function () {
    expect(Action::make('save')->getModalFooterActionsAlignment())->toBe(Alignment::End);
});

it('right-aligns form actions', function () {
    expect(BasePage::$formActionsAlignment)->toBe(Alignment::End);
});

it('stacks table rows on narrow screens', function () {
    $livewire = new class extends Component implements HasTable
    {
        use InteractsWithTable;

        public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
        {
            return null;
        }
    };

    expect(Table::make($livewire)->isStackedOnMobile())->toBeTrue();
});

it('opens action modals as slide-overs', function () {
    expect(Action::make('edit')->isModalSlideOver())->toBeTrue();
});

it('keeps confirmation modals as centred dialogs', function () {
    // Evaluated at render time — an eager check inside configureUsing reads false,
    // because Action-level configurations run before DeleteAction::setUp().
    expect(DeleteAction::make('delete')->isModalSlideOver())->toBeFalse();
});

it('injects the mobile stylesheet into the head', function () {
    $head = (string) FilamentView::renderHook(PanelsRenderHook::HEAD_END);

    expect($head)
        ->toContain('viewport-fit=cover')
        ->toContain('fi-header-actions-ctn')
        ->toContain('@media (pointer: coarse)');
});

it('needs no script-src allowance', function () {
    // The safe-area fix is a meta tag, not a script. Keep it that way: an inline script
    // would force every consuming app to permit `script-src 'unsafe-inline'`.
    expect((string) FilamentView::renderHook(PanelsRenderHook::HEAD_END))
        ->not->toContain('<script');
});

it('leaves the sidebar toggle alone', function () {
    // Owned by mobile-bottom-nav: only it knows, at render time, whether the bar
    // actually drew and whether its More button is there to replace the hamburger.
    expect((string) FilamentView::renderHook(PanelsRenderHook::HEAD_END))
        ->not->toContain('fi-topbar-open-sidebar-btn');
});
