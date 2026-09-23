# Feature Specification: Cup Event View SPA

**Feature Branch**: `034-cup-event-view-spa`

**Created**: 2026-09-20

**Status**: Implemented; final full-suite verification remains in tasks

**Input**: Move the legacy cup-event group standings page to the SPA. Show a
full cup-event detail card, then a paginated standings table (50 rows by
default) with group selection and athlete-name search. Fix cup-stage links on
the cup view so they open this cup-event view. Retire the replaced legacy UI,
routes, tests, and no-longer-used legacy service code.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View cup-event standings (Priority: P1)

A visitor opens a cup stage and sees its event and cup context followed by the
stage standings for a selected cup group, without being sent to a Blade page.

**Why this priority**: Standings are the purpose of a cup stage; the SPA must
replace the existing public page before its legacy counterpart can disappear.

**Independent Test**: Open a cup event with multiple cup groups, select a
group, search an athlete by name, and navigate between pages of results.

**Acceptance Scenarios**:

1. **Given** an existing cup event, **When** a visitor opens its SPA URL,
   **Then** they see an information card with the cup, linked competition,
   event, event date, and configured points.
2. **Given** a cup event with multiple eligible cup groups, **When** a visitor
   chooses a group, **Then** the table contains only that group's calculated
   standings and the selected group remains visible.
3. **Given** a selected group with more than 50 standings, **When** the page
   opens or the visitor changes page, **Then** it displays at most 50 rows and
   follows the shared pagination controls.
4. **Given** a visitor enters a valid athlete-name search, **When** the search
   is applied, **Then** the table reloads from its first page with matching
   athletes only.
5. **Given** a visitor changes a group or search while a previous points
   request is pending, **When** the later request completes first, **Then**
   only results for the latest selection are rendered.
6. **Given** a points request fails, **When** it completes, **Then** stale rows
   are cleared and the table displays the shared error state.
7. **Given** a missing, disabled, or inactive cup event, **When** its SPA URL
   is opened, **Then** the visitor receives the established SPA not-found
   state.

---

### User Story 2 - Reach the correct cup-event view (Priority: P1)

A visitor selects a stage from the cup detail table and arrives at the new
full cup-event page rather than the ordinary event page or an obsolete
group-specific legacy URL.

**Why this priority**: The existing destination loses the cup-stage standings
context and is the reported navigation defect.

**Independent Test**: Select a stage-name link from a cup page and verify its
SPA destination includes the selected cup-event identifier and renders the
cup-event view.

**Acceptance Scenarios**:

1. **Given** a cup stage in the cup detail table, **When** a visitor follows
   its main link, **Then** they reach the SPA cup-event view for that stage.
2. **Given** a linked competition or event in the cup-event card, **When** a
   visitor follows it, **Then** it reaches the existing SPA competition or
   event page.

---

### User Story 3 - Retire the legacy standings surface (Priority: P2)

Visitors use one SPA cup-event detail surface, and the legacy Blade controller,
route, template, tests, and uniquely unused legacy support code are removed.

**Why this priority**: Two render surfaces drift and leave maintenance code in
the application after the migration.

**Independent Test**: Confirm the old group-specific URL is no longer routed,
while retained cup tables, exports, cache clearing, and cup-event mutations
still resolve.

**Acceptance Scenarios**:

1. **Given** the migration is deployed, **When** a user requests the former
   cup-event group show URL, **Then** no Blade standings page is rendered.
2. **Given** legacy classes or methods were used only by the retired page,
   **When** usages are inspected, **Then** they are removed rather than kept
   dead.

### Edge Cases

- A cup event without eligible groups or calculated points displays an explicit
  empty state and does not issue an invalid group request.
- A name shorter than the established minimum does not issue a broad name
  query; the page clears rows, displays the shared three-character hint, and
  clearing a valid search reloads the first page.
- A cancelled or superseded request cannot overwrite rows, pagination, or an
  error state selected by a later group or search request.
- A table loading, error, or empty message has enough vertical space for its
  text and remains readable on every table using the shared component.
