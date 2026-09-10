# Research: Event View SPA

## Decisions

### Reuse EventRepository for event detail and distance list

- **Decision**: Add focused Event Application queries/actions; use the existing `EventRepository::byId()`, which already loads competition, cup, distance, and group data.
- **Rationale**: It keeps the query in the existing port and avoids a new Distance repository for an event-owned list.
- **Alternatives considered**: Return distances from the existing paginated event list (insufficient event detail); query Eloquent in a controller (breaks layer boundary); add a repository (prohibited for this feature).

### Extend the bounded protocol-line list

- **Decision**: Add `distanceId`, optional athlete name, and `withClub` to the existing list API. The event page sends `distanceId` and `withClub=1`, never `withEvent` or `withCompetition`.
- **Rationale**: The existing person page retains its contract; the new event view gains a bounded result list and avoids needless nested relations.
- **Alternatives considered**: Dedicated event-results endpoint (duplicates protocol-line query/DTO); fetching all event lines client-side (poor scale); loading event/competition on every row (unneeded work).

### Resolve clubs in a batch through the existing Club port

- **Decision**: Normalize distinct raw protocol-line club names once, fetch matching active clubs through a batch method on the existing Club port, and map club DTO data in the Application layer only when requested.
- **Rationale**: It preserves legacy normalized matching and prevents N+1 queries.
- **Alternatives considered**: One club lookup per row (N+1); eager-load `person.club` (does not represent the raw result club); return every club to the browser (over-fetching).

### Retain only the legacy edit flow

- **Decision**: The SPA Edit button is a normal link to `/events/{eventId}/edit`; remove the two public view routes only after SPA links replace them.
- **Rationale**: It meets the requested migration boundary and preserves all remaining event maintenance workflows.
- **Alternatives considered**: Migrate editing now (scope expansion); leave the old show routes (two competing views).

### Adopt established SPA presentation patterns

- **Decision**: Follow `CompetitionDetailsPage` and existing action components for cards, impressions, action buttons, not-found behaviour, and paginated tables.
- **Rationale**: Ensures button styling and authorization behaviour match existing view pages.
- **Alternatives considered**: A custom event-specific component system (inconsistent and unnecessary).
