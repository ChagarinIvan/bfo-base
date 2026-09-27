# Tasks: SPA night mode

**Input**: Design documents from `/specs/038-spa-night-mode/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/appearance.md

**Tests**: Included because the specification requires regression coverage for state, persistence, accessibility, and navigation behavior.

## Dependency order

```text
T001 ─┬→ T003 → T004 ─┬→ T005
      └→ T002          ├→ T006
                       └→ T007
T005, T006, T007 → T008 → T009
T008 → T010 → T011
T009, T011 → T012, T013 → T014
```

## Phase 1: Setup

**Purpose**: Establish the appearance state contract and test locations.

- [x] T001 Add the `AppearanceMode` type, validated storage key, and public store API in `resources/spa/stores/appearance.ts`.
- [x] T002 [P] Add Belarusian labels for light mode, night mode, and the appearance toggle in `resources/lang/by.json`.

## Phase 2: Foundational

**Purpose**: Connect the preference to the document root and PrimeVue theme setup before adding the control.

- [x] T003 Apply the stored `.app-night-mode` marker before Vue mount and configure PrimeVue Aura `theme.options.darkModeSelector` to the same selector in `resources/spa/main.ts`.
- [x] T004 Implement safe storage read/write, default light mode, reactive toggling, and root marker synchronization in `resources/spa/stores/appearance.ts`.
- [x] T005 [P] Define light/night custom shell variables and dark selectors for body, cards, tables, forms, dropdowns, messages, focus states, and overlays in `resources/spa/styles.css`.

## Phase 3: User Story 1 - Switch the SPA appearance (Priority: P1) 🎯 MVP

**Goal**: Every visitor can switch the complete SPA shell between light and night mode from the global navigation.

**Independent Test**: Mount the shell, activate the control, and assert that mode state and the document marker change without router navigation or API calls.

### Tests for User Story 1

- [x] T006 [P] [US1] Add store tests for light-to-night and night-to-light transitions, root marker updates, and no navigation/network side effects in `resources/spa/stores/appearance.test.ts`.
- [x] T007 [P] [US1] Add AppLayout tests proving the control renders for anonymous users, sits in the auth navigation near the Horizon action when available, exposes `aria-pressed`, and toggles by keyboard in `resources/spa/components/AppLayout.test.ts`.

### Implementation for User Story 1

- [x] T008 [US1] Add the localized native appearance toggle to `resources/spa/components/AppLayout.vue`, independent of `auth.canAccessHorizon`, with state-specific icon and accessible label.

**Checkpoint**: The shell switches appearance for anonymous and authenticated navigation without changing routes or auth behavior.

## Phase 4: User Story 2 - Keep the preference across visits (Priority: P2)

**Goal**: A valid browser-local preference survives reloads and invalid storage does not break the SPA.

**Independent Test**: Set night mode, recreate the store/bootstrap state, and assert that night mode is restored; use invalid and throwing storage stubs to assert light fallback and continued toggling.

- [x] T009 [US2] Extend `resources/spa/stores/appearance.test.ts` with persistence, invalid-value fallback, storage-error handling, and a bootstrap test that verifies `.app-night-mode` is applied before mount.

**Checkpoint**: A returning visitor sees the selected mode before interacting with the shell.

## Phase 5: User Story 3 - Understand and operate the control (Priority: P2)

**Goal**: The control communicates its state and remains operable with keyboard and assistive technology at narrow and wide navigation layouts.

**Independent Test**: Inspect both toggle states in AppLayout tests and verify accessible name, pressed state, focusability, and unchanged Horizon/logout actions.

- [x] T010 [US3] Add narrow-layout, focus, accessible-name, and authenticated Horizon/logout regression assertions in `resources/spa/components/AppLayout.test.ts` and related shell test helpers.
- [x] T011 [US3] Add responsive and focus-visible styles for the appearance control in `resources/spa/styles.css`.

**Checkpoint**: The appearance control is understandable and usable without changing existing global navigation behavior.

## Phase 6: Polish and cross-cutting validation

**Purpose**: Validate the feature against its contract and existing SPA behavior.

- [x] T012 [P] Update SPA smoke or shell regression coverage for route persistence across `/app/competitions`, `/app/cups`, and `/app/persons` in `resources/spa/smoke.test.ts` or the relevant route tests.
- [x] T013 [P] Review all shared SPA custom surfaces for hard-coded light colors, complete night selectors, and WCAG AA contrast in `resources/spa/styles.css`.
- [x] T014 Run the feature quickstart and focused Vitest, typecheck, and SPA build commands documented in `specs/038-spa-night-mode/quickstart.md`.

## Parallel execution

- T002 can run in parallel with the store implementation after the public labels are agreed.
- T005 can run in parallel with T003 and T004 after the contract is agreed.
- T006 and T007 can run in parallel after T004's public store API is defined.
- T012 and T013 can run in parallel after T008 and T011.

## Implementation strategy

Deliver the MVP through T001–T008. Complete persistence and accessibility hardening through T009–T011, then run the cross-cutting validation phase. No backend, database, API, or Horizon changes are required.
