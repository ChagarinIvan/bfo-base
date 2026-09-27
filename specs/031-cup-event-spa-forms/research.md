# Research: Cup Event SPA Forms

## Decision: use target CupEvent commands and services

**Rationale**: Legacy create/update actions directly mutate Eloquent models.
The existing CupEvent repository, assembler and aggregate events provide the
target migration seam.

**Alternatives considered**: Extending `CupEventsService` or legacy controllers
was rejected because it grows the legacy surface.

## Decision: model the form API under the cup resource

Use the aggregate endpoints `GET/POST /api/v1/cup-events` and
`PUT /api/v1/cup-events/{cupEventId}`; create carries `cupId` in its body.

**Rationale**: The route expresses stage ownership and maps directly to SPA
route parameters.

## Decision: reuse paginated event listing for event selection

**Rationale**: `GET /events` already filters and paginates. The former
10,000-record Blade select is rejected as unbounded.

## Decision: remove only superseded legacy form paths

Delete create/edit controllers, mutation actions, form views, routes and tests.
Keep deletion, table, export and cache endpoints outside this feature.
