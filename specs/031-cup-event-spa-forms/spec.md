# Feature Specification: Cup Event SPA Forms

**Feature Branch**: `031-cup-event-spa-forms`

**Created**: 2026-09-20

**Status**: Implemented (backend request suite pending local MySQL availability)

**Input**: Move CupEvent creation and editing to the SPA, reuse established SPA
form patterns, and retire the superseded legacy form surface.

## User Scenarios & Testing

### User Story 1 - Add a stage to a cup (Priority: P1)

An authenticated administrator opens a cup in the SPA, adds a stage through a
standard SPA form, and returns to that cup.

**Independent Test**: Open the add-stage action, choose an event and points,
save, then see the new stage in the cup stage list.

**Acceptance Scenarios**:

1. **Given** an authenticated administrator, **When** they select “Add stage”
   on a cup card, **Then** they reach an SPA form pre-associated with that cup.
2. **Given** valid event and points values, **When** they save, **Then** the
   stage is created and the user returns to the SPA cup page.
3. **Given** invalid or incomplete input, **When** they save, **Then** the form
   shows field errors and preserves entered values.

---

### User Story 2 - Edit a cup stage (Priority: P1)

An authenticated administrator edits a stage from the cup stage table without
leaving the SPA.

**Independent Test**: Open an existing stage edit action, change event or
points, save, and verify the updated values on the cup page.

**Acceptance Scenarios**:

1. **Given** an existing stage, **When** the administrator selects edit,
   **Then** the SPA form loads its current event and points.
2. **Given** valid changes, **When** they save, **Then** the stage is updated
   and the user returns to its cup page.
3. **Given** a missing cup or stage, **When** its form URL is opened, **Then**
   the existing SPA not-found state is shown.

---

### User Story 3 - Retire legacy stage forms (Priority: P2)

The SPA is the only create/edit UI for cup stages, while API mutation behaviour
and unrelated legacy cup operations remain available.

**Independent Test**: Verify old create/edit form URLs are no longer
registered, while new SPA routes are protected and stage mutations work.

## Edge Cases

- A duplicate event association or a stage belonging to another cup is rejected
  with the established validation/domain error and does not alter data.
- Guests are redirected to login for SPA create/edit routes and cannot invoke
  mutation APIs.
- Event options are bounded and searchable using the established event picker
  pattern.

## Requirements

### Functional Requirements

- **FR-001**: The system MUST provide authenticated SPA routes for creating and
  editing a cup stage.
- **FR-002**: The forms MUST reuse the established SPA form layout, controls,
  validation display, save/cancel actions, and Belarusian text.
- **FR-003**: The create form MUST bind the stage to its route cup and allow an
  administrator to select an event and points.
- **FR-004**: The edit form MUST load and allow modification of the existing
  stage event and points only within its route cup.
- **FR-005**: Successful create and update operations MUST return to the SPA
  cup-detail page.
- **FR-006**: The system MUST expose protected V1 mutation/read contracts
  needed by the forms and cover them with request tests.
- **FR-007**: The cup card add-stage action and stage-table edit action MUST
  target the new SPA routes.
- **FR-008**: Legacy create/edit form controllers, routes, templates and tests
  replaced by the SPA forms MUST be removed.
- **FR-009**: New user-facing text MUST be added only to the Belarusian
  dictionary.

### Key Entities

- **Cup stage**: Association of one cup, one event and points.
- **Event option**: Existing event selectable when creating or editing a stage.

## Success Criteria

### Measurable Outcomes

- **SC-001**: An administrator can create or edit a stage from its cup page in
  no more than three interactions after opening the action.
- **SC-002**: Guests can perform zero stage mutations through SPA routes or V1
  endpoints.
- **SC-003**: Request and SPA regression tests cover create, edit, validation,
  authorization, redirects and legacy-form retirement.

## Assumptions

- Existing CupEvent mutation rules, endpoints and event option data are reused
  unless a small V1 adapter is needed for the SPA.
- Cup detail remains the return destination after save or cancel.
- Cup tables, exports, cache operations and stage deletion are out of scope.
