# Filament Mobile Preset

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hammadzafar05/filament-mobile-preset.svg?style=flat-square)](https://packagist.org/packages/hammadzafar05/filament-mobile-preset)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/hammadzafar05/filament-mobile-preset/tests.yml?branch=5.x&label=tests&style=flat-square)](https://github.com/hammadzafar05/filament-mobile-preset/actions?query=workflow%3Atests+branch%3A5.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/hammadzafar05/filament-mobile-preset/fix-code-style.yml?branch=5.x&label=code%20style&style=flat-square)](https://github.com/hammadzafar05/filament-mobile-preset/actions?query=workflow%3A"Fix+code+style"+branch%3A5.x)
[![Total Downloads](https://img.shields.io/packagist/dt/hammadzafar05/filament-mobile-preset.svg?style=flat-square)](https://packagist.org/packages/hammadzafar05/filament-mobile-preset)

Mobile-first defaults for Filament panels, in one plugin. Adds a bottom navigation bar, pulls
action buttons into thumb reach, opens modals as slide-overs, removes "Create & create another",
and enlarges touch targets.

Every default is a single fluent call away from being turned off.

## Installation

```bash
composer require hammadzafar05/filament-mobile-preset
```

Register it on a panel:

```php
use Hammadzafar05\FilamentMobilePreset\FilamentMobilePresetPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(FilamentMobilePresetPlugin::make());
}
```

That's it. **No custom theme, no Tailwind `@source` line, no npm build** — the stylesheet is plain
CSS injected into the panel's `<head>`.

## What it does

| Default | Mechanism | Applies on desktop? |
|---|---|---|
| Bottom navigation bar | Registers [`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav) on the panel | No — hidden above 1024px |
| Tables stack into labelled cards | `Table::stackedOnMobile()` | No — table again above 640px |
| No "Create & create another" | `CreateAction::createAnother(false)` + `CreateRecord::disableCreateAnother()` | Yes |
| Modal footer actions right-aligned | `Action::modalFooterActionsAlignment(Alignment::End)` | Yes |
| Form actions right-aligned | `BasePage::formActionsAlignment(Alignment::End)` | Yes |
| Modals open as slide-overs | `Action::slideOver()` — confirmation dialogs stay centred | Yes |
| Page header + table actions right-aligned | CSS | No — below 1024px only |
| 44px touch targets | CSS `@media (pointer: coarse)` | Touch devices only |
| `viewport-fit=cover` for iOS safe areas | Patches the viewport meta tag | n/a |

Filament's own alignment APIs are server-side and have no knowledge of viewport width, so the
options that use them apply at every screen size. Right-aligned modal footers and form actions are
a mainstream convention, so this is a deliberate trade rather than an oversight. Everything that
would be wrong on a desktop is done in a `@media` query instead.

### The safe-area fix

Filament renders `<meta name="viewport" content="width=device-width, initial-scale=1">` with no
`viewport-fit=cover`, which makes `env(safe-area-inset-*)` resolve to `0` on iOS. This preset
emits a second viewport meta so bottom-anchored UI clears the iPhone home indicator.

### Content Security Policy

The stylesheet is injected inline, so a panel using this preset needs `style-src 'unsafe-inline'`
— the same allowance
[`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav) already requires, and
the reason neither package needs a custom theme or a build step.

It requires **no `script-src` allowance**. The safe-area fix is a meta tag rather than the
one-line script it could have been, precisely so that installing this package does not oblige
your app to permit inline scripts. A test asserts the injected head contains no `<script`.

## Configuration

```php
use Hammadzafar05\MobileBottomNav\MobileBottomNav;

FilamentMobilePresetPlugin::make()
    ->bottomNav(MobileBottomNav::make()->fromNavigation(5))  // or ->bottomNav(false)
    ->thumbAlignment(false)
    ->slideOverModals(false)
    ->createAnother()          // restores "Create & create another"
```

| Method | Default | Effect |
|---|---|---|
| `bottomNav(bool\|MobileBottomNav)` | `true` | Registers the bottom bar. Pass a configured instance to customise it, or `false` to skip. Skipped automatically if you already registered `MobileBottomNav` yourself. |
| `stackedTables(bool)` | `true` | Renders table rows as stacked, labelled cards below 640px instead of scrolling sideways. |
| `thumbAlignment(bool)` | `true` | Right-aligns page header, form and modal footer actions. |
| `slideOverModals(bool)` | `true` | Opens action modals as slide-overs. |
| `createAnother(bool)` | `false` | Restores the "Create & create another" action. |

Touch-target sizing and the safe-area fix are unconditional — an accessibility baseline and a bug
fix, not preferences.

### The topbar hamburger is not this plugin's business

The bottom bar's "More" button and the topbar hamburger both call `$store.sidebar.open()`, so
showing both is redundant. Hiding the hamburger safely means knowing whether the bottom bar
actually rendered — it bails out for guests, for tenanted panels with no tenant resolved, and for
panels whose navigation items have no icons — and whether the More button is enabled. Both facts
only exist at render time inside
[`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav), so that is where the
behaviour belongs. Hiding it from here would strand users behind a bar that never drew.

### Stacked tables use Filament's 640px breakpoint

`stackedOnMobile()` switches to cards below `sm` (640px) and back to a real table above it, so a
tablet in the 640–1023px band still gets a table while the bottom bar is visible. That is
Filament's own breakpoint, left alone deliberately. A resource can opt out per table:

```php
public static function table(Table $table): Table
{
    return $table->stackedOnMobile(false);
}
```

### Slide-overs enable sticky modal chrome

Filament derives sticky modal headers and footers from the slide-over flag, so
`slideOverModals()` turns both on. That is what you want on a phone; it is also visible on desktop.

### Re-enabling create-another on one page

`CreateRecord::disableCreateAnother()` writes to a static property shared by every create page.
To bring it back for a single page, redeclare the property on that page class:

```php
class CreatePost extends CreateRecord
{
    protected static bool $canCreateAnother = true;
}
```

> Under Laravel Octane, `formActionsAlignment` and `canCreateAnother` are process-global statics
> set from a per-panel hook, so a second panel in the same worker inherits them. Under PHP-FPM
> this cannot happen.

## Customising the stylesheet

```bash
php artisan vendor:publish --tag="filament-mobile-preset-views"
```

Then edit `resources/views/vendor/filament-mobile-preset/styles.blade.php`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Hammad Zafar](https://github.com/hammadzafar05)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
