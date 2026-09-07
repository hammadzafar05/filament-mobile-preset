# Filament Mobile Preset

<!--
    Hidden on filamentphp.com, which renders its own hero image above the docs , 
    showing it twice would be redundant. Absolute URLs so the image resolves
    wherever this file is rendered, not only on GitHub.
-->
<div class="filament-hidden">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/hammadzafar05/filament-mobile-preset/5.x/art/banner-dark.jpg">
  <img alt="Mobile Preset: every control within reach of one thumb" src="https://raw.githubusercontent.com/hammadzafar05/filament-mobile-preset/5.x/art/banner-light.jpg">
</picture>
</div>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hammadzafar05/filament-mobile-preset.svg?style=flat-square)](https://packagist.org/packages/hammadzafar05/filament-mobile-preset)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/hammadzafar05/filament-mobile-preset/tests.yml?branch=5.x&label=tests&style=flat-square)](https://github.com/hammadzafar05/filament-mobile-preset/actions?query=workflow%3Arun-tests+branch%3A5.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/hammadzafar05/filament-mobile-preset/fix-code-style.yml?branch=5.x&label=code%20style&style=flat-square)](https://github.com/hammadzafar05/filament-mobile-preset/actions?query=workflow%3A"Fix+code+style"+branch%3A5.x)
[![Total Downloads](https://img.shields.io/packagist/dt/hammadzafar05/filament-mobile-preset.svg?style=flat-square)](https://packagist.org/packages/hammadzafar05/filament-mobile-preset)

Mobile-first defaults for Filament panels, in one plugin. Adds a bottom navigation bar, pulls
action buttons into thumb reach, opens modals as slide-overs, removes "Create & create another",
and enlarges touch targets.

Every default is a single fluent call away from being turned off.

| Without the preset | With the preset |
|---|---|
| <img alt="A resource table on a phone without the preset: the table scrolls sideways, the email column is clipped mid-word, and the row actions sit off-screen" src="https://raw.githubusercontent.com/hammadzafar05/filament-mobile-preset/5.x/art/without-preset.png" width="200"> | <img alt="A resource table on a phone with the preset: the rows are stacked as cards, each value is labelled, and the row actions are at the bottom" src="https://raw.githubusercontent.com/hammadzafar05/filament-mobile-preset/5.x/art/with-preset.png" width="200"> |
| Sideways scroll, clipped values, row actions out of reach | Stacked cards, actions in reach, bottom navigation |

## Requirements

PHP 8.2+, and Filament **4.11.5+ or 5.6.5+**. ([Why those floors?](#why-the-version-floors))

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

That's it. **No custom theme, no Tailwind `@source` line, no npm build.** The stylesheet is plain
CSS injected into the panel's `<head>`.

## What it does

| Default | Turn off with | Mechanism | Applies on desktop? |
|---|---|---|---|
| Bottom navigation bar | `bottomNav(false)` | Registers [`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav) on the panel | No. hidden above 1024px |
| Tables stack into labelled cards | `stackedTables(false)` | `Table::stackedOnMobile()` | No. table again above 640px |
| No "Create & create another" | `createAnother()` | `CreateAction::createAnother(false)` + `CreateRecord::disableCreateAnother()` | Yes |
| Modal footer actions right-aligned | `thumbAlignment(false)` | `Action::modalFooterActionsAlignment(Alignment::End)` | Yes |
| Form actions right-aligned | `thumbAlignment(false)` | `BasePage::formActionsAlignment(Alignment::End)` | Yes |
| Modals open as slide-overs | `slideOverModals(false)` | `Action::slideOver()`; confirmation dialogs stay centred | Yes |
| Page header + table actions right-aligned | `thumbAlignment(false)` | CSS | No. below 1024px only |
| 44px touch targets | always on | CSS `@media (pointer: coarse)` | Touch devices only |
| `viewport-fit=cover` for iOS safe areas | always on | Emits a second viewport meta tag | n/a |

Four of these apply at every screen size, not just on mobile. See
[why some defaults apply on desktop too](#why-some-defaults-apply-on-desktop-too).

## Configuration

```php
use Hammadzafar05\MobileBottomNav\MobileBottomNav;

FilamentMobilePresetPlugin::make()
    ->bottomNav(MobileBottomNav::make()->fromNavigation(5))  // or ->bottomNav(false)
    ->thumbAlignment(false)
    ->slideOverModals(false)
    ->createAnother()          // restores "Create & create another"
    ->moreButtonLabel('Menu')  // customize the "More" button label
```

| Method | Default | Effect |
|---|---|---|
| `bottomNav(bool\|MobileBottomNav)` | `true` | Registers the bottom bar. Pass a configured instance to customise it, or `false` to skip. Skipped automatically if you already registered `mobile-bottom-nav`. |
| `stackedTables(bool)` | `true` | Renders table rows as stacked, labelled cards below 640px instead of scrolling sideways. |
| `thumbAlignment(bool)` | `true` | Right-aligns page header, form and modal footer actions. |
| `slideOverModals(bool)` | `true` | Opens action modals as slide-overs. |
| `createAnother(bool)` | `false` | Restores the "Create & create another" action. |
| `moreButtonLabel(string)` | `"More"` | Customizes the text displayed on the "More" button in the mobile bottom navigation bar. |

Touch-target sizing and the safe-area fix are unconditional: an accessibility baseline and a bug
fix, not preferences.

### Keeping the topbar hamburger

The hamburger is hidden for you, by
[`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav) rather than by this
package ([why?](#why-the-hamburger-is-not-this-plugins-job)). To keep it, tell the bottom bar:

```php
FilamentMobilePresetPlugin::make()
    ->bottomNav(MobileBottomNav::make()->hideSidebarToggle(false))
```

### Opting out of stacking, per table

```php
public static function table(Table $table): Table
{
    return $table->stackedOnMobile(false);
}
```

### Re-enabling create-another on one page

`CreateRecord::disableCreateAnother()` writes to a static property shared by every create page.
To bring it back for a single page, redeclare the property on that page class:

```php
class CreatePost extends CreateRecord
{
    protected static bool $canCreateAnother = true;
}
```

### Customizing the more button label

Pass a custom label to the `moreButtonLabel()` method:

```php
FilamentMobilePresetPlugin::make()
    ->moreButtonLabel('Navigation')  // or any other label
```

## Deployment notes

### Content Security Policy

The stylesheet is injected inline, so a panel using this preset needs `style-src 'unsafe-inline'`
,  the same allowance
[`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav) already requires, and
the reason neither package needs a custom theme or a build step.

It requires **no `script-src` allowance**. The safe-area fix is a meta tag rather than the
one-line script it could have been, precisely so that installing this package does not oblige
your app to permit inline scripts. A test asserts the injected head contains no `<script`.

### Laravel Octane

`formActionsAlignment` and `canCreateAnother` are process-global statics set from a per-panel
hook, so a second panel in the same worker inherits them. Under PHP-FPM this cannot happen.

## Customising the stylesheet

```bash
php artisan vendor:publish --tag="filament-mobile-preset-views"
```

Then edit `resources/views/vendor/filament-mobile-preset/styles.blade.php`.

## Testing

```bash
composer test
```

## Design notes

Why the plugin behaves as it does. None of this is needed to use it.

### Why the version floors

Filament 4.0.0–4.11.4 and 5.0.0–5.6.4 carry four published advisories, including an
unauthenticated temporary file upload on auth pages
([CVE-2026-48500](https://github.com/advisories)). A plain `^4.0 || ^5.0` would have advertised
support for every one of those versions, and Composer blocks them at install time anyway. The
plugin's own code works fine across both majors; the floors are about what it is reasonable to
tell someone to install.

### Why some defaults apply on desktop too

Filament's own alignment APIs are server-side and have no knowledge of viewport width, so the
options that use them apply at every screen size. Right-aligned modal footers and form actions are
a mainstream convention, so this is a deliberate trade rather than an oversight. Everything that
would be wrong on a desktop is done in a `@media` query instead.

### Why the safe-area fix is a meta tag

Filament renders `<meta name="viewport" content="width=device-width, initial-scale=1">` with no
`viewport-fit=cover`, which makes `env(safe-area-inset-*)` resolve to `0` on iOS. This preset
emits a second viewport meta so bottom-anchored UI clears the iPhone home indicator.

This does rely on browsers honouring the last viewport tag when several are present, which is
universally implemented though not formally specified. A script patching the existing tag would be
deterministic, but it would have obliged every consuming app to allow `script-src 'unsafe-inline'`
to set one attribute. If the fix ever fails, that is the reason to suspect.


### Why the hamburger is not this plugin's job

The bottom bar's "More" button and the topbar hamburger both call `$store.sidebar.open()`, so
showing both is redundant. Hiding it safely means knowing whether the bar actually rendered (it
bails out for guests, for tenanted panels with no resolved tenant, and for panels whose navigation
items have no icons) and whether the More button is enabled. Both facts only exist at render time
inside `mobile-bottom-nav`, which is why the behaviour lives there and why `^1.4` is required.
Deciding it from here would strand users behind a bar that never drew.

### Why stacked tables use the 640px breakpoint

`stackedOnMobile()` switches to cards below `sm` (640px) and back to a real table above it, so a
tablet in the 640–1023px band still gets a table while the bottom bar is visible. That is
Filament's own breakpoint, left alone deliberately.

### Why slide-overs enable sticky modal chrome

Filament derives sticky modal headers and footers from the slide-over flag, so
`slideOverModals()` turns both on. That is what you want on a phone; it is also visible on desktop.

## Changelog

Please see the
[changelog](https://github.com/hammadzafar05/filament-mobile-preset/blob/5.x/CHANGELOG.md) for more
information on what has changed recently.

## Contributing

Please see
[CONTRIBUTING](https://github.com/hammadzafar05/filament-mobile-preset/blob/5.x/.github/CONTRIBUTING.md)
for details.

## Security Vulnerabilities

Please review
[our security policy](https://github.com/hammadzafar05/filament-mobile-preset/blob/5.x/.github/SECURITY.md)
on how to report security vulnerabilities.

## Credits

- [Hammad Zafar](https://github.com/hammadzafar05)
- [All Contributors](https://github.com/hammadzafar05/filament-mobile-preset/contributors)

## License

The MIT License (MIT). Please see the
[license file](https://github.com/hammadzafar05/filament-mobile-preset/blob/5.x/LICENSE.md) for more
information.