- A requested group outside the cup event's eligible groups yields no data and
  does not expose standings from another group.
- A missing athlete, club, competition, or event reference renders the shared
  fallback presentation without causing per-row fetches.
- A direct URL with an invalid page or group identifier receives the existing
  validation/not-found behaviour.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST provide a public SPA cup-event detail route that
  identifies a cup event independently of the cup-detail route.
- **FR-002**: The detail page MUST use the established SPA information-card
  pattern and display the public cup-event data: cup name/year/type, linked
  competition and event, event date, and configured points.
- **FR-003**: The page MUST present a group selector populated only with the
  cup event's eligible cup groups; the first available group is selected by
  default.
- **FR-004**: The page MUST show a shared paginated calculated-standings table
  for the selected group, defaulting to 50 rows per page.
- **FR-005**: The standings table MUST retain the legacy public values: place,
  athlete link and name, birth year, club link/name, time, and points, with the
  configured maximum-points result visually distinguished.
- **FR-006**: The table MUST support the established debounced athlete-name
  search, reset pagination whenever group or name changes, and avoid broad
  requests for incomplete names. One- and two-character values MUST clear the
  current rows and show the shared three-character hint without a request; a
  name sent to API MUST contain at least three characters.
- **FR-007**: Read endpoints for the new page MUST be public and use the
  established V1 DTO, validation, serialization, command, application-service,
  domain-port, repository, and `Slice` pagination contracts.
- **FR-008**: The cup detail's stage navigation MUST target the SPA cup-event
  route; links in the new card to its event and competition MUST target their
  existing SPA routes.
- **FR-009**: The legacy `ShowCupEventGroupAction`, its route, Blade template,
  uniquely obsolete controller tests, and only code in `app/Services` that has
  no remaining usage after the migration MUST be removed. Unrelated legacy cup
  tables, exports, cache controls, and mutations remain available.
- **FR-010**: New or changed V1 endpoints MUST have request tests with
  class-level `@see` references; SPA tests MUST cover page loading, group
  selection, filters, pagination, link destination, empty/not-found handling,
  request lifecycle/error handling, and legacy retirement.
- **FR-011**: New user-facing SPA text MUST be added only to the Belarusian
  dictionary.
- **FR-012**: Every asynchronous page and points request MUST accept an
  `AbortSignal`, cancel its predecessor on supersession or unmount, and retain
  a request identity guard for clients that complete a cancelled request.

### Key Entities

- **Cup event**: A cup's stage association with one event and a configured
  maximum points value.
- **Cup group**: An eligible group within a cup whose members receive standings
  for a cup event.
- **Calculated standing**: A group-specific athlete result comprising place,
  athlete, birth year, club, time, and awarded points.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A visitor can reach a selected cup stage's standings in one
  interaction from the cup stage table.
- **SC-002**: Every standings response is bounded to 50 rows by default and
  never mixes entries from different cup groups.
- **SC-003**: Valid group and athlete-name changes reset the table to its first
  page and return only matching standings.
- **SC-004**: Targeted API and SPA regression suites cover all acceptance
  scenarios, including the navigation regression and retired URL.
- **SC-005**: No uniquely obsolete Blade rendering or legacy service code
  remains after the SPA route becomes the sole cup-event standings view.
- **SC-006**: Switching groups or typing during a slow request never shows
  rows or pagination from a previous group or filter.

## Assumptions

- The legacy group tabs are represented by the project's standard SPA select
  control; "select by name" is interpreted as selecting a cup group and the
  text filter searches athlete names.
- The existing calculator remains the source of standings semantics; this
  feature changes delivery, filtering, and pagination rather than scoring.
- Visitors and authenticated users see the same public standings; no new
  administrator-only operations are introduced on this page.
- Existing active-cup constraints continue to decide whether a cup event can
  be read.

## Out of Scope

- Changing cup scoring, ranking calculation, group eligibility, event results,
  or persistence rules.
- Migrating cup table/export/cache/delete workflows not uniquely required by
  the legacy cup-event group page.
