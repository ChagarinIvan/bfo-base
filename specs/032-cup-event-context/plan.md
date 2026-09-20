# Implementation Plan: Cup Event Context Badges

**Branch**: `032-cup-event-context` | **Date**: 2026-09-20 | **Spec**: [spec.md](spec.md)

## Summary

Expose one non-paginated compact batch read for active cup stages by event IDs,
then render a shared linked cup badge beside event names in competition, person,
rank and group tables.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript/Vue 3

**Primary Dependencies**: Laravel 13, PrimeVue, Sanctum

**Storage**: MySQL 8.4

**Testing**: PHPUnit API requests; Vitest component/page tests

**Target Platform**: Existing SPA and legacy cup-stage view

**Performance Goals**: One additional batch request per displayed table; no
per-row requests or N+1 relations.

## Constitution Check

- Bridge action remains thin; DTO validates query; application service assembles
  output; Domain repository port and Eloquent adapter perform lookup.
- API request test has class-level `@see` for the action.
- Application/unit tests use mocks only; API test owns database fixtures.
- New SPA text goes only into `resources/lang/by.json`.

## Design

1. `CupEventContextSearchDto` accepts non-empty `eventIds[]`.
2. `ListCupEventContextsAction` calls `ListCupEventContextsService`, which asks
   `CupEventRepository::byCriteria()` for active stages matching the IDs and
   maps a compact context DTO. The repository eager-loads only `cup`.
3. The result DTO contains `eventId`, `cupId`, `cupName`, `cupType`,
   `cupEventId`, and `href`. `href` uses the cup's first group and the existing
   `/cups/{cup}/{cupEvent}/{group}/show` route.
4. `getCupEventContexts(eventIds)` batches the read. `CupEventBadges.vue`
   renders type icon inside a linked button/badge and uses PrimeVue Popover for
   name on click/hover/focus.
5. Each consumer fetches contexts after its event rows load, stores them by
   event ID, and renders the common component next to the event name.

## Project Structure

```text
app/Application/{Dto,Service}/CupEvent/
app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/
app/Domain/Cup/CupEvent/
app/Infrastructure/Laravel/Eloquent/CupEvent/
resources/spa/{api,components,pages}/
tests/Feature/Api/V1/Cup/
```
