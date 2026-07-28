{{--
    Filament emits `<meta name="viewport" content="width=device-width, initial-scale=1">`
    with no `viewport-fit=cover`, so `env(safe-area-inset-*)` resolves to 0 on iOS and any
    safe-area padding (including mobile-bottom-nav's) silently does nothing. Patching the
    attribute is deterministic; a second viewport meta relies on undefined last-wins behaviour.
--}}
<script>document.querySelector('meta[name=viewport]')?.setAttribute('content', 'width=device-width, initial-scale=1, viewport-fit=cover')</script>

<style data-navigate-track>
    @media (pointer: coarse) {
        /*
            WCAG 2.5.8 — Filament does not size up actions on touch devices.
            Table record actions default to LINK_VIEW, not icon buttons, so `.fi-link`
            has to be here too: it is the most-tapped control in a resource table.
            `.fi-link` is already inline-flex, so a min block size applies cleanly.
            Unconditional: an accessibility baseline, not a preference.
        */
        .fi-ta-actions .fi-icon-btn,
        .fi-ta-actions .fi-link,
        .fi-ac .fi-icon-btn,
        .fi-ac .fi-link {
            min-block-size: 2.75rem;
            min-inline-size: 2.75rem;
        }

        /*
            Filament pins `.fi-link-label` to `align-self: baseline` so links sit on the
            baseline of surrounding prose. Growing the box to 2.75rem pulls that baseline
            away from the icon's centre, leaving the label stranded above it. Inside an
            action row there is no prose to align to, so centre it.
        */
        .fi-ta-actions .fi-link > .fi-link-label,
        .fi-ac .fi-link > .fi-link-label {
            align-self: center;
        }
    }

    {{-- 1023px, not `md`: mobile-bottom-nav hides at min-width 1024px. --}}
    @media (max-width: 1023px) {
        /*
            Filament lifts the stacked selection checkbox out of flow and pins it to the
            card's top-right (`absolute end-5 top-0`), so a long value in the first column
            wraps underneath it. Reserve the gutter. Inert when selection is disabled,
            since the adjacent sibling selector needs a selection cell to match.
        */
        .fi-ta-table-stacked-on-mobile .fi-ta-selection-cell + .fi-ta-cell {
            padding-inline-end: 3rem;
        }

        @if ($hasThumbAlignment)
            /*
                Page header actions. No native API reaches these: the header component
                accepts an `actionsAlignment` prop, but nothing in Filament ever passes it.
                `row-reverse` mirrors what `.fi-align-end` already means for `.fi-ac`, and
                puts the primary action rightmost, nearest the thumb.
            */
            .fi-header-actions-ctn > .fi-ac:not(.fi-width-full) {
                flex-direction: row-reverse;
            }

            /*
                Table header actions and stacked-mode row actions. Normal <td> tables
                already default to `justify-end`; empty states are `.fi-align-center`
                and are correctly excluded by this selector.
            */
            .fi-ta-actions.fi-align-start {
                justify-content: flex-end;
            }

            /*
                Stacked cards. Filament pins record actions to `justify-start` below 640px
                and `sm:justify-end` above it; this just extends the right-aligned form down
                to the smallest screens, where thumb reach matters most. `!important` beats
                the deeply nested vendor rule without depending on source order.
            */
            .fi-ta-table-stacked-on-mobile .fi-ta-actions {
                justify-content: flex-end !important;
            }
        @endif

        @if ($hidesSidebarToggle)
            /*
                Both sidebar toggles call $store.sidebar.open(), which is exactly what the
                bottom bar's "More" button does. `!important` because Alpine's x-show writes
                an inline display style on these.
            */
            .fi-topbar-open-sidebar-btn,
            .fi-layout-sidebar-toggle-btn-ctn {
                display: none !important;
            }
        @endif
    }
</style>
