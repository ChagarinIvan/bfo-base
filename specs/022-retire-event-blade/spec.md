# Feature Specification: Retire Event Blade

**Feature Branch**: `022-retire-event-blade`
**Created**: 2026-09-12
**Status**: Implemented
**Input**: User description: "Replace the remaining `resources/views/events` pages with SPA pages, remove their controllers and related legacy DTOs, services, repositories, and follow existing SPA layout and controls."

## Clarifications

### Session 2026-09-12

- Q: Should the remaining Cup Blade pages be detached from the legacy event presentation DTO in this feature? → A: Keep Cup pages on Blade and move their event representation to `ViewEventDto` if its contract is sufficient.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Manage an event without Blade (Priority: P1)

An authenticated organizer creates or edits an event from the SPA, supplies its title, description, date, and either a protocol file or an OBelarus URL, and then sees the existing event information and processing outcome.

**Why this priority**: Creating and correcting events is the core workflow still blocked on the legacy event pages.

**Independent Test**: An authenticated user can open the SPA form from a competition, submit valid event data with each supported protocol source, and edit an existing event without any request reaching a legacy event route.

**Acceptance Scenarios**:

1. **Given** an authenticated organizer viewing a competition, **When** they choose to add an event, **Then** the SPA shows a form visually consistent with existing competition, group, and person forms.
2. **Given** an existing event, **When** an authenticated organizer edits its details or replaces its protocol source, **Then** the saved event reflects the submitted values and shows validation errors next to invalid fields.
3. **Given** a submitted protocol file or OBelarus URL, **When** event processing is accepted, **Then** the organizer receives the same success or failure feedback as the previous workflow.

---

### User Story 2 - Perform remaining event operations from the SPA (Priority: P2)

An authenticated organizer can deactivate an event and unite selected competition events without opening an event Blade page.

**Why this priority**: These actions complete the remaining event-management surface and allow the obsolete event route group to disappear.

**Independent Test**: From SPA competition and event views, an authenticated organizer completes each operation and the legacy event URL has no matching route.

**Acceptance Scenarios**:

1. **Given** an active event, **When** the organizer confirms deactivation, **Then** it no longer appears as active.
2. **Given** two or more eligible events in one competition, **When** the organizer selects them for uniting, **Then** the resulting combined event is available using the current business rules.

---

### User Story 3 - Retire the obsolete event presentation stack (Priority: P3)

A maintainer can verify that no remaining application path renders an event Blade view or depends on event-only legacy presentation code.

**Why this priority**: Removing the unused path reduces duplicate UI behaviour and avoids maintaining incompatible presentation models.

**Independent Test**: Automated checks show no `resources/views/events` files, legacy event web routes, or unreachable event-only controllers/services/tests remain; active SPA and Cup workflows continue to work.

**Acceptance Scenarios**:

1. **Given** the migration is complete, **When** a maintainer inspects registered web routes, **Then** no route points to an obsolete event Blade controller.
2. **Given** existing Cup pages remain available, **When** they render event information, **Then** they do so without relying on an event presentation object that is being retired.

### Edge Cases

- A user who is not authenticated cannot access event-management operations.
- A form submission with missing required values, an unsupported protocol file, or an unreachable OBelarus URL preserves entered values where possible and presents an actionable error.
- An organizer cannot unite fewer than two eligible events or events from different competitions.
- A direct request to a removed legacy event URL returns the application's normal not-found response rather than a Blade fallback.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide SPA routes and navigation for all workflows currently represented by `resources/views/events`: create, edit, protocol-source selection, and unite events.
- **FR-002**: The event create and edit SPA forms MUST use the established application layout, spacing, controls, icon-bearing action buttons, validation style, loading state, and responsive behaviour used by existing SPA management pages.
- **FR-003**: The system MUST preserve the current authorization and business validation rules for creating, updating, deactivating, and uniting events.
- **FR-004**: The system MUST provide an SPA-accessible action to deactivate an event, with confirmation for the destructive action.
- **FR-004a**: Event create and unite actions MUST take competition identity from the route parameter; request DTOs MUST contain only body/form data.
- **FR-004b**: Invalid protocol source content MUST be represented as a domain error and translated at the Application/API boundary to HTTP 400 with error code `invalid_protocol`.
- **FR-004c**: Event information updates and protocol replacements MUST be separate domain operations with separate domain events and handlers.
- **FR-005**: The system MUST remove `resources/views/events`, the corresponding legacy web routes/controllers, and event-only legacy application DTOs, services, repository methods, tests, and helpers once they have no callers.
- **FR-006**: The system MUST leave unrelated legacy surfaces operational, including Cup pages and Cup-event administration.
- **FR-007**: The system MUST keep Cup pages on Blade and replace their `LegacyViewEventDto` dependency with `ViewEventDto` before deleting the legacy DTO, provided that DTO supplies their required event data.
- **FR-008**: The system MUST keep existing public event API routes and the already delivered event-view SPA route working.
- **FR-009**: Unite events MUST lock selected source events in a transaction, persist the combined event before generating dependent distances and protocol lines, and leave source events unchanged.

### Key Entities *(include if feature involves data)*

- **Event**: A dated competition stage with a title, description, protocol source, active state, and optional relation to a combined event.
- **Protocol source**: Either an uploaded protocol file or an OBelarus URL supplied when an event is created or updated.
- **Combined event**: An event produced by applying existing unite rules to selected events from one competition.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An authenticated organizer can complete create, edit, deactivate, and unite-event workflows using SPA navigation without visiting an event Blade URL.
- **SC-002**: Every remaining event-management form displays its initial interactive state within two seconds on the supported desktop viewport under normal local development conditions.
- **SC-003**: Invalid event input is rejected before a successful operation and each failed field has a visible explanatory message.
- **SC-004**: Repository inspection finds zero files under `resources/views/events` and zero registered legacy event web routes after delivery.
- **SC-005**: Existing public event APIs, event view SPA, and Cup workflows retain their covered behaviour after the cleanup.

## Assumptions

- Existing API patterns and SPA components are the visual and interaction reference; this feature introduces no independent design system.
- The data model and existing protocol parsing/queue semantics are retained.
- Cup pages remain Blade in this feature unless only their event presentation dependency must be adjusted to permit legacy event DTO removal.
- The legacy URLs are intentionally retired rather than redirected, because SPA navigation supplies their replacements.
