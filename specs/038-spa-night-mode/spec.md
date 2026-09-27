# Feature Specification: SPA night mode

**Feature Branch**: `038-spa-night-mode`

**Created**: 2026-09-25

**Status**: Implemented

**Input**: Add a night mode switch to the SPA navigation near the Horizon control, using the existing UI framework capabilities where they fit.

## User Scenarios & Testing

### User Story 1 - Switch the SPA appearance (Priority: P1)

A visitor can switch the SPA between the default light appearance and a night appearance from the global navigation. The control is available beside the Horizon control when Horizon is shown and remains available when Horizon is hidden.

**Why this priority**: Night mode is the complete user value of the feature and must work from every SPA page.

**Independent Test**: Open any SPA page, activate the appearance control, and verify that the page shell and a representative shared control change to the night appearance without navigating away.

**Acceptance Scenarios**:

1. **Given** the SPA is open in light mode, **When** the visitor activates the night mode control, **Then** the navigation, page background, text, cards, tables, forms, overlays, and shared controls use the night appearance.
2. **Given** the SPA is open in night mode, **When** the visitor activates the same control, **Then** the light appearance returns without a page reload.
3. **Given** the visitor is anonymous and Horizon is not available, **When** the SPA navigation renders, **Then** the appearance control is still present and usable.
4. **Given** the visitor opens a different SPA route after changing the appearance, **When** the new page renders, **Then** the selected appearance remains active.

### User Story 2 - Keep the preference across visits (Priority: P2)

A visitor who selected an appearance sees the same choice after refreshing the page or opening the SPA again in the same browser.

**Why this priority**: A preference that disappears on every reload makes the control difficult to use in practice.

**Independent Test**: Select night mode, reload the SPA, and verify that the navigation and page render in night mode before the visitor interacts with the control.

**Acceptance Scenarios**:

1. **Given** the visitor selected night mode, **When** the page is refreshed, **Then** night mode is restored.
2. **Given** no appearance preference exists, **When** the SPA opens, **Then** it uses the existing light appearance by default.
3. **Given** stored preference data is invalid or unavailable, **When** the SPA opens, **Then** it falls back to the light appearance and remains usable.
4. **Given** a valid night preference exists, **When** the SPA starts, **Then** the initial rendered shell uses the night marker before interactive mounting completes.

### User Story 3 - Understand and operate the control (Priority: P2)

A visitor can identify the current appearance and operate the control with a mouse, keyboard, and assistive technology.

**Why this priority**: The control is part of the global shell and must not hide its state or make navigation harder to use.

**Independent Test**: Inspect the control in both states using keyboard navigation and a screen reader-friendly accessibility tree.

**Acceptance Scenarios**:

1. **Given** light mode is active, **When** the visitor focuses the control, **Then** it has a clear accessible name and indicates that activating it will enable night mode.
2. **Given** night mode is active, **When** the visitor focuses the control, **Then** it indicates the current state and the action to return to light mode.
3. **Given** the visitor uses only a keyboard, **When** they activate the focused control, **Then** the appearance changes and focus remains usable in the navigation.

## Edge Cases

- The control must not depend on authentication or Horizon access.
- The control must work when the navigation collapses or wraps at narrow viewport widths.
- Existing route changes, logout, and Horizon launch must keep their current behavior after the control is added.
- The night palette must preserve readable contrast for links, muted text, table rows, validation messages, focus indicators, and disabled controls.
- Browser storage failures must not prevent the SPA from loading or changing appearance for the current page.
- The first paint should avoid showing a long light-mode flash when a stored night-mode preference exists.

## Requirements

### Functional Requirements

- **FR-001**: The SPA MUST provide one global appearance control in the main navigation near the Horizon control area.
- **FR-002**: The control MUST switch between light and night appearances without a full page reload or route change.
- **FR-003**: The night appearance MUST cover the shared SPA shell and reusable controls, including navigation, cards, tables, forms, dialogs, menus, messages, and overlays used by existing pages.
- **FR-004**: The control MUST be available to anonymous and authenticated visitors, regardless of Horizon access.
- **FR-005**: The SPA MUST persist the selected appearance in the current browser and restore it on a later SPA load.
- **FR-006**: The default appearance MUST remain light when no valid preference exists.
- **FR-007**: The control MUST expose its current state and action through an accessible name, keyboard operation, focus styling, and an appropriate pressed or checked state.
- **FR-008**: Appearance changes MUST not alter route navigation, authentication state, Horizon access, API requests, or logout behavior.
- **FR-009**: The implementation MUST use the existing SPA theme and component facilities where they provide the required light and night states, with project styles covering the shared shell and custom surfaces.
- **FR-010**: The implementation MUST include automated coverage for state changes, persistence and navigation placement, including the anonymous navigation case.

### Key Entities

- **Appearance preference**: A browser-local value with one of the supported appearance states, light or night.
- **Appearance control**: The global navigation control that displays the current state and changes the appearance preference.

## Success Criteria

### Measurable Outcomes

- **SC-001**: A visitor can switch appearance from every SPA route in one interaction, without a route change or full page reload.
- **SC-002**: 100% of the SPA page shells and shared control states used by `/app/competitions`, `/app/cups`, and `/app/persons` meet WCAG AA contrast expectations for text, borders, surfaces, and focus indicators in night mode.
- **SC-003**: A stored valid preference restores the selected appearance on the first rendered SPA state after refresh in supported browsers.
- **SC-004**: Automated coverage verifies both appearance states, persistence, keyboard-accessible control state, and placement for anonymous and authenticated navigation.
- **SC-005**: Existing navigation, authentication, Horizon launch, logout, and route-level tests continue to pass without appearance state changing their behavior.

## Assumptions

- The SPA continues to use PrimeVue Aura and its current global stylesheet.
- Appearance is a browser-local preference; no account field, API endpoint, or server-side synchronization is required.
- Light mode remains the default for existing visitors.
- The first release covers the SPA application shell and shared components. Standalone Horizon pages and unrelated legacy Blade pages are outside the feature.
- The covered route set for acceptance checks is `/app/competitions`, `/app/cups`, and `/app/persons`.
- The existing translation system supplies labels for the control and its state text.
