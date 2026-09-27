# Research: Cup View SPA

## Decisions

### Use a nested cup-events endpoint

**Decision**: Add `GET /api/v1/cups/{cupId}/events` with optional
authentication and shared `Pagination`.

**Rationale**: It keeps cup ownership at the route boundary, gives the SPA a
single bounded data source, and follows the V1 action/Slice pattern used by
event and competition lists.

### Preserve `ViewCupEventDto`

**Decision**: Reuse the existing DTO and assembler instead of introducing a
new SPA-specific event shape.

**Rationale**: It exposes the cup-stage ID, points, and audit impressions. The
SPA resolves its bounded event IDs through the existing events endpoint with a
competition-name resource flag, avoiding an event/competition join in this API.

### Paginate at the CupEvent repository

**Decision**: Replace the list service's `byCriteria(...)->all()` read with
`paginate(Criteria)` returning `Slice`.

**Rationale**: The current implementation loads every stage before mapping,
which cannot meet the bounded-list requirement.

### Filter by linked event fields

**Decision**: `date` matches the linked event date; `name` matches normalized
event name or competition name. The query is always additionally constrained
by cup ID.

**Rationale**: This mirrors the competition page's familiar filters and the
old stage title combines competition and event names.

### Add bounded event-ID filtering

**Decision**: Accept optional `eventIds[]` in the cup-event search DTO and
apply it directly to `cup_events.event_id`.

**Rationale**: It supports future callers with a known event subset without
changing ownership or requiring relation loading.

### Retain unported legacy operations

**Decision**: Card and row actions use retained legacy form/mutation/export/
cache/table URLs until their own SPA migrations.

**Rationale**: The requested migration is the cup view, not the cup-event
forms or result-table workflows. Their redirects must return to the new SPA
detail route.
