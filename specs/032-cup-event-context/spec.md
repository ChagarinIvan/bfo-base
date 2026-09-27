# Feature Specification: Cup Event Context Badges

**Feature Branch**: `032-cup-event-context`

**Created**: 2026-09-20

**Status**: Implementing

**Input**: Show a cup badge beside event names on every existing event table:
competition detail, person detail, rank page, and group detail. Each badge shows
the cup type icon and cup-group label in a dedicated “Кубкі” column, reveals
the cup name on hover, and opens the existing legacy cup-stage view. The event
detail card shows the same list.

## User Scenarios & Testing

### User Story 1 - Identify an event's cup (Priority: P1)

A visitor sees whether an event belongs to one or more cup groups wherever that
event appears in an event table or its detail card.

**Why this priority**: Cup membership is important context for results and must
be visible consistently without opening a separate page.

**Independent Test**: Open each of the four event-table pages with an event
linked to a cup and verify the compact cup badge beside its event name.

**Acceptance Scenarios**:

1. **Given** a listed event linked to an active cup, **When** a visitor views
   its row on the competition-detail, person-detail, rank, or group-detail
   page, **Then** a cup badge appears in the “Кубкі” column.
2. **Given** a listed event linked to several active cups, **When** a visitor
   views its row, **Then** every linked cup is represented by a separate badge.
3. **Given** a listed event not linked to an active cup, **When** a visitor
   views its row, **Then** no empty badge or placeholder is shown.

---

### User Story 2 - Navigate from badge to cup stage (Priority: P1)

A visitor can use a cup badge to open the existing cup-stage result view.

**Why this priority**: The badge must provide a useful path to the detailed cup
context rather than being only decorative.

**Independent Test**: Select a badge on each supported table and verify that it
opens the linked cup's existing stage view.

**Acceptance Scenarios**:

1. **Given** a cup badge beside an event, **When** a visitor selects it,
   **Then** the existing cup-stage result view opens for that cup and event.
2. **Given** a visitor hovers or focuses a cup badge, **When** the popover is
   shown, **Then** it names the linked cup and identifies its type with the
   matching icon.

### Edge Cases

- A response may contain no cup-stage associations for some or all requested
  event identifiers; the affected rows remain usable without badges.
- A listed event can belong to several cups; each association is shown once.
- Only active cups and active stages are presented, so badges never lead to an
  unavailable stage view.
- Events shown on one table are requested together, avoiding one additional
  request per table row.

## Requirements

### Functional Requirements

- **FR-001**: The system MUST return compact active cup-stage associations for
  a supplied list of event identifiers, including the cup identifier, cup name,
  cup type, stage identifier, and a legacy-stage-view destination.
- **FR-002**: The association request MUST return all associations for the
  supplied events in one response and must not expose unrelated associations.
- **FR-003**: The competition-detail, person-detail, rank and group-detail
  event tables MUST show a dedicated “Кубкі” column with badges for each
  associated cup group.
- **FR-004**: The SPA event-detail card MUST show the same badge list.
- **FR-007**: Each cup badge MUST use the established cup-type icon, the
  cup-group name and the deterministic existing cup-group colour, and provide
  the cup name in an accessible hover/focus popover.
- **FR-008**: Each cup badge MUST link to the existing legacy cup-stage view.
- **FR-009**: New user-facing text MUST be added only to the Belarusian
  dictionary.
- **FR-010**: API and SPA tests MUST cover the association payload, empty and
  multiple-association cases, and badge rendering/linking on every supported
  table.

### Key Entities

- **Cup-stage context**: A compact representation of an active association
  between an event and a cup, containing display and navigation information.
- **Cup badge**: A linked visual marker beside an event name that conveys the
  cup type and exposes the cup name.

## Success Criteria

### Measurable Outcomes

- **SC-001**: All four named event tables show one badge per active cup
  association for every returned event row.
- **SC-002**: A visitor can open the relevant cup-stage result view with one
  badge selection.
- **SC-003**: Loading a table with any number of visible event rows requires
  at most one additional association request.
- **SC-004**: Automated API and SPA tests cover empty, single and multiple cup
  associations plus all four display locations.

## Assumptions

- Existing anonymous access rules for event lists and cup-stage views remain
  unchanged.
- Every group defined by the linked cup is returned as its own badge and legacy
  stage-view destination.
- The existing cup-type icon component and project popover styling are reused.
- Creating a new SPA cup-stage view is out of scope.
