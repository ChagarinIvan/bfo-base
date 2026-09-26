# Implementation Plan: Retire Legacy Web Routes and Services

**Branch**: `master` | **Date**: 2026-09-26 | **Spec**: [spec.md](spec.md)

## Summary

Replace the remaining useful cup web actions with V1 API actions and SPA calls. Make the full CSV export read the cached `CupTableBuilder` result used by the table view. Remove the provider, unused group export, and old browser view layer. Replace `DistanceService` calls with `DistanceRepository` criteria shared through protected methods in `AbstractCupType`; move event-distance deletion behind a domain port. Inventory all remaining legacy services and remove only unreferenced code without expanding the migration.

## Technical Context

**Language/Version**: PHP 8.5, TypeScript and Vue 3  
**Primary Dependencies**: Laravel 13, V1 auth middleware, Axios, PrimeVue  
**Storage**: MySQL 8.4, Redis cup table cache  
**Testing**: PHPUnit API/integration and unit tests, Vitest SPA tests  
**Target Platform**: Laravel server and SPA  
**Performance Goals**: Export calculates each group once using the shared cached builder; avoid per-row database queries  
**Constraints**: Preserve CSV download behavior, V1 error contract, scoring rules, and email rendering  
**Scale/Scope**: Four replacement API actions, one retired group export route, root redirect, eight legacy service classes, cup scoring and event cleanup callers

## Constitution Check

| Rule | Design check |
| --- | --- |
| Layer boundaries | New use cases in Application; actions in Bridge; repository and cache adapters in Infrastructure. Domain has no new Laravel dependency. |
| Commands and mutation | Each new action constructs a command and calls `execute()`. Existing disable and cache services remain the mutation authorities. |
| Repository contract | Cup and distance reads use Domain ports. New distance deletion uses a Domain port and Infrastructure implementation. No new legacy repository or service class. |
| Tests | Unit tests mock repositories and collaborators; API tests use DB records; SPA tests cover caller changes. |
| V1 API | V1 auth/error conventions apply; API request tests identify actions with class-level `@see`. |
| Final gates | Focused tests during work, full test/style/static checks and application startup at the end. |

The checks pass before and after design. Existing cup types have legacy dependencies; this feature removes the `DistanceService` dependency and does not introduce a new one.

## Project Structure

### Documentation

```text
specs/039-retire-legacy-web/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── contracts/routes-and-export.md
├── checklists/requirements.md
├── quickstart.md
└── tasks.md
```

### Source code

```text
app/Application/Service/Cup/          # export and existing cup use cases
app/Application/Service/CupEvent/     # existing stage disable use case
app/Domain/Cup/                       # cup table builder and scoring
app/Domain/Distance/                  # distance repository and deletion ports
app/Infrastructure/Laravel/Eloquent/ # distance adapters
app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/
app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php
resources/spa/api/cups.ts
resources/spa/pages/cups/
resources/spa/components/
tests/Feature/Api/V1/Cup/
tests/Application/Service/Cup/
tests/Domain/Cup/
```

**Structure Decision**: Extend current layered code and V1 API provider. Register the minimal root redirect with the existing route provider after removing the web provider.

## Sequence

1. Capture current routes, service callers, and representative export/table behavior.
2. Add API request coverage for cup delete, stage delete, cache clear, full export, and absence of group export; add SPA caller coverage.
3. Build the full export use case around `CupTableBuilder` and cup repositories. Return CSV through a V1 action using a dedicated CSV serializer.
4. Add management API actions using existing Application services. Replace SPA links and download calls.
5. Replace distance queries with `DistanceRepository` calls in protected `AbstractCupType` methods; add a distance deletion port and adapter for event cleanup. Remove `DistanceService`.
6. Remove the web provider and browser-only components, services, views, routes, Mix assets and dependencies, and obsolete tests. Retain mail views.
7. Audit remaining legacy services, run focused tests, final project gates, application startup, and a query-count check for exports.

## Complexity Tracking

CSV attachments return an HTTP response from V1 actions, while the standard V1 read action returns DTOs. This exception is required for a browser download with content disposition and uses the same `ApiAction` error handling. The existing cup type hierarchy still depends on `ProtocolLinesRepository`; replacing that separate legacy dependency requires a larger scoring refactor and is recorded in the service inventory rather than silently added to this feature.
