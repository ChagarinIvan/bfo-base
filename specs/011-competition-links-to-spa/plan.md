# Implementation Plan: Competition links to SPA

**Branch**: `011-competition-links-to-spa` | **Date**: 2026-09-03 | **Spec**: [spec.md](spec.md)

## Summary

Replace internal competition list/view/create/edit entry points with existing SPA routes, then
remove only fully redundant Blade actions, views, web routes and tests. Keep V1 API actions and
non-migrated event/protocol/cup flows intact. Legacy URLs are handled according to the inventory
in [research.md](research.md), with redirects retained only where external links need compatibility.

## Technical Context

**Language/Version**: PHP 8.5 / Laravel 13; TypeScript strict / Vue 3

**Primary Dependencies**: Laravel routing, existing V1 competition API, Vue Router 4, Vitest

**Storage**: Existing Competition/Event persistence; no schema changes expected

**Testing**: PHPUnit route/controller tests; Vitest router/component tests; frontend CI

**Target Platform**: Nginx + PHP-FPM serving SPA shell under `/app/*`

**Project Type**: Laravel web application with Vue SPA

**Performance Goals**: Navigation adds no API requests beyond existing SPA page flow; no new N+1

**Constraints**: Preserve non-competition Blade routes and V1 API contracts; no second competition
UI; auth remains enforced by SPA guards and API middleware

**Scale/Scope**: Competition list, details, create/edit entry points, internal links, redirects
and their legacy presentation code

## Constitution Check

| Principle | Status | Decision |
|---|---|---|
| Layered architecture | ✅ | Navigation stays frontend; existing API remains Bridge/Application path; no domain changes. |
| No legacy expansion | ✅ | Remove redundant competition Blade entry points; add no `app/Services`. |
| DI and no facades | ✅ | No new backend dependencies; retained actions remain unchanged where possible. |
| Tests required | ✅ | Add link/route regression coverage; update tests only for intentional removals. |
| SPA migration direction | ✅ | `/app/*` is the single competition UI; non-migrated sections remain available. |
| N+1/performance | ✅ | Navigation does not alter list/detail queries; verify SPA requests remain bounded. |

## Phase 0: Research

See [research.md](research.md). Inventory covers navbar, root redirect, 404, login/registration
redirects, competition Blade views, delete redirect and event links. SPA has all four target routes;
V1 list/view/create/update API actions are retained.

## Phase 1: Design

See [data-model.md](data-model.md), [contracts/ui-navigation.md](contracts/ui-navigation.md),
and [quickstart.md](quickstart.md).

### Project structure

```text
app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php
app/Bridge/Laravel/Http/Controllers/Competition/
resources/views/layouts/navbar.blade.php
resources/views/errors/404error.blade.php
resources/views/competitions/
app/Bridge/Laravel/Http/Controllers/{Login,Registration}/
resources/spa/router/
resources/spa/pages/competitions/
tests/Bridge/Laravel/Http/Controllers/Competition/
tests/                         # link/route regression coverage
```

### Implementation decisions

- Use `/app/competitions...` for internal links into the SPA; retain `action()` calls for sections
  that remain Blade.
- Redirect root, login/registration completion and 404 competition fallback to the SPA list.
- Keep competition detail links to event/protocol pages on their existing URLs until migrated.
- Remove all legacy competition presentation and mutation actions only after usages are gone;
  remove views, routes and tests together. SPA create/update/delete uses V1 endpoints.
- If old public URLs need bookmark compatibility, redirect them to matching SPA paths and cover
  redirects without retaining Blade rendering.

## Post-design Constitution Check

All gates pass. The plan changes presentation/navigation boundaries only, keeps API and domain
contracts stable, and inventories every legacy deletion candidate before removal.

## Complexity Tracking

No constitution violations require justification.
