# Feature Specification: Cup View SPA

**Feature Branch**: `030-cup-view-spa`

**Created**: 2026-09-19

**Status**: Implemented

**Input**: Move the cup detail page to the SPA with a cup information card and
paginated cup-event listing. Preserve established filters, authenticated
impressions and controls, and retire the legacy cup-show surface.

## User Scenarios & Testing

### User Story 1 - View a cup and its stages (Priority: P1)

A visitor opens a cup from the SPA listing and sees its information in the
standard details card plus a paginated list of that cup's stages, without being
sent to a Blade page.

**Why this priority**: A cup page is the public entry point to its stages and
standings.

**Independent Test**: Open an existing cup, filter its stages by date and
name, change page size, and verify that only stages of the opened cup are
shown.

**Acceptance Scenarios**:

1. **Given** an existing cup, **When** a visitor opens its SPA URL, **Then**
   they see the full public `ViewCupDto` data — name, year, type with icon,
   stage count, linked group badges, and visibility information — in the
   standard information-card layout.
2. **Given** a cup with stages, **When** its page opens, **Then** the stage
   table shows at most 50 rows by default and offers pagination using the
   shared listing-table controls.
3. **Given** a visitor enters a complete stage-name filter or selects a date,
   **When** the filter changes, **Then** the stage list reloads from its first
   page with only matching stages of that cup.
4. **Given** a visitor follows a stage link, **When** they open it, **Then**
   they reach the existing SPA event view.
5. **Given** a missing cup, **When** a visitor opens its SPA URL, **Then**
   they receive the existing SPA not-found state.

---

### User Story 2 - Use authenticated cup controls (Priority: P2)

An authenticated administrator sees the cup audit information and all controls
that were available on the cup page, while guests see no administrative
controls or impressions.

**Why this priority**: The migration must retain the established management
workflow without exposing maintenance operations publicly.

**Independent Test**: Compare guest and authenticated rendering of the same
cup; verify card and stage controls, audit details, and destinations only for
the authenticated view.

**Acceptance Scenarios**:

1. **Given** an authenticated administrator, **When** they open a cup, **Then**
   the card exposes retained add-stage, cache-clear, table-export, table, and
   delete controls.
2. **Given** an authenticated administrator, **When** they inspect a stage
   row, **Then** they see edit and delete controls for that stage.
3. **Given** a guest, **When** they open the same cup, **Then** cup and stage
   impressions, card controls, and stage-row controls are absent.
4. **Given** a destructive control is selected, **When** confirmation is
   required, **Then** the established confirmation interaction is shown before
   the deletion target is invoked.

---

### User Story 3 - Retire the legacy cup-show page (Priority: P3)

Visitors and administrators use the SPA cup URL as the single cup-detail
surface; the superseded Blade show page is no longer routable.

**Why this priority**: Keeping two detail pages creates diverging behaviour and
keeps legacy rendering alive after the migration.

**Independent Test**: Request the retired cup-show URL and confirm it is not
registered, while retained cup-table, cup-event form, and mutation workflows
continue to resolve.

**Acceptance Scenarios**:

1. **Given** the migration is deployed, **When** a user follows a cup link
   within the SPA, **Then** it targets the SPA cup-detail route.
2. **Given** the old cup-show URL, **When** it is requested, **Then** no Blade
   cup-detail page is rendered.
3. **Given** retained cup-table, cup-event create/edit, export, cache-clear,
   and delete workflows, **When** they complete, **Then** redirects lead to
   the SPA cup page or cup list as appropriate.

### Edge Cases

- A cup without stages renders an explicit empty table state and valid
  pagination metadata.
- A date or name filter cannot return stages belonging to another cup.
- An incomplete name filter follows the competition listing's minimum-length
  behaviour and does not issue a broad name query.
- A guest cannot obtain impression fields or management actions from cup or
  stage-list responses.
- A cup without groups does not generate a malformed table destination.
- The cup-events endpoint accepts optional `eventIds[]` for future consumers
  that already hold a bounded event selection.

## Requirements

### Functional Requirements

- **FR-001**: The system MUST provide a public SPA cup-detail route and update
  SPA cup-name links to use it.
- **FR-002**: The cup-detail view MUST use the established SPA information-card
  presentation and display every public `ViewCupDto` field, including type
  icon, linked group badges, and visibility.
- **FR-003**: The cup-detail view MUST show a shared paginated listing of cup
  stages with a default page size of 50.
- **FR-004**: The stage list MUST support established date and name filters and
  reset to the first page whenever either filter changes.
- **FR-005**: The stage list MUST display stage name, date, points, and public
  stage navigation without loading a stage from another cup.
- **FR-006**: Cup and stage impressions MUST be visible only to authenticated
  administrators.
- **FR-007**: Card controls and stage-row edit/delete controls MUST be visible
  only to authenticated administrators; the card preserves add-stage,
  cache-clear, table-export, table, and delete operations.
- **FR-008**: The stage list MUST retain the established cup-stage read model
  (stage IDs, points, and authenticated impressions) while becoming paginated.
  Event details are loaded separately through the bounded events endpoint.
- **FR-009**: The existing cup-stage listing use case MUST paginate at the data
  source and MUST NOT load the complete stage collection into memory.
- **FR-010**: The legacy cup-show controller, route, Blade template, and tests
  made obsolete by this SPA page MUST be removed. Retained cup-table, export,
  cache, create/edit stage, and mutation workflows remain available.
- **FR-011**: Any retained workflow that previously returned to the legacy cup
  show page MUST return to the SPA cup page or cup list instead.
- **FR-012**: New or changed JSON endpoints and SPA behaviours MUST have API
  request and SPA regression tests covering filters, pagination, missing cups,
  authenticated visibility, action visibility, and retirement of the legacy
  page.
- **FR-013**: New user-facing text MUST be added only to the Belarusian
  dictionary.

### Key Entities

- **Cup**: A seasonal competition series with groups, type, visibility, and
  audit impressions.
- **Cup stage**: A cup-owned reference to an event, with points and its own
  audit impressions.
- **Stage event**: The dated event linked from a cup stage and used for stage
  name and navigation.

## Success Criteria

### Measurable Outcomes

- **SC-001**: A visitor can open a cup and reach any visible stage in no more
  than two page interactions after choosing the cup from the listing.
- **SC-002**: The initial stage listing renders no more than 50 rows, and each
  page contains only stages belonging to the selected cup.
- **SC-003**: Date and valid name filters produce matching stage pages and
  always reset pagination to page one.
- **SC-004**: A guest exposes zero administrative card controls, row controls,
  or impressions; an authenticated administrator exposes all retained controls.
- **SC-005**: Targeted API and SPA regression suites cover every acceptance
  scenario and pass.

## Assumptions

- The existing SPA authentication state remains the source for conditional
  rendering; server-side authenticated serialization continues to protect
  impressions.
- Existing cup-event create/edit/delete, cache-clear, export, and table actions
  remain legacy workflow destinations until their own migrations; only the
  cup-show render surface is retired here.
- The existing cup-event DTO remains the read contract for the paginated list.
- The current shared listing table, filter panel, information card, action
  button, and delete-confirmation patterns are reused.

## Out of Scope

- Migrating cup-event create or edit forms, cup tables, exports, cache
  implementation, or calculated cup-event group pages to SPA.
- Changing cup or cup-stage persistence, scoring, groups, or visibility rules.
- Adding cup icons to event pages.
