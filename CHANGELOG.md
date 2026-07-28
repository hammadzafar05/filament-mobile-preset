# Changelog

All notable changes to `filament-mobile-preset` will be documented in this file.

## 1.0.0 - 2026-07-28

Initial release. Mobile-first defaults for Filament v5 panels, registered as a panel plugin.

### Added

- Bottom navigation bar, by registering [`mobile-bottom-nav`](https://github.com/hammadzafar05/mobile-bottom-nav) on the panel. Skipped automatically if you registered it yourself.
- Table rows stack into labelled cards below 640px instead of scrolling sideways, via `Table::stackedOnMobile()`.
- Action modals open as slide-overs. Actions requiring confirmation keep their centred dialog.
- Page header, form and modal footer actions pulled to the right edge, within thumb reach.
- "Create & create another" removed from both the create modal and the full-page create form.
- 44px minimum touch targets on coarse pointers, covering link-style table record actions as well as icon buttons (WCAG 2.5.8).
- `viewport-fit=cover`, so `env(safe-area-inset-*)` stops resolving to `0` on iOS and bottom-anchored UI clears the home indicator.
- Fluent opt-outs for every default: `bottomNav()`, `stackedTables()`, `thumbAlignment()`, `slideOverModals()`, `createAnother()`.
- Requires `mobile-bottom-nav` `^1.4`, which hides the now-redundant topbar hamburger itself. That decision depends on whether the bar actually rendered and whether its More button is enabled, both of which are only knowable at render time inside that package.

### Notes

- Requires no `script-src` CSP allowance. The inline stylesheet needs `style-src 'unsafe-inline'`.
- No custom theme, Tailwind `@source` entry, or npm build step.
- Under Laravel Octane, `BasePage::$formActionsAlignment` and `CreateRecord::$canCreateAnother` are process-global statics set from a per-panel hook, so a second panel in the same worker inherits them. See the README.
