# Research: Retire Event Blade

## Decision: expose event-management mutations as authenticated API V1 operations

**Rationale**: Existing SPA create/edit/delete flows already use authenticated JSON actions and field-level 422 errors. Protocol uploads require `FormData`; Axios supports that without retaining form-post routes.

Route-owned identifiers are passed explicitly to actions and commands. competitionId is not duplicated in create/unite request DTOs.

**Alternatives considered**:

- Keep SPA links to old form routes: rejected because it leaves the Blade pages and controllers in production.
- Submit forms to the old routes from SPA: rejected because it retains two HTTP contracts and redirects.

## Decision: retain Event domain operations and move only orchestration out of the legacy controller

**Rationale**: create, update, disable, and protocol parsing are valid behaviours. `UnitEventsAction` is the exception: it contains direct Eloquent persistence and aggregation, so it becomes an Application command/service using existing ports/factories as far as their contracts allow.

The delivered unite flow locks source events in a transaction, creates the combined event through the standard event factory, persists it through EventRepository, and only then generates distances and protocol lines in a dedicated domain service.

**Alternatives considered**:

- Delete unite-events: rejected because the retired `sum` page exposes a used organiser workflow.
- Leave unite-events in a new API controller: rejected by the Application/Bridge boundary.

## Decision: keep protocol failure translation at the Application boundary

Rationale: ProtocolFactory is a domain service and therefore raises InvalidProtocolContent, without depending on Symfony or HTTP. AddEventService and UpdateEventService translate it to the InvalidProtocol Application exception (400, invalid_protocol), which the API error serializer already understands.

Event information and protocol replacement are separate domain operations with separate events and handlers.

## Decision: re-use `ViewEventDto` for Cup Blade presentation

**Rationale**: Cup templates require event id, competition id/name, name, and date, all available on `ViewEventDto`. The only `firstDistance` conditional can be retired: an event link may safely lead to the SPA's existing empty-distance state. This avoids leaking a domain `Distance` through a presentation DTO.

**Alternatives considered**:

- Keep `LegacyViewEventDto`: rejected by the requested legacy cleanup.
- Add raw `Distance` to `ViewEventDto`: rejected because the public/read DTO must not carry Eloquent/domain objects for a Blade-only conditional.

## Decision: re-use existing SPA visual primitives

**Rationale**: `CompetitionForm`, `Card.form-card`, `ActionButton`, `ConfirmDeleteDialog`, `Message`, toasts, and existing page spacing are already the accepted visual language.

**Alternatives considered**:

- New page-specific styles/controls: rejected because they caused visual divergence on the earlier event page.
