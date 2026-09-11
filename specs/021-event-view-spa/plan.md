# Implementation Plan: Event View SPA

**Branch**: `021-event-view-spa` | **Date**: 2026-09-10 | **Spec**: [spec.md](spec.md)

## Summary

Replace the public Blade event-result page with an SPA event view. Add read APIs for one event and all of its distances, extend the existing protocol-line listing for distance/name/club reads, retain the legacy event-edit target, and delete only the retired event show entry points.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript, Vue 3, Laravel 13
**Primary Dependencies**: Laravel, PrimeVue, Axios, PHPUnit, Vitest
**Storage**: MySQL/Eloquent; no schema change
**Testing**: PHPUnit API/integration tests and Vitest SPA/API/component tests
**Target Platform**: Laravel monolith plus `/app` SPA
**Project Type**: Web application
**Performance Goals**: One event read, one unpaginated distance read, and paginated protocol-line reads without per-line relation queries
**Constraints**: Public endpoint data remains safe for guests; impressions/actions are gated by existing optional API authentication; the legacy edit URL remains; no new repository abstraction
**Scale/Scope**: One SPA page, three read APIs or API extensions, and removal of the legacy event-show surface

## Constitution Check

- Bridge actions deserialize requests and invoke Application commands only.
- Read ports remain `byCriteria`/`oneByCriteria`; eager resources are typed and opt-in. No repository is added.
- Club-name normalization is applied at the Domain boundary before matching persisted normalized club names.
- Domain/Application unit tests use mocks; API request tests create database fixtures.
- No Laravel facade or new `app/Services` class is introduced.

**Result**: PASS before research and after design. The plan extends existing Event, Club, and ProtocolLine ports rather than adding a legacy path.

## Design

1. Add a public `GET /api/v1/events/{eventId}` detail action backed by an Event Application query. Its DTO contains event information, competition display data, and impressions protected by the existing authenticated serialization group. Cup data is deferred to dedicated Cup and CupEvent APIs.
2. Add public `GET /api/v1/distances?eventId={eventId}`. A `ListEventDistances` Application query loads the active event through the existing Event port, maps its already-loaded distances/groups to small distance DTOs, and returns the full list (not a `Slice`). A missing/inactive event is a 404.
3. Extend the existing protocol-line search DTO/command/criteria/resources with `distanceId`, case-insensitive athlete-name filter, and `withClub`. `distanceId` becomes a supported bounded listing selector alongside the current person listing. It does not enable `withEvent` or `withCompetition` for the event page.
4. Keep a raw `club` field on every `ViewProtocolLineDto`. When `withClub=1`, `ListProtocolLinesService` collects distinct normalized raw names for the page, asks the existing Club port for matching active clubs in one query, and maps optional club ID/name into each DTO. No club resolution is performed when the resource is off.
5. Add SPA API clients/types/tests and an `EventViewPage` using established `Card`, `DataTable`, `Select`, `Paginator`, `ImpressionDetails`, and `ActionButton` conventions. Initial selection is the first returned distance; changing it or the name filter resets pagination and reloads protocol lines. The page detects optional points/VK columns from the returned page as the legacy table did.
6. Register `/app/events/:eventId`; replace competition event links with this route. Authenticated state controls impressions, the legacy `/events/{eventId}/edit` link, activation date, and Assign person action.
7. Remove only `ShowEventAction`, `ShowEventDistanceAction`, `RendersEventDistance`, `resources/views/events/show.blade.php`, their two public web routes/imports, and their uniquely scoped tests. Keep all other event/cup Web actions and their routes.

## Project Structure

```text
app/Application/Dto/{Event,Distance,ProtocolLine}/
app/Application/Service/{Event,Distance,ProtocolLine}/
app/Bridge/Laravel/Http/Controllers/Api/V1/{Event,Distance}/
app/Bridge/Laravel/Provider/{ApiV1RoutesServiceProvider,WebRoutesServiceProvider}.php
app/Domain/{Club,ProtocolLine}/
app/Infrastructure/Laravel/Eloquent/{Club,ProtocolLine}/
resources/spa/{api,pages/events,router}/
tests/Feature/Api/V1/{Event,Distance,ProtocolLine}/
```

**Structure Decision**: Extend the Laravel monolith and SPA in place; no new project or database structure is needed.
