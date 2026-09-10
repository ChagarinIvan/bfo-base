# Feature Specification: Event View SPA

**Feature Branch**: `021-event-view-spa`
**Created**: 2026-09-10
**Status**: Ready for planning
**Input**: Migrate the event view page to the SPA while preserving its protocol-line workflow.

## User Scenarios & Testing

### User Story 1 - View an event and its results (Priority: P1)

A visitor opens an event from the competition SPA and sees the event information and the protocol lines for a selected distance without being sent to a Blade page.

**Why this priority**: Event results are the primary public value of the page.

**Independent Test**: Open an event with multiple distances, choose each distance, and verify that only its protocol lines and the legacy-visible result columns are shown.

**Acceptance Scenarios**:

1. **Given** an event exists, **When** a visitor opens its SPA URL, **Then** they see its name, date, competition link, cup badges, and a distance selector populated with every event distance.
2. **Given** a visitor selects a distance, **When** its result list loads, **Then** the table shows that distance's protocol lines with the public columns and values that were visible on the retired event page.
3. **Given** a distance has no protocol lines, **When** it is selected, **Then** the page shows an explicit empty state rather than stale results.
4. **Given** an invalid or unavailable event or distance, **When** a visitor opens or selects it, **Then** they receive the existing SPA not-found or error state.

---

### User Story 2 - Use staff-only event result controls (Priority: P2)

An authenticated staff member sees audit information and can reach the existing legacy event edit form or the SPA form to assign a person to a protocol line.

**Why this priority**: These controls preserve the staff workflow while event editing remains intentionally outside this migration.

**Independent Test**: Authenticate, open an event, and verify audit details, the legacy edit target, the Actions column, and each assignment target; repeat as a guest and verify none are exposed.

**Acceptance Scenarios**:

1. **Given** an authenticated staff member, **When** they view an event, **Then** they see created and updated impressions and an Edit button that opens the current legacy event edit form.
2. **Given** an authenticated staff member, **When** they view a protocol-line table, **Then** it includes an Actions column with an Assign person control for every line that opens the existing SPA assignment form.
3. **Given** a guest, **When** they view the same event, **Then** audit details, Edit, and the Actions column are absent.

---

### User Story 3 - Search and identify protocol-line affiliations (Priority: P3)

A visitor can narrow an event's selected-distance results by an athlete's first or last name and can see a link to a known club when a protocol line's raw club name matches that club after normalization.

**Why this priority**: This retains useful legacy-table behaviour while making large result lists practical to browse.

**Independent Test**: Filter a selected distance by either name component and verify that only matching lines remain; use a raw club name with whitespace/case variation and verify its normalized club link.

**Acceptance Scenarios**:

1. **Given** a selected distance has protocol lines, **When** a visitor enters a first or last name filter, **Then** the list returns matching athletes only and retains the selected distance.
2. **Given** a protocol line's raw club name normalizes to a known club, **When** club information is requested, **Then** the line displays its original raw club name as a link to that club.
3. **Given** no known club matches the normalized raw name, **When** club information is requested, **Then** the original raw club name is displayed without a broken link.

### Edge Cases

- An event without distances shows a clear empty state and does not request protocol lines.
- A selected distance belonging to another event is rejected rather than leaking results.
- Guests never receive staff-only impression or action data in the rendered page.
- Missing optional result values use the same empty representation as the legacy page.

## Requirements

### Functional Requirements

- **FR-001**: The system MUST provide an SPA event-view route and use it from event links in the competition SPA.
- **FR-002**: The event view MUST show the event's public information, its competition link, and associated cup badges.
- **FR-003**: The event view MUST obtain all distances belonging to the event in one unpaginated list and provide them through a distance selector.
- **FR-004**: The event view MUST list only protocol lines for the selected event distance, without loading unrelated event or competition details for each line.
- **FR-005**: The protocol-line list MUST preserve the legacy event table's public result information: serial number, athlete names and links where assigned, raw club name and known-club link, birth year, rank, time, place, complete rank, points, and out-of-competition status when applicable.
- **FR-006**: The protocol-line list MUST support filtering by athlete first name or last name while retaining the selected distance.
- **FR-007**: A protocol-line response MUST include its raw club name and, when club resolution is requested, the matching club determined by normalized club name.
- **FR-008**: Authenticated staff MUST see event impressions, an Edit action targeting the existing legacy event edit page, activation-date data where previously visible, and an Actions column linking each protocol line to the existing SPA person-assignment form.
- **FR-009**: Guests MUST NOT see the staff-only controls or audit information.
- **FR-010**: The legacy event show route, controller, Blade view, unique distance-show route/action, and tests made obsolete by the SPA event view MUST be removed; legacy event create, edit, update, delete, protocol-download, unit-event, and cup workflows remain.
- **FR-011**: The new and changed JSON and SPA behaviour MUST have regression coverage, including authorization, event/distance scoping, normalized club resolution, name filters, and no per-line relation loading.
- **FR-012**: The feature MUST migrate distance querying from the legacy repository to the target
  Domain repository port and Infrastructure Eloquent implementation, without changing existing
  distance-dependent workflows.

### Key Entities

- **Event**: A dated competition stage with public information, audit impressions, cup associations, and distances.
- **Distance**: An event-owned result category selected before protocol lines are listed.
- **Protocol line**: A raw result row with athlete, result, raw club-name, optional assigned person, and optional resolved club.
- **Club**: A known organization linked only when its normalized name matches the protocol line's raw club name.

## Success Criteria

### Measurable Outcomes

- **SC-001**: A visitor can open an event, select a distance, and see its results in no more than two page interactions after following the event link.
- **SC-002**: Every distance belonging to an event is available in the selector, and selecting any one displays no protocol line belonging to another distance or event.
- **SC-003**: A guest sees no staff-only event controls, while an authenticated staff member can reach the retained event edit form and every protocol-line assignment form from the event view.
- **SC-004**: Targeted API and SPA regression tests cover all acceptance scenarios and pass.

## Assumptions

- The existing SPA authentication state is the source of staff-only rendering decisions.
- The existing legacy edit page remains the edit destination until event editing is migrated separately.
- The existing protocol-line list response and pagination conventions are extended rather than replaced.
- Migrating the existing distance repository and its callers is in scope because the event-distance
  list needs the target persistence path; existing distance workflows retain their observable behaviour.
- "Actions" means the existing Assign person flow; extraction and other protocol-line mutations are out of scope.

## Out of Scope

- Migrating the event create, edit, update, delete, protocol-download, unit-event, cup, or person-assignment forms themselves.
- Redesigning the event information or result-table visual system beyond established SPA view-page and button patterns.
- Changing normalization rules or mutating club/person associations while viewing results.
