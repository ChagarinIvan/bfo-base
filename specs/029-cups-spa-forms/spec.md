# Feature Specification: SPA Cup Forms and Legacy Route Removal

**Feature Branch**: `029-cups-spa-forms`

**Created**: 2026-09-18

**Status**: Draft

**Input**: Перенести форму создания кубка и редактирования кубка в SPA-приложение, удалить соответствующие legacy Blade-шаблоны и маршруты.

## User Scenarios & Testing

### User Story 1 - Create a cup in SPA (Priority: P1)

An authenticated administrator opens the cup creation page in the SPA, fills in the cup fields,
receives inline validation errors when input is invalid, and creates the cup without leaving the SPA.

**Why this priority**: Creating cups is the primary new workflow and removes the first legacy form.

**Independent Test**: An authenticated request can open the SPA creation route, submit valid data,
and observe the new cup in the cup listing; invalid data returns field-level errors without a record.

**Acceptance Scenarios**:

1. **Given** an authenticated user on the cups listing, **When** they select “Дадаць кубак”,
   **Then** the SPA displays a creation form with name, type, events count, year, and visibility fields.
2. **Given** valid form values, **When** the user submits the form, **Then** the cup is persisted,
   a success notification is shown, and the user is returned to the SPA cups listing.
3. **Given** invalid or incomplete values, **When** the user submits the form, **Then** the SPA
   displays field-level validation errors and does not create a cup.
4. **Given** an unauthenticated user, **When** they access the creation route or creation endpoint,
   **Then** the request is rejected according to the existing authentication contract.

### User Story 2 - Edit a cup in SPA (Priority: P1)

An authenticated administrator selects “Рэдагаваць” for a cup, sees its current values in the SPA
form, updates them, and saves the changes without using the legacy edit page.

**Why this priority**: Editing is the second required administrative workflow and must preserve the
existing cup-management capability during the migration.

**Independent Test**: An authenticated user can load a cup by identifier, edit a field, submit the
form, and verify the persisted value through the listing or API response.

**Acceptance Scenarios**:

1. **Given** an authenticated user on the cups listing, **When** they select “Рэдагаваць”,
   **Then** the SPA loads the selected cup and pre-fills all editable fields.
2. **Given** valid changed values, **When** the user submits the edit form, **Then** the cup is
   updated, a success notification is shown, and the user returns to the SPA cup listing.
3. **Given** a missing or inactive cup, **When** the edit page loads, **Then** the SPA shows a
   not-found error and does not display an editable form.
4. **Given** invalid changed values, **When** the user submits the form, **Then** field-level
   validation errors are displayed and the existing cup data remains unchanged.

### User Story 3 - Retire legacy cup form routes (Priority: P2)

The application no longer exposes the legacy create/edit Blade pages or their form submission routes;
the remaining cup detail, table, event, export, and delete flows continue to work.

**Why this priority**: Removing duplicate entry points prevents users from reaching an obsolete UI and
completes the migration while preserving unrelated legacy cup flows.

**Independent Test**: Legacy form URLs return not found, SPA routes and API requests remain available,
and existing detail/table/event/delete route tests remain green.

**Acceptance Scenarios**:

1. **Given** any client, **When** it requests `/cups/create` or `/cups/{id}/edit`, **Then** the
   legacy form route is not available.
2. **Given** any client, **When** it submits the legacy create/update form endpoints, **Then** those
   legacy mutation routes are not available.
3. **Given** a user using cup details, tables, events, export, or deletion, **When** they follow the
   existing route, **Then** that unrelated flow remains available.

### Edge Cases

- The creation form uses the available `Year` enum values and `CupType` enum values; unsupported
  values are rejected by the API.
- `eventsCount` must be an integer from 1 through 100.
- Visibility defaults to visible for a new cup and can be explicitly disabled during creation or edit.
- API failures other than validation show a generic localized error and leave the form usable for retry.
- Save buttons show a pending state and prevent duplicate submissions while a request is in flight.
- The edit form must not overwrite fields with stale data if its load request fails or is superseded.

## Requirements

### Functional Requirements

- **FR-001**: The SPA MUST provide authenticated routes for creating and editing cups.
- **FR-002**: The creation and edit forms MUST expose name, type, events count, year, and visibility.
- **FR-003**: The API MUST provide authenticated create, view, and update cup operations using the
  existing V1 JSON conventions and camelCase field names.
- **FR-004**: The API MUST validate `name`, `eventsCount`, `year`, `type`, and `visible`, returning
  structured HTTP 422 field errors for invalid input.
- **FR-005**: Successful create and update operations MUST return the serialized cup representation
  required by the SPA and preserve the existing authenticated audit-field behavior.
- **FR-006**: The SPA MUST display server validation errors next to their fields and a localized
  fallback error for non-validation failures.
- **FR-007**: The SPA MUST show a pending state during create/update requests and prevent duplicate submits.
- **FR-008**: Successful create/update operations MUST show a localized success notification and
  navigate to the SPA cups listing.
- **FR-009**: Unauthenticated users MUST be denied access to create, view-for-edit, and update operations.
- **FR-010**: The legacy cup create/edit Blade templates and their create/edit form routes MUST be removed.
- **FR-011**: Existing cup detail, table, event, export, and delete routes MUST remain available.
- **FR-012**: New and changed API behavior MUST have request tests, and new SPA behavior MUST have
  component tests covering the primary success and validation/error flows.
- **FR-013**: New SPA text MUST be added only to the Belarusian dictionary; Russian SPA translations
  are out of scope.

### Key Entities

- **Cup**: A competition cup with identifier, name, type, event count, year, visibility, active state,
  and audit impressions.
- **Cup form payload**: The editable Cup fields submitted by an authenticated administrator using
  camelCase names.
- **Validation error**: A structured API error containing a field and localized/displayable message.

## Success Criteria

### Measurable Outcomes

- **SC-001**: An authenticated administrator can complete create and edit flows entirely in SPA without
  receiving a legacy Blade form response.
- **SC-002**: 100% of invalid form submissions covered by acceptance tests return HTTP 422 with field
  errors and do not mutate cup data.
- **SC-003**: 100% of legacy create/edit page and mutation routes covered by tests are unavailable after
  migration, while unrelated cup routes remain green.
- **SC-004**: The frontend CI completes with all SPA tests, typecheck, lint, and production build passing.
- **SC-005**: The backend quality gates complete without new CS, PHPStan, or regression-test failures.

## Assumptions

- Existing Application cup commands/services and domain validation are reused or adapted rather than
  introducing a second cup mutation model.
- The SPA uses the existing `/api/v1/cups` resource family and the existing authenticated Pinia/session
  flow.
- The SPA create and edit routes use the existing `/app/*` SPA route convention; legacy `/cups/*`
  detail/table URLs remain ordinary links because they are still Blade flows.
- The existing `CupDto` field rules are the baseline for API validation unless a regression test proves
  that a rule must be corrected.
- Deleting a cup is not part of this migration; its existing legacy confirmation flow remains in scope
  only for preservation.
- Mobile-specific layout and a new API version are out of scope.
