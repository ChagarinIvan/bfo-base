# Implementation Plan: Cup Type Icons in SPA

**Branch**: `030-cup-type-icons` | **Date**: 2026-09-18 | **Spec**: [spec.md](spec.md)

## Summary

Add a typed, centralized frontend presentation mapping from every backend
`CupType` value to a bundled Font Awesome icon and Belarusian label key. Reuse a
small `CupTypeIcon` component in the cups listing and shared create/edit form,
including custom type-selector options. Unknown values use a neutral fallback;
the API and persisted cup model remain unchanged.

## Technical Context

**Language/Version**: TypeScript/Vue 3, PHP 8.5/Laravel 13

**Primary Dependencies**: PrimeVue, PrimeIcons, bundled `@fortawesome/fontawesome-free`, Vitest

**Storage**: N/A; icon metadata is frontend presentation data

**Testing**: Vitest component/model tests, existing `npm run ci`

**Target Platform**: Existing browser SPA bundle

**Project Type**: Laravel web application with Vue SPA

**Performance Goals**: No additional network requests or per-row expensive computation

**Constraints**: Belarusian-only SPA text, no API/database changes, fallback must be safe

**Scale/Scope**: 10 current cup enum values, cups listing and shared cup form

## Constitution Check

- PASS: presentation code remains in `resources/spa`; no Domain/API/storage change.
- PASS: existing PrimeVue UI and bundled icon assets are reused.
- PASS: mapping is centralized and importable by future event pages.
- PASS: all changed behavior receives Vitest coverage; full frontend CI is run at the end.
- PASS: user-facing text is added only to `resources/lang/by.json`.

## Research Summary

- The SPA currently loads PrimeIcons in `resources/spa/main.ts` and the project
  already depends on `@fortawesome/fontawesome-free` for the legacy bundle.
- PrimeIcons does not provide the needed bicycle, skiing, and running symbols;
  Font Awesome 5 free provides `fa-bicycle`, `fa-skiing`, `fa-running`,
  `fa-user-tie`, `fa-user-graduate`, `fa-child`, and `fa-bolt`. The elk-path
  cup uses a small bundled SVG moose illustration because the icon set has no
  suitable moose icon.
- PrimeVue `Select` supports `#option` and `#value` slots, so type options can
  display an icon without changing the submitted string value.

## Project Structure

```text
resources/spa/
├── main.ts                                  # load bundled Font Awesome CSS
├── components/
│   ├── CupTypeIcon.vue                       # reusable icon + accessibility
│   └── cupTypeModels.ts                      # centralized typed mapping/fallback
└── pages/cups/
    ├── CupsPage.vue                          # icon beside cup name
    ├── CupsPage.test.ts                       # listing icon coverage
    ├── CupForm.vue                            # icon type options/value
    └── CupForm.test.ts                        # mapping/options/payload coverage
```

**Structure Decision**: The mapping and icon component live under shared
`components` because future event pages must reuse them without importing from
the cups page implementation.

## Implementation Notes

- Keep canonical enum strings in the mapping: `elite`, `master`, `sprint`,
  `bike`, `juniors`, `youth`, `new_youth`, `new_master`, `ski`, `elk_path`.
- Use a typed fallback for unknown strings (`fa-question-circle` and a generic
  Belarusian label), while preserving the original type value in form payloads.
- Use `aria-label`/`title` on the icon and render a visible type label in
  selector options; icons are supplementary, not the only meaning.
- Do not add event-page integration in this feature.

## Complexity Tracking

No constitution violations or additional complexity requiring justification.
