# Implementation Plan: SPA night mode

**Branch**: `038-spa-night-mode` | **Date**: 2026-09-25 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/038-spa-night-mode/spec.md`

## Summary

Add a browser-local light/night appearance preference to the SPA shell. Configure PrimeVue Aura to use its class based dark color scheme, centralize state and storage handling in a small Pinia store, apply the root theme marker during bootstrap, and add an accessible toggle beside the existing Horizon navigation action. Extend the shared CSS variables and tests so custom shell surfaces follow the same palette.

## Technical Context

**Language/Version**: TypeScript 5.7, Vue 3.5

**Primary Dependencies**: PrimeVue 4.2 Aura theme, Pinia 2.3, Vue Test Utils, Vitest

**Storage**: Browser local storage only; no database or API changes

**Testing**: Vitest component/store tests, existing SPA smoke tests, TypeScript check, SPA build

**Target Platform**: Existing browser SPA under `/app/*`

**Project Type**: Laravel web application with Vue SPA frontend

**Performance Goals**: Apply a stored mode before SPA mount and perform a toggle without a page reload or network request

**Constraints**: Preserve authentication, Horizon access, route behavior, localization, keyboard navigation, and the existing light appearance

**Scale/Scope**: One global shell control, one preference state, shared CSS palette, and regression coverage for anonymous and authenticated navigation

## Constitution Check

- **Layering**: Pass. The feature is SPA presentation state and stays in `resources/spa`; no domain or persistence code is needed.
- **No unnecessary backend/API work**: Pass. Appearance is browser-local and does not need an endpoint.
- **Testing**: Pass when store, shell, persistence, accessibility, and route regression tests are added.
- **Modern stack**: Pass. Use the existing Vue, Pinia, PrimeVue, and TypeScript setup.
- **Localization**: Pass when new labels are added to the existing translation source and used through `t()`.

## Research Summary

See [research.md](research.md). The selected approach uses PrimeVue Aura's class based color scheme, a Pinia appearance store with defensive local storage handling, and a document-root marker applied before mount.

## Project Structure

```text
resources/spa/
├── main.ts                                  # apply stored root mode before mount
├── styles.css                               # shell palette and custom dark surfaces
├── i18n.ts                                  # existing translation lookup
├── components/
│   ├── AppLayout.vue                        # global toggle beside Horizon
│   └── AppLayout.test.ts                    # navigation and accessibility coverage
└── stores/
    ├── appearance.ts                        # reactive mode and storage behavior
    └── appearance.test.ts                   # state and persistence tests

resources/lang/
└── by.json                                  # appearance labels

specs/038-spa-night-mode/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/appearance.md
└── tasks.md
```

## Implementation Decisions

1. Use one `AppearanceMode` union (`light | night`) in the store and validate values read from storage.
2. Keep the light mode as the absence of the dark selector or an explicit light state, so existing pages retain their current appearance.
3. Configure PrimeVue's `theme.options.darkModeSelector` as `.app-night-mode` at app startup and toggle that class on `document.documentElement`.
4. Define custom variables for body, muted text, cards, tables, dropdowns, and navigation surfaces under the night selector. Avoid per-page theme conditionals.
5. Render a native button with `aria-pressed`, a localized label, and a state-specific icon/title. Place it independently of `auth.canAccessHorizon`.
6. Catch storage read/write errors and keep the in-memory state usable.

## Risks and Mitigations

| Risk | Mitigation |
|---|---|
| A custom hard-coded white surface remains unreadable | Search shared SPA styles and cover representative pages in component tests and quickstart checks. |
| Stored mode flashes light before Vue mounts | Read and apply the mode in `main.ts` before `mount()`. |
| Toggle changes shell behavior | Keep the state separate from auth and router stores; assert Horizon, logout, and navigation behavior remains unchanged. |
| Browser storage is blocked | Fall back to light mode and continue toggling in memory. |
