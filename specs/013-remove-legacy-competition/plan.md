# Implementation Plan: Удаление legacy competition-раздела

**Branch**: `013-remove-legacy-competition` | **Date**: 2026-09-03 | **Spec**: [spec.md](spec.md)

## Summary

Проверить существующую competition SPA как canonical интерфейс, довести отсутствующие экраны до
минимально рабочего состояния только при необходимости, затем полностью очистить оставшийся
legacy presentation-слой соревнований: web routes, actions, Blade views, неиспользуемые импорты и
регрессионные тесты старого поведения. Сохранить V1 API, Application/Domain-логику, данные и
неперенесённые event/protocol/cup-пути.

## Technical Context

**Language/Version**: PHP 8.5 / Laravel 13; TypeScript strict / Vue 3

**Primary Dependencies**: Laravel routing, existing V1 competition API, Vue Router 4, Vitest,
PHPUnit

**Storage**: Existing MySQL-backed Competition/Event persistence; no schema changes

**Testing**: PHPUnit feature/request and controller tests; Vitest router/page tests; repository
search and route inventory; final Composer and frontend quality gates

**Target Platform**: Nginx + PHP-FPM serving the SPA shell under `/app/*`

**Project Type**: Laravel web application with Vue SPA and transitional Blade sections

**Performance Goals**: No new API requests or N+1 queries beyond the existing competition SPA flow;
legacy removal must not add a second data-loading path

**Constraints**: Preserve V1 API contracts and stored data; do not expand `app/Services`; do not
remove non-competition event/protocol/cup functionality; old web mutation routes must not execute
through fallback handlers

**Scale/Scope**: Competition list/detail/create/edit SPA entry points, all legacy competition web
entry points, internal links, route/action/view/test inventory

## Constitution Check

| Principle | Status | Decision |
|---|---|---|
| Layered architecture | ✅ | No new domain or persistence behavior; UI/navigation changes stay in Bridge/SPA boundaries. |
| No legacy expansion | ✅ | Legacy competition presentation is removed; no new classes are added to `app/Services`. |
| DI and no facades | ✅ | No new backend dependencies; retained API/Application code remains unchanged unless inventory requires it. |
| Tests required | ✅ | Route absence, SPA navigation and retained cross-section links receive regression coverage. |
| SPA migration direction | ✅ | `/app/competitions*` is the sole competition UI; old Blade screens are removed. |
| N+1/performance | ✅ | Inventory and targeted checks confirm no new query path or per-row request. |

No constitution violations require justification.

## Phase 0: Research

See [research.md](research.md). The repository inventory shows the target SPA already exists with
list, details, create and edit routes. Research therefore focuses on detecting all remaining legacy
competition references and classifying each as removable, retained API/domain code, or a valid link
from a non-migrated section.

## Phase 1: Design

See [data-model.md](data-model.md), [contracts/removal-contract.md](contracts/removal-contract.md)
and [quickstart.md](quickstart.md).

### Project structure

```text
app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php
app/Bridge/Laravel/Http/Controllers/Competition/       # legacy candidates, if present
app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/ # retained API
app/Application/Service/Competition/                  # retained use cases
app/Domain/Competition/                                # retained domain
resources/spa/router/index.ts
resources/spa/pages/competitions/                      # existing target UI
resources/views/competitions/                           # legacy candidates, if present
resources/views/{events,groups,persons,flags,cup}/      # retained sections and links
tests/Feature/Competition/
tests/Feature/Api/V1/Competition/                       # retained API tests
tests/Bridge/Laravel/Http/Controllers/Competition/     # legacy tests, if present
specs/013-remove-legacy-competition/
```

### Implementation decisions

- Treat the existing `/app/competitions`, `/app/competitions/:id`, `/app/competitions/create` and
  `/app/competitions/:id/edit` routes as canonical after verifying their page and router tests.
- If the preflight inventory finds a missing target page, implement only that missing SPA screen
  using existing SPA patterns and V1 contracts before deleting its legacy counterpart.
- Enumerate every old competition list/show/create/edit/store/update/delete route and every action,
  Blade view, import, test and internal URL that refers to it.
- Delete obsolete legacy route registrations, presentation/mutation actions, Blade views and tests
  as one unit. Remove stale imports even when the enclosing Blade page still exists for another
  section.
- Do not delete `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition`, Application/Domain
  competition code, or unrelated event/protocol/cup actions.
- Keep links from retained sections pointed to `/app/competitions...`; retain event/protocol links
  themselves until those sections migrate.
- Verify old URLs are absent and cannot mutate data; preserve only behavior explicitly classified in
  the inventory.

## Post-design Constitution Check

All gates pass. The plan removes redundant legacy presentation code, keeps target-layer API/domain
code intact, uses tests for behavior changes, and preserves local runnability and non-competition
flows.

## Complexity Tracking

No constitution violations require justification.
