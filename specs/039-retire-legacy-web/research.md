# Research: Legacy Web Retirement

## Existing entry points

**Decision**: Replace the provider's cache clear, cup disable, stage disable, and full export routes with V1 routes. Retire the unused group export route. Register the root redirect with SPA routes.

**Rationale**: `WebRoutesServiceProvider` registers only `/` and those five cup routes. The SPA still calls three old mutation URLs. A full export V1 route already exists but points at the old browser controller.

**Alternative considered**: Keep old web routes as redirects. This would preserve unsafe GET mutations and keep the provider alive.

## Export calculation and format

**Decision**: Load a cup and ordered stages through Domain repositories and call `CupTableBuilder` for each supported group. Serialize ranked rows to semicolon-delimited CSV with CRLF line endings and proper CSV quoting. Keep the full export as an attachment. API failures use JSON.

**Rationale**: `ViewCupTableService` already uses `CupTableBuilder`; its cached decorator shares invalidation tags with cup changes. The old export independently calls `CupEventsService` and recalculates points under a second cache key.

**Alternative considered**: Call `ViewCupTableService` for export. It applies name filtering and pagination DTO assembly, so it does not expose complete table data for a file.

## Distance migration

**Decision**: Inject `DistanceRepository` into `AbstractCupType` and keep shared lookup criteria in protected methods for group names, equal distances, and event group distances. Add a `DistanceDeleter` port for event cleanup, implemented by Eloquent.

**Rationale**: `DistanceService` mixes queries, a cup selection rule, and Eloquent deletion. Domain cup types must not depend on a legacy service. Event handlers must not call a model relation to delete persistence state.

**Alternative considered**: A separate `DistanceQueries` service. It only forwarded repository calls and added an extra dependency between cup types and the repository.

## Old frontend inventory

**Decision**: Remove layout and component Blade templates, their PHP components and registration provider, unused action base and view helper service. Retain `resources/views/emails/*` because mail classes render them. Remove `UserService`; keep the `Language` middleware's fixed Belarusian locale behavior for the remaining web middleware users.

**Rationale**: Production references to layout/component views are confined to the old view layer. Email views remain live.

**Alternative considered**: Delete all `resources/views`. This would break registration and password email delivery.

## Legacy services audit

**Decision**: Remove `CupEventsService`, `DistanceService`, and browser-only services as part of this feature. Record active production callers for protocol and person services; leave their wider migrations outside this route retirement.

**Rationale**: The latter services are used by imports, ranking, commands, or integrations and require separate behavior changes.
